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

class Response extends StdObject
{

    public $response = '';

    public $headers = [];

    private $header;

    public function __construct(array $arguments = [])
    {
        $this->header = new Header(array());
        parent::__construct($arguments);
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
        $this->header('Content-Type', 'application/json');
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

    public function redirect($url)
    {

    }


    public function end($data)
    {

        $this->getHeader();
        exit($data);
    }

    public function send($str)
    {

        $this->end($str);
    }

    public function sendFile($str)
    {

    }

    public function getHeader()
    {

    }

    public function __invoke($request)
    {
        return $this;
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