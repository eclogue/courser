<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/12/22
 * @time: 下午12:31
 */
define('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';

use Courser\App;
use Hayrick\Http\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use DI\Container;


$app = new App();


class Test implements MiddlewareInterface
{
    public function __construct()
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response =  $handler->handle($request);

        return $response;
    }
}

$app->add(new Test());


$app->get('/test/:id', function (ServerRequestInterface $request) {
    $id = $request->getAttribute('params');

    $response = new Response();
    return $response->json(['id' => $id]);
});
$app->setReporter(function(RequestInterface $request, Throwable $err) {
//    var_dump($request, $err->getMessage());
    $response = new Response();
    $response = $response->json([
        'error' => $err->getMessage(),
    ])->withStatus(500);
//    var_dump($response->getHeaders());

    return $response;
});

//echo "<pre>";
$app->run();


//$builder = new \DI\ContainerBuilder();
//$builder->addDefinitions([
//    'foo' => function ($c) {
//        return new Response();
//    },
//    Test::class => \DI\factory([\Hayrick\Environment\Relay::class, 'createFromGlobal'])
//]);
//
//$container = $builder->build();
//
//$make = $container->make(Test::class, [
//    'user' => 'torvalds',
//]);
//
//var_dump($make);

//var_dump($container->get('GithubProfile'));
