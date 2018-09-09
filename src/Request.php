<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/9/8
 * @time: 下午5:37
 */

namespace Courser;

use Slim\Http\Request as SlimRequest;
class Request extends SlimRequest
{

    public static function createByRelay(Relay $relay)
    {
        return new SlimRequest(...$relay->toArray());
    }

}