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

$app->get('/', function ($request, $next) {
    $response = new Response();
    return $response->withStatus(400)->write('test1');
});

$app->get('/test/:id', function ($request, $next) {
    $id = $request->getParam('id');
    $response = new Response();
    return $response->json(['id' => $id]);
});

echo "<pre>";
($app->run($_SERVER['REQUEST_URI']))();


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