# Course

A fast and lighter PHP micro framework used swoole.The greatest truths are the simplest,
 `entities should not be multiplied unnecessarily.` Born with natural beauty.

### Installation
`composer require racecourse/courser` or git clone https://github.com/racecourse/courser
### Get start

`composer install` 

Create a new file server.php.

```php
<?php
require('./vendor/autoload.php');
use Course\Course;
$config = [
    'server' => [
        'host' => '0.0.0.0',
        'port' => '5001'
    ]
];

Course::use(function($req, $res) {
   yield;
   echo "this middleware 1 \n";
});

Course::use(function($req, $res) {
    yield;
    echo "this middleware 2 \n";
});
Course::get('/', function($req, $res) {
    $html = "<h1> fuck world</h1>";
    $res->header('Content-Type', 'text/html');
    $res->send($html);
});

$server = new \Course\Server\HttpServer($config);

$server->start();
?>
```
now run `php server.php`, visit 127.0.0.1:5001

### Router


```php
<?php
# basic /users/11
Course::get('/users/:id', function($req, $res) {
    var_dump($req->params['id']); // id must be integer
    yield 1000;
});
Course::get('/users/:id', function($req, $res) {
    $value = (yield); // $value === 1000;
    $res->send($value);
});
# use array
Course::get('users/*', [function($req, $res) {
    /* do something*/
}, function($req, $res) {
    /*...todo*/
}]);

# use namespace
Course::put('/user/{username}', ['MyNamespace\Controller', 'action']);

# use group

Course::group('/admin/{username}',  function() {
    // [Notice: In group `$this` is bind to Courser\Router, don't use Courser::[method]()]
    
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
    
   Use `Courser::used(function($req, $res) {});` to add a middleware.
   Course's middleware is like [koa](https://github.com/koajs/koa).In koa, everything is middleware.
   But Courser split middleware and user business. Middleware are mount at group scope;The default group 
   is the root '/';
   A middleware must be a callable function or a instance that have `__invoke` function;
 


### Benchmark
    
    Just kidding.
     


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
