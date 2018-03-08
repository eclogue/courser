<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/7/2
 * @time: 下午2:17
 */


define('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';
use Courser\App;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Hayrick\Http\Request;
use Hayrick\Http\Response;
use Hayrick\Http\Stream;
use Courser\Relay;
use Swoole\Http\Request as SRequest;
use Swoole\Http\Response as SResponse;
use Swoole\Http\Server;
use function DI\factory;


function getRelay(SRequest $request) {
    $server = $request->server ?? [];
    $cookie = $request->cookie ?? [];
    $files = $request->files ?? [];
    $query = $request->get ?? [];
    $headers = $request->header ?? [];
    $stream = fopen('php://temp', 'w+');
    $source = $request->rawContent();
    if ($source) {
        fwrite($stream, $source);
    }

    if (!isset($server['http_host']) && isset($headers['http_host'])) {
        $server['http_host'] = $headers['https_host'];
    }

    $body = new Stream($stream);
    $relay = new Relay($server, $headers, $cookie, $files, $query, $body);
    return $relay;
}

$container = new \DI\Container();
$container->set('request.resolver', function() {
    return function (SRequest $request) {
        return getRelay($request);
    };
});
$container->set('response.resolver', function() {
    $instance = new class extends \Courser\Terminator {

        public function end(ResponseInterface $response) {
            $data = $response->getBody();
            $this->origin->end($data);
        }
    };

    return $instance;
});

$app = new App($container);

$app->get('/', function(Request $request,  RequestHandlerInterface $handler) {
    $header = $request->getHeaders();
    $response = new Response();
    foreach($header as $key => $value) {
        $response = $response->withHeader($key, $value);
    }

    return $response->json(['test' => 1]);
});
$app->setReporter(function (Request $req, Throwable $err) {
    $res = new Response();
    return $res->withStatus(500)->json([
        'message' => $err->getMessage(),
        'code' => 500
    ]);
});
$server = new Server('127.0.0.1', 7001);
$server->on('request', function(Srequest $request, SResponse $response) use ($app) {
    $app->run($request->server['request_uri'], $request, $response);
});


$server->start();





//$res = $co();
//var_dump($res);
//$cur = $res->valid();
//var_dump($cur);

