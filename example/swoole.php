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
//use Ben\Config;
use Courser\Server\SwooleServer;
use Hayrick\Http\Request;
use Hayrick\Http\Response;
use Courser\Relay;
use Swoole\Http\Request as SRequest;


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


$config = [];

error_reporting(E_ALL);
//Config::set($config);
$app = new App();
//$app->used(function(Request $req, Closure $next) {
////    var_dump($req);
//    echo "this middleware 1 \n";
//    $response = $next($req);
//    // var_dump($response);
//    return $response;
//});
//
//$app->used(function(Request $req, Closure $next) {
//    yield 1;
//    $response = $next($req);
//    echo "this middleware 2 \n";
//    return $response;
//});
//$app->get('/', function(Request $req,  Closure $next) {
//    $html = "<h1> fuck world2</h1>";
//    $res = yield $next($req);
//    $res->write($html);
//
//    return $res->withHeader('Content-Type', 'text/html');
//});
$app->get('/', function(Request $req) {
    $req = null;
    new Fuck;
    $html = "<h1> fuck world</h1>";
    $res = new Response();
//    ob_start ();
//
//    ob_start ();                              // Capturing
//    phpinfo ();                               // phpinfo ()
//    $info = trim (ob_get_clean ());
    return $res->send($html);
});

//$app->get('/error', function () {
//    throw new Exception('test');
//});

//$app->post('/test', function (Request $request, $next) {
//    echo '12312312312312312312321312';
//    yield;
//    return '123';
//});

//$app->used(function () {
//    $response = new \Hayrick\Http\Response();
//
//    return $response->withStatus(404)
//        ->json(['message' => 'Not Found']);
//});
$app->setReporter(function (Request $req, Throwable $err) {
    $res = new Response();
    return $res->withStatus(500)->json([
        'message' => $err->getMessage(),
        'code' => 500
    ]);
});

$server = new SwooleServer($app);
$server->bind('0.0.0.0', '8179');
$server->setting([
    // ... swoole setting
]);
$server->register('start', function (){
    echo "server started, listening at: http//127.0.0.1:8179\n";
});

$server->start();




//$res = $co();
//var_dump($res);
//$cur = $res->valid();
//var_dump($cur);

