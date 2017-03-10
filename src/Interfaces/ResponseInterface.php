<?php

/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午1:04
 */

namespace Courser\Interfaces;

interface ResponseInterface
{


    public function status($code);

    /*
     * set response header
     * @param string $field
     * @param mixed $value
     * @return void
     * */
    public function header($field, $value);

    /*
     * get all response headers
     * */
    public function getHeaders();

    /*
     * set content-type = json,and response json
     * @param array | iterator $data
     * */
    public function json($data);

    /*
     * finish request
     * @param mix $data
     * */
    public function end($data);

    /*
     * send string and finish request
     * @param mix $data
     * */
    public function send($str);

    /*
     * send file extend swoole_http_response
     * @param string $file
     * */
    public function sendFile($file);

    /*
     * write chunk data extend from swoole_http_response
     * @param mixed $data
     * */
    public function write($data);

    /*
     * get header by key
     * */
    public function getHeader($key);

}