<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/2/4
 * @time: 下午3:18
 */
define('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';

use Courser\Route;

$test = function () {

};

$route = new Route('get', '/test/:id', $test);
