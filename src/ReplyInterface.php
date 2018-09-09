<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/9/8
 * @time: 下午6:38
 */

namespace Courser;

use Psr\Http\Message\ResponseInterface;

interface ReplyInterface
{
    public function end(ResponseInterface $response);

}