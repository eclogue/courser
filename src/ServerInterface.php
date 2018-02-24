<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/2/24
 * @time: 上午11:55
 */

namespace Courser;


interface ServerInterface
{

    public function buildRequest();

    public function buildResponse();
}