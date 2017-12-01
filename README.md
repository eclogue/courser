# Course
[![Build Status](https://travis-ci.org/eclogue/courser.svg?branch=master)](https://travis-ci.org/eclogue/courser)
[![Coverage Status](https://coveralls.io/repos/github/eclogue/courser/badge.svg?branch=master)](https://coveralls.io/github/eclogue/courser?branch=master)
[![Latest Stable Version](https://poser.pugx.org/eclogue/courser/version)](https://packagist.org/packages/eclogue/courser)
[![Total Downloads](https://poser.pugx.org/eclogue/courser/downloads)](https://packagist.org/packages/eclogue/courser)
[![License](https://poser.pugx.org/eclogue/courser/license)](https://packagist.org/packages/eclogue/courser)

A tiny web framework. It is so simple that you can use it without document. The core files less than 1000 lines.I believe that 
`entities should not be multiplied unnecessarily.` 

当时明月在，曾照彩云归。 --- 临江仙·梦后楼台高锁【晏几道】

### Installation
`composer require eclogue/courser` or git clone https://github.com/eclogue/courser

### Get start

`composer install` 

Create a new file server.php.

```php
<?php
require('./vendor/autoload.php');
use Courser\App;
use Ben\Config;
//use Psr\Http\Message\RequestInterface as Request;
use Hayrick\Http\Request;
use Hayrick\Http\Response;

$config = [
    'server' => [
        'host' => '0.0.0.0',
        'port' => '5001',
    ],
];
Config::set($config);
$app = new App();
$app->used(function(Request $req, Closure $next) {
   echo "this middleware 1 \n";
   $response = yield $next($req);
   // var_dump($response);
   return $response;
});

$app->used(function(Request $req, Closure $next) {
    yield;
    $response = $next($req);
    echo "this middleware 2 \n";
    // var_dump($response);
    return $response;
});
$app->get('/', function(Request $req,  Closure $next) {
    $html = "<h1> fuck world</h1>";
    $res = yield $next($req);
    
    return $res->withHeader('Content-Type', 'text/html');
});
$app->get('/', function(Request $req) {
    $html = "<h1> fuck world</h1>";
    $res = new Response();

    return $res->end($html);
});

$server = new \Course\Server\HttpServer($app);
$server->bind(Config::get('server.host'), Config::get('server.port'));
$server->start();
?>
```
now run `php server.php`, visit 127.0.0.1:5001

### Router

```php
<?php

# basic /users/11
$app->get('/users/:id', function($req, Closure $next) {
    var_dump($req->params['id']); // id must be integer
    return $next($req);
});
$app->get('/users/:id', function($req) {
    return ['data' => '1'];
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
    $this->used(function($req, Closure $next) { // Add group middleware
        // todo
        // this middleware is mount at /admin/{username} scope, have not effect outside of this group.
    });
    $this->get('/test/:id', function($req, Closure $next) {
        yield 1;
        // ...
    });
});
```
 
### Middleware

  Courser's middleware look like [koa](https://github.com/koajs/koa).
  
  It is compatible with Laravel's middleware and the suggestion of [http-handlers](https://github.com/php-fig/fig-standards/blob/master/proposed/http-handlers/request-handlers-meta.md#52-single-pass-lambda)
  
  You can define a middleware like:
```
    $app->used(function(Request $request, $next) {
        return $next($request);  
    });
```
like this:
```
   class A {
        public function someMethod(Request $request, $next) {
            // ....
        }
   }
   
   $app->used([A:class, 'someMethod']);
    
```    
  


### Not Found
```php
$app->notFound(function (Request $req){
    $response = new Response();
    $response->withStatus(404)->json(['message' => 'Not Found']);
});
```
### Exception
```php
$app->error(function ($req, $res, Exception $err) {
   $res->withStatus(500)->json([
       'message' =>$err->getMessage(),
       'code' => 10502,
   ]);
});

```

### Coroutine

  Courser support write coroutine application in easy way. If you are not familiar with php coroutine, it does'nt matter,
  
  Courser had already do everything. you just use `yield` keyword  to let process gives up its time slice. 
  ```
    // a middleware
    function middleware(Request $req, Closure $next) {
        $userId = $req->getParam('userId');
        $model = new User();
        $user = yield $model->findById($userId);
        var_dump($user);
        return $next($request);
    }
  ```

### Develop
 Here is a tool to help you write web app ([gharry](https://github.com/eclogue/gharry))
 It watch project file change and auto reload your server.
 
 [Ben](https://github.com/eclogue/ben) is a convenient config manager， I recommend use Ben to manage use config file.

### Benchmark
    Damn it. I just know that, it is fast.

### Community

 - [中文文档](https://superbogy.gitbooks.io/courser/content/)
 - ~~[English document]()~~~
 - [Example](https://github.com/eclogue/knight)
 - [issue](https://github.com/shipmen/Course/issues)
 
### Coding...

### Maintain

mulberry10<[mulberry10th@gmail.com]()>

### License
    MIT


