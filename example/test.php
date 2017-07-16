<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/4
 * @time      : 下午8:45
 */

define('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';
use Courser\App;
use Ben\Config;
use Courser\Server\HttpServer;


$config = [];

Config::set($config);
$app = new App();

$app->used(function($req, $res) {
    yield;
    echo "this middleware 1 \n";
});

$app->used(function($req, $res) {
    yield;
    echo "this middleware 2 \n";
});
$app->used(function($req, $res) {
    $i = 5;
    while($i) {
        $i--;
        yield;
        echo "this middleware 3 \n";
    }
});
$app->used(function($req, $res) {
    yield;
    echo "this middleware 4 \n";
});
$app->get('/', function($req, $res) {
    $html = "<h1 style='text-align: center;font-size: 8em;margin-top: 20%'>";
    $html .= "Fuck world</h1>";
    $res->withHeader('Content-Type', 'text/html')->send($html);
});
$app->post('/', function($req, $res) {
    var_dump($req->payload('test'));
    $html = "<h1 style='text-align: center;font-size: 8em;margin-top: 20%'>";
    $html .= "Fuck world</h1>";
    $res->withHeader('Content-Type', 'text/html');
    $res->send($html);
});
$app->used(function($req, $res) {
    $res->withStatus(404)->json([
        'message' => 'not found',
    ]);
});

$app->error(function($req, $res, $err) {
    $res->withStatus(500)->json([
        'message' => $err->getMessage(),
    ]);
});

$server = new HttpServer($app);
$server->bind('0.0.0.0', '6001');
$server->set([
   // ... swoole setting
]);
$server->start();