<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午1:04
 */
namespace Courser\Interfaces;


abstract class RequestInterface
{
    public $server = [];

    public $cookie = [];

    public $files = [];

    public $headers = [];

    public function init()
    {

    }

    /*
     * add param name
     * @param string $name
     * @return void
     * */
    public function addParamName($name)
    {

    }

    /*
     * set param
     * @param string $key
     * @param string $val
     * */
    public function setParam($key, $val) {

    }

    public function __call($name, $arguments)
    {

    }

    public function __get($key, $value)
    {

    }
}