<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/5/6
 * @time: 下午11:19
 */

namespace Courser\Traits;


trait Facade
{
    public static $container = [];

    public static $name = '';

    public static $instance = null;


    public static function make($name, $container)
    {
        static::$name = $name;
        if (empty(self::$container)) {
            self::$container = $container;
        }
    }


    public static function __callStatic($method, $args)
    {
        if (stripos($method, 'make') !== false) {
            $method = lcfirst(mb_substr($method, 4));
        }
        $instance = self::$container[static::$name];
        $instance->$method(...$args);
    }
}