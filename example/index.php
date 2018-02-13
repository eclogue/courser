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
use Courser\Server\CGIServer;
use Hayrick\Http\Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

$app = new App();

class md1 {


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        var_dump($request);
        var_dump($handler);

        return new Response();
    }
}


echo "<pre>";
//$app->add(new md1());

$app->get('/', function () {
    $response = new Response();

    return $response->withStatus(400)->write('test1');
});

$server = new Courser\Server\CGIServer($app);
$server->start();

