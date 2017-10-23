<?php

/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/5/12
 * @time: 下午7:52
 */
namespace Courser\Tests\Stub;

class Response
{
    public $header = [];

    public $server = [];

    public $get = [];

    public $post = [];

    public $cookie = [];

    public $files = [];

    public $statusCode = 200;

    public $fd = 1;

    public function rawContent()
    {
        return '';
    }

    public function json($data)
    {
        echo json_encode($data);
    }

    public function status($status = 200)
    {
        return $this->statusCode = 200;
    }

    public function end()
    {

    }

    public function write()
    {

    }
}