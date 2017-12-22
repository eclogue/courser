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
use Hayrick\Http\Request;
use Hayrick\Http\Response;

$app = new App();

$app->used(function (Request $req, Closure $next) {
    $response = $next($req);
    $response->write('<h1>test2</h1>');

    return $response;
});

$app->get('/', function () {
    $response = new Response();

    return $response->send('test1');
});

$server = new Courser\Server\CGIServer($app);
$server->start();