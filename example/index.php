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
use Courser\Server\HttpServer;
use Bulrush\Poroutine;
use Bulrush\Scheduler;


$config = [];

//Config::set($config);
$app = new App();
$app->used(function ($req, $next) {
//    echo 'this is 1' . PHP_EOL;
//    echo "this is 111 \n";
    $res = $next($req);
    return $res;
});
$app->used(function ($req, $next) {
//    echo 'this is 2222' . PHP_EOL;
    $response = $next($req);

    return $response;
});
$app->get('/', function ($request) {
//    echo 'this is 333333' . PHP_EOL;
//    var_dump($content);
    return (new \Hayrick\Http\Response())->json(['data' => '123']);
});

$app->used(function () {
    $response = new \Hayrick\Http\Response();

    return $response->withStatus(404)
        ->json(['message' => 'Not Found']);
});
$app->error(function ($req, $err) {
    $res = new \Hayrick\Http\Response();
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

//function foo () {
//    echo 'start++' . PHP_EOL;
//    yield;
////    $a = yield file_get_contents('http://mulberry10.com');
//    $a = yield from [123,1];
//    echo "yyyyyy \n";
//    echo $a . PHP_EOL;
//}
//
//function bar () {
//    echo '===----' . PHP_EOL;
//    $a = yield 2;
//    echo $a . PHP_EOL;
//}
//
//$scheduler = new Scheduler();
//
//$scheduler->add(foo());
//$scheduler->add(bar());
//$scheduler->run();

$req = [1];

$foo = function ($req) {

};

$pipes = [
    $foo,
    $foo,
    $foo,
];

$co = function () {
    echo '123123' . PHP_EOL;
    yield 1;
    echo '----->';
};


//$res = $co();
//var_dump($res);
//$cur = $res->valid();
//var_dump($cur);

