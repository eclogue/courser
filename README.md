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

**Simple usage** `php -S 127.0.0.1:7001 -t example/`


**Use swoole for server**: `php example/swoole.php`




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

$app->group('/admin/{username}',  function(App $app) {
    // [Notice]: In group `$this` is bind to Courser,
    // middleware define in group just have effect on the router of group scope 
    $app->used(function($req, RequestHandlerInterface $handler) { // Add group middleware
        // todo
        // this middleware is mount at /admin/{username} scope, have not effect outside of this group.
    });
    $app->get('/test/:id', function($req, RequestHandlerInterface $handler) {
        yield $handler->handle($req);
        // ...
    });
});
```
 
### Middleware

 Flow the PSR-15 standard, 
 see [https://github.com/middlewares/awesome-psr15-middlewares](https://github.com/middlewares/awesome-psr15-middlewares) 
  


### Not Found handle

```php
$app->notFound(function (Request $req){
    $response = new Response();
    $response->withStatus(404)->json(['message' => 'Not Found']);
});
```

or

```
# add after the last route
$app->add(new NotFoundMiddleware());
```

### Exception
```php
$app->setReporter(function ($req, $res, Exception $err) {
   $res->withStatus(500)->json([
       'message' =>$err->getMessage(),
       'code' => 10502,
   ]);
});

```

### Coroutine

  Courser support write coroutine application in easy way. support `yield`  syntax. 
  ```
    // a middleware
    function process(Request $req, RequestHandlerInterface $handler) {
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
 
 [Ben](https://github.com/eclogue/ben) is a convenient config manager， I recommend use Ben to manage diff env config file.

### Benchmark
    Damn it. I just know that, it is fast.

### Demo

 - [Example](https://github.com/eclogue/knight)
 
### Coding...

### Maintain

mulberry10<[mulberry10th@gmail.com]()>

### License
    MIT


