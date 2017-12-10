<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/12/10
 * @time: 下午12:40
 */

namespace Courser\Server;


interface ServerInterface
{
    public function respond();

    public function buildRequest();
}