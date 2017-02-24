# Barge
Just for fun

### get start

`composer install` 

create a new file server.php

```php
<?php
require('./vendor/autoload.php');
use Barge\Barge;
$config = [
    'server' => [
        'host' => '0.0.0.0',
        'port' => '5001'
    ]
];

Barge::use(function($req, $res) {
   yield;
   echo "this middleware 1 \n";
});

Barge::use(function($req, $res) {
    yield;
    echo "this middleware 2 \n";
});
Barge::get('/', function($req, $res) {
    $html = "<h1> fuck world</h1>";
    $res->header('Content-Type', 'text/html');
    $res->send($html);
});

$server = new \Barge\Server\HttpServer($config);

$server->start();
?>
```
now run `php server.php`, visit 127.0.0.1:5001

### Router
```php

 # basic /users/11
 $app->get('/users/:id', function($req, $res) {
    var_dump($req->params['id']); // id must be integer
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
  
  $app->group('/admin',  [ // match all `/admin` prefix uri: /admin/1;/admin/user/1
    function($req, $res) { /*...*/},
    ['MyNamespace\Controller', 'todo'],
  ]);
 ```
 
 ### Model
 
 used like mongo shell
 
  ```php
  $user = new Model('user');

$condition = [
    'id' => ['$gt' => 1, '$lt' => 100, '$neq' => 23],
    '$or' => [
        'email' => 'aaxx@scac.com',
        'status' => '1',
    ],
    'age' => ['$lt' => 70]
];
  $user->field('*')
    ->where($condition)
    ->order(['id' => 'desc'])
    ->skip(100)
    ->limit(10)
    ->select();
// execute sql: SELECT * FROM `user` WHERE (`id`>'1' and `id`<'100' and `id`!='23') or (`email`='aaxx@scac.com' and `status`='1') and (`age`<'70') ORDER BY `id` DESC limit 10 offset 100;

   ```
-------
#coding...

#maintain
mulberry10<[mulberry10th@gmail.com]()>
