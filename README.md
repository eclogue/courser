# Course

![](https://travis-ci.org/racecourse/courser.svg?branch=master)

A fast and lighter PHP micro framework base on swoole.I believe that `The greatest truths are the simplest,
 entities should not be multiplied unnecessarily.` Courser born with natural beauty.

### Installation
`composer require racecourse/courser` or git clone https://github.com/racecourse/courser
### Get start

`composer install` 

Create a new file server.php.

```php
<?php
require('./vendor/autoload.php');
use Course\App;
use Courser\Helper\Config;

$config = [
    'server' => [
        'host' => '0.0.0.0',
        'port' => '5001',
    ],
];
Config::set($config);
$app = new App('dev');
$app->use(function($req, $res) {
   yield;
   echo "this middleware 1 \n";
});

$app->use(function($req, $res) {
    yield;
    echo "this middleware 2 \n";
});
$app->get('/', function($req, $res) {
    $html = "<h1> fuck world</h1>";
    $res->header('Content-Type', 'text/html');
    $res->send($html);
});

$server = new \Course\Server\HttpServer($app);

$server->start();
?>
```
now run `php server.php`, visit 127.0.0.1:5001

### Router


```php
<?php

# basic /users/11
$app->get('/users/:id', function($req, $res) {
    var_dump($req->params['id']); // id must be integer
    yield 1000;
});
$app->get('/users/:id', function($req, $res) {
    $value = (yield); // $value === 1000;
    $res->send($value);
});
# use array
$app->get('users/*', [function($req, $res) {
    /* do something*/
}, function($req, $res) {
    /*...todo*/
}]);

# use namespace
$app->put('/user/{username}', ['MyNamespace\Controller', 'action']);

# use group

$app->group('/admin/{username}',  function() {
    // [Notice]: In group `$this` is bind to Courser,
    // middleware define in group just have effect on the router of group scope 
    $this->used(function($req, $res) { // Add group middleware
        // todo
        // this middleware is mount at /admin/{username} scope, have not effect outside of this group.
    });
    $this->get('/test/:id', function($req, $res) {
        yield 1;
    });
});
```
 
### Middleware
>  Course's middleware look like [koa](https://github.com/koajs/koa).In koa, everything is middleware,
   but Courser split middleware and user business. 
   A middleware must be a callable function or a instance that have `__invoke` function;


### Benchmark
 Just kidding.
 Env: docker on mac, ubuntu16.10 PHP 7.0.15-0ubuntu0.16.04.4 (cli) ( NTS )
 Notice: don't test benchmark on mac
>
```
Server Software:        swoole-http-server
Server Hostname:        127.0.0.1
Server Port:            5001
Document Path:          /test
Document Length:        10 bytes
Concurrency Level:      2000
Time taken for tests:   5.260 seconds
Complete requests:      50000
Failed requests:        0
Total transferred:      7900000 bytes
HTML transferred:       500000 bytes
Requests per second:    9505.09 [#/sec] (mean)
Time per request:       210.414 [ms] (mean)
Time per request:       0.105 [ms] (mean, across all concurrent requests)
Transfer rate:          1466.60 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0   59  35.6     58     134
Processing:    50  147  54.6    132     348
Waiting:       12  129  53.8    121     324
Total:        107  206  42.1    203     352 
```


### Community

 - [中文文档](https://superbogy.gitbooks.io/courser/content/)
 - [English document]()
 - [Example]()
 - [FAQ](https://github.com/shipmen/Course/issues)
 
### Coding...

### Maintain

mulberry10<[mulberry10th@gmail.com]()>

### License
    MIT


