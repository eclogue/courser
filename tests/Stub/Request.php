<?php

/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/5/12
 * @time: 下午7:52
 */
namespace Courser\Tests\Stub;


class Request
{
    public $header = [];

    public $server = [];

    public $get = [];

    public $post = [];

    public $cookie = [];

    public $files = [];

    public $fd = 1;


    public function rawContent() {
        return '';
    }

    public function header($name, $default = '') {
        return $default;
    }

    public function cookie(...$args) {
        return true;
    }

    public function status($status) {
        return true;
    }

    public function gzip() {
        return true;
    }

    public function write($chunk = '') {
        return true;
    }

    public function sendFile($file) {
        return true;
    }

    public function send() {

    }

    public function end() {
        return true;
    }

}