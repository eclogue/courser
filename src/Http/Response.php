<?php
/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/16
 * Time: 上午12:12
 */

namespace Barge\Http;

use Barge\Http\StdObject;
use Barge\Http\Header;

class Response
{

    public $response = '';

    public $headers = [];

    public $statusCode = 200;

    private $header;

    public $res;

    public function __construct()
    {
        $this->header = new Header(array());

    }


    public function setResponse($response)
    {
        $this->res = $response;
    }


    public function status($code) {
        $this->statusCode = $code;

        return $this;
    }


    public function header($field, $value)
    {
        $this->headers[$field] = $value;
    }


    public function getHeaders() {
        return $this->header;
    }

    public function json($data)
    {
        if (is_array($data)) {
            $data = json_decode($data);
        }

        $this->end($data);
    }


    public function setETag($ETag)
    {
        preg_match('/^(W\/)?"/', $ETag, $match);
        if (!$match) {
            $ETag = '"' . $ETag . '"';
        }
        $this->header('ETag', $ETag);
    }



    public function end($data)
    {
        foreach($this->headers as $key => $value) {
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

    public function getHeader()
    {

    }

    public function __invoke($string)
    {
        $this->end($string);
    }

    public function __toString()
    {
        $output = sprintf(
            'HTTP/%s %s %s',
//            $this->getProtocolVersion(),
            $this->statusCode
//            $this->getReasonPhrase()
        );
        $output .= PHP_EOL;
        foreach ($this->getHeaders() as $name => $values) {
            $output .= sprintf('%s: %s', $name, $this->getHeaderLine($name)) . PHP_EOL;
        }
        $output .= PHP_EOL;
        $output .= (string)$this->getBody();
        return $output;
    }

}