<?php
/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/16
 * Time: 上午12:12
 */

namespace Courser\Http;


class Response
{

    public $response = '';

    public $headers = [];

    public $statusCode = 200;

    private $header;

    public $res;

    public $body;

    public function __construct()
    {
        $this->header = new Header(array());

    }


    public function setResponse(\Swoole\Http\Response $response)
    {
        $this->res = $response;
    }


    public function status($code)
    {
        $this->statusCode = $code;

        return $this;
    }


    public function header($field, $value)
    {
        $this->headers[$field] = $value;
    }


    public function getHeaders()
    {
        return $this->header;
    }

    public function json($data)
    {
        if (is_array($data)) {
            $data = json_decode($data);
        } else {
            $data = (array)$data;
        }

        $this->end($data);
    }


    public function end($data)
    {
        foreach ($this->headers as $key => $value) {
            $this->res->header($key, $value);
        }
        $this->res->status($this->statusCode);
        $this->res->end($data);
    }

    public function send($str)
    {

        $this->end($str);
    }

    public function sendFile($file)
    {
        $this->res->sendFile($file);
    }

    public function write($data)
    {
        $this->req->write($data);
    }

    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    public function __invoke($string)
    {
        $this->end($string);
    }

    public function __toString()
    {
        $output = sprintf(
            'HTTP/%s %s %s',
            $this->statusCode
        );
//        $output .= PHP_EOL;
//        foreach ($this->getHeaders() as $name => $values) {
//            $output .= sprintf('%s: %s', $name, $this->getHeaderLine($name)) . PHP_EOL;
//        }
//        $output .= PHP_EOL;
//        $output .= (string)$this->getBody();
        return $output;
    }

}