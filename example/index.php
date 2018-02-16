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
use Courser\Test;

$app = new App();


echo "<pre>";

$app->add(new Test());

$app->get('/', function () {
    $response = new Response();

    return $response->withStatus(400)->write('test1');
});

$server = new Courser\Server\CGIServer($app);
$server->start();

