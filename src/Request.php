<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/9/8
 * @time: 下午5:37
 */

namespace Courser;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class Request extends ServerRequest
{

    public static function createByRelay(Relay $relay)
    {
        return new ServerRequest(...$relay->toArray());
    }


    public static function createFromGlobals()
    {
        return ServerRequestFactory::fromGlobals();
    }
}
