<?php
/**
 * @license   MIT
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/4
 * @time      : 下午8:45
 */

define('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';
use Courser\App;
//use Ben\Config;
use Courser\Server\HttpServer;


$config = [];

//Config::set($config);
$app = new App();

$app->used(function($req, $res) {
    yield;
    echo "this middleware 1 \n";
});

$app->used(function($req, $res) {
    $f = function () {
        $i = 0;
        while ($i < 50000) {
            $i++;
        }
    };
    yield $f();
    echo "this middleware 2 \n";
});
$app->used(function($req, $res) {
    $i = 5;
    while($i) {
        $i--;
        yield $i;
        echo "this middleware 3 \n";
    }
});
$app->used(function($req, $res) {
    echo "this middleware 4 \n";
    $ret = (yield);
    echo "d44444444 \n";
    var_dump($ret);
    yield 4;
});
$app->get('/', function($req, $res) {
    echo "ffffffuck";
    $html = "<h1 style='text-align: center;font-size: 8em;margin-top: 20%'>";
    $html .= "Fuck world</h1>";
    $res->withHeader('Content-Type', 'text/html')->end($html);
});
$app->post('/', function($req, $res) {
    $html = "<h1 style='text-align: center;font-size: 8em;margin-top: 20%'>";
    $html .= "Fuck world</h1>";
    $res->withHeader('Content-Type', 'text/html');
    $res->end($html);
});
$app->used(function($req, $res) {
    $res->withStatus(404)->json([
        'message' => 'Not Found',
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