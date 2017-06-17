# Course

![](https://travis-ci.org/racecourse/courser.svg?branch=master)

A fast and lighter PHP micro framework base on swoole. I believe that `The greatest truths are the simplest,
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

### Not Fund
```php
$app->notFound(function ($req, $res) {
    $res->status(404)->json(['message' => 'Not Found']);
});
```
### Exception
```php
$app->error(function ($req, $res, Exception $err) {
   $res->status(500)->json([
       'message' =>$err->getMessage(),
       'code' => 10502,
   ]);
});

```

### Develop
 I have write a tool for develop courser app ![gharry](https://github.com/racecourse/gharry)
 It watch project file change and auto reload your server.

### Benchmark
 Just for fun.
 
 
 Env: docker on mac, ubuntu16.10 PHP 7.0.15-0ubuntu0.16.04.4 (cli) ( NTS )
 
 util: apache ab
>
```
Requests per second:    9801.09 [#/sec] (mean)
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


