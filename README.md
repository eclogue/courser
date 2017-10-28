# Course
[![Build Status](https://travis-ci.org/eclogue/courser.svg?branch=master)](https://travis-ci.org/eclogue/courser)
[![Coverage Status](https://coveralls.io/repos/github/eclogue/courser/badge.svg)](https://coveralls.io/github/eclogue/courser)
[![Latest Stable Version](https://poser.pugx.org/eclogue/courser/version)](https://packagist.org/packages/eclogue/courser)
[![Total Downloads](https://poser.pugx.org/eclogue/courser/downloads)](https://packagist.org/packages/eclogue/courser)
[![License](https://poser.pugx.org/eclogue/courser/license)](https://packagist.org/packages/eclogue/courser)


A minimalist web framework. I believe that 
`entities should not be multiplied unnecessarily.` 

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

$config = [
    'server' => [
        'host' => '0.0.0.0',
        'port' => '5001',
    ],
];
Config::set($config);
$app = new App();
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
$server->bind(Config::get('server.host'), Config::get('server.port'));
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
>  Course's middleware look like [koa](https://github.com/koajs/koa).
   A middleware must be a callable function or a instance that have `__invoke` function;

```php
class session {
    
    public function set()
    {
      // do something ...
    }
    
    public function get()
    {
      // ...
    }
    
    public function __invoke()
    {
        // ...
    }
}

$app->used(new session());
```

### Not Found
```php
$app->notFound(function ($req, $res) {
    $res->withStatus(404)->json(['message' => 'Not Found']);
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
    function middleware($req, $res) {
        $userId = $req->getParam('userId');
        $model = new User();
        $user = yield $model->findById($userId);
        var_dump($user);
    }
  ```

### Develop
 Here is a tool to help you write web app [gharry](https://github.com/eclogue/gharry)
 It watch project file change and auto reload your server.
 [Ben](https://github.com/eclogue/ben) is a convenient config manager， I recommend use Ben to manage use config file.

### Benchmark
    Higher than php-fpm.

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


