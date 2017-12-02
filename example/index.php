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
use Hayrick\Http\Request;
use Hayrick\Http\Response;

$config = [];

//Config::set($config);
$app = new App();
$app->used(function(Request $req, Closure $next) {
//    var_dump($req);
    var_dump($req->getParsedBody());
    echo "this middleware 1 \n";
    $response = $next($req);
    // var_dump($response);
    return $response;
});

$app->used(function(Request $req, Closure $next) {
    yield 1;
    $response = $next($req);
    echo "this middleware 2 \n";
     var_dump($response);
    return $response;
});
//$app->get('/', function(Request $req,  Closure $next) {
//    $html = "<h1> fuck world</h1>";
//    $res = yield $next($req);
//
//    return $res->withHeader('Content-Type', 'text/html');
//});
//$app->get('/', function(Request $req) {
//    $html = "<h1> fuck world</h1>";
//    $res = new Response();
//
//    return $res->end($html);
//});


$app->post('/test', function (Request $request, $next) {
    echo '12312312312312312312321312';
    yield;
    return '123';
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

