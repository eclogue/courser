<?php

define('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';
use Hayrick\Http\Request;

$http = new Swoole\Http\Server("127.0.0.1", 7001);
$http->set([
    'worker_num' => 2,
    'dispatch_mode' => 2,
]);
$http->on('request', function ($request, $response) {
    try {
        $relay = new \Courser\Relay($request);
        $req = new Request($relay);
        new Fuck;
        $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
});
$http->start();

