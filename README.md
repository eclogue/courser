# Course
[![Build Status](https://travis-ci.org/eclogue/courser.svg?branch=master)](https://travis-ci.org/eclogue/courser)
[![Coverage Status](https://coveralls.io/repos/github/eclogue/courser/badge.svg?branch=master)](https://coveralls.io/github/eclogue/courser?branch=master)
[![Latest Stable Version](https://poser.pugx.org/eclogue/courser/version)](https://packagist.org/packages/eclogue/courser)
[![Total Downloads](https://poser.pugx.org/eclogue/courser/downloads)](https://packagist.org/packages/eclogue/courser)
[![License](https://poser.pugx.org/eclogue/courser/license)](https://packagist.org/packages/eclogue/courser)

Fast and scalable web framework. Implement psr-7, psr-15, psr-4, psr-2, psr-11.It is easy to write swoole, reactphp, workerman and cgi web application.I believe that 
`entities should not be multiplied unnecessarily.` 

**Feature**
- PSR-15 middleware
- PSR-7 http message
- PSR-11
- coroutine support


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
use Psr\Http\Message\RequestInterface;
use Hayrick\Http\Response;
use Psr\Http\Server\RequestHandlerInterface;

$config = [
    'server' => [
        'host' => '0.0.0.0',
        'port' => '5001',
    ],
];
Config::set($config);
$app = new App();
$app->used(function(RequestInterface $req, RequestHandlerInterface $handler) {
   echo "this middleware 1 \n";
   $response = yield $handler->handle($req);
   // var_dump($response);
   return $response;
});

$app->used(function(RequestInterface $req, RequestHandlerInterface $handler) {
    yield;
    $response = $handler->handle($req);
    echo "this middleware 2 \n";
    // var_dump($response);
    return $response;
});
$app->get('/', function(RequestInterface $req,  RequestHandlerInterface $handler) {
    $html = "<h1> fuck world</h1>";
    $res = yield $handler->handle($req);
    
    return $res->withHeader('Content-Type', 'text/html');
});
$app->get('/', function(Request $req) {
    $html = "<h1> fuck world</h1>";
    $res = new Response();

    return $res->end($html);
});

```
**use swoole for server**:

```
$server = new \Course\Server\HttpServer($app);
$server->bind(Config::get('server.host'), Config::get('server.port'));
$server->start();
```
now run `php server.php`, visit 127.0.0.1:5001

**use cgi server**:
```
$server = new \Course\Server\CGIServer($app);
$server->start();
```


### Router

```php
<?php

# basic /users/11
$app->get('/users/:id', function($req, RequestHandlerInterface $handler) {
    var_dump($req->params['id']); // id must be integer
    return $handler->handle($req);
});
$app->get('/users/:id', function($req) {
    return ['data' => '1'];
});
# use array
$app->get('users/*', [function($req, RequestHandlerInterface $handler) {
    /* do something*/
}, function($req, RequestHandlerInterface $handler) {
    /*...todo*/
}]);

# use namespace
$app->put('/user/{username}', ['MyNamespace\Controller', 'action']);

# use group

$app->group('/admin/{username}',  function() {
    // [Notice]: In group `$this` is bind to Courser,
    // middleware define in group just have effect on the router of group scope 
    $this->used(function($req, RequestHandlerInterface $handler) { // Add group middleware
        // todo
        // this middleware is mount at /admin/{username} scope, have not effect outside of this group.
    });
    $this->get('/test/:id', function($req, RequestHandlerInterface $handler) {
        yield $handler->handle($req);
        // ...
    });
});
```
 
### Middleware

  Courser's middleware look like [koa](https://github.com/koajs/koa).
  
  It is compatible with Laravel's middleware and the suggestion of [http-handlers](https://github.com/php-fig/fig-standards/blob/master/proposed/http-handlers/request-handlers-meta.md#52-single-pass-lambda)
  
  You can define a middleware like:
```
    $app->used(function(Request $request, $handler) {
        return $handler($request);  
    });
```
like this:
```
   class A {
        public function someMethod(Request $request, $handler) {
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
    function middleware(Request $req, RequestHandlerInterface $handler) {
        $userId = $req->getParam('userId');
        $model = new User();
        $user = yield $model->findById($userId);
        var_dump($user);
        $response = yield $handler->handle($request);

        return $response;
    }
  ```

### Develop
 Here is a tool quickly develop web app ([gharry](https://github.com/eclogue/gharry))

 It watch project file change and auto reload your server.
 
 [Ben](https://github.com/eclogue/ben) is a convenient config manager， I recommend use Ben to manage use config file.

### Benchmark
    Damn it. I just know that, it is fast.

### Community

 - [中文文档](https://superbogy.gitbooks.io/courser/content/)
 - [Example](https://github.com/eclogue/knight)
 - [issue](https://github.com/shipmen/Course/issues)
 
### Coding...

### Maintain

mulberry10<[mulberry10th@gmail.com]()>

### License
    MIT


