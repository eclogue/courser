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
    echo 'this is 1' . PHP_EOL;
    $next($req);
});
$app->used(function ($req, $next) {
    echo 'this is 2222' . PHP_EOL;
    yield $next($req);
});

$app->error(function ($req, $res, $err) {
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

$carry = function () {
    return function ($stack, $pipe) {
        return function ($passable) use ($stack, $pipe) {
            if (is_callable($pipe)) {
                // If the pipe is an instance of a Closure, we will just call it directly but
                // otherwise we'll resolve the pipes out of the container and call it with
                // the appropriate method and arguments, returning the results back out.
                return $pipe($passable, $stack);
            } elseif (!is_object($pipe)) {
                list($name, $parameters) = $this->parsePipeString($pipe);
                // If the pipe is a string we will parse the string and resolve the class out
                // of the dependency injection container. We can then build a callable and
                // execute the pipe function giving in the parameters that are required.
                $pipe = $this->getContainer()->make($name);
                $parameters = array_merge([$passable, $stack], $parameters);
            } else {
                // If the pipe is already an object we'll just make a callable and pass it to
                // the pipe as-is. There is no need to do any extra parsing and formatting
                // since the object we're given was already a fully instantiated object.
                $parameters = [$passable, $stack];
            }
            return method_exists($pipe, $this->method)
                ? $pipe->get(...$parameters)
                : $pipe(...$parameters);
        };
    };
};





