<?php
/**
 * @license https://github.com/racecourse/courser/licese.md
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/5/13
 * @time: 下午4:32
 */

date_default_timezone_set('Asia/Shanghai');
// Enable Composer autoloader
/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
// Register test classes
$autoloader->addPsr4('Courser\Tests\\', __DIR__);