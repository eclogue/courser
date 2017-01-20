# Barge
Just for fun

### initialize

```php
$config = [];
$root = './';
$app = new Barge();
$app->init($root, $config);
$app->get('/', function($req, $res) {
    $res->send('fuck world');
});
$app->run();

```
### Router
```php

 # /users/11
 $app->get('/users/:id', function($req, $res) {
    var_dump($req->params['id']); // id must be integer
  });
 
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
