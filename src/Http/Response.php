<?php

namespace Courser\Http;

use Courser\Interfaces\ResponseInterface;

class Response extends ResponseAbstract implements ResponseInterface
{

    public $response = '';

    /*
     * @var array
     * */
    public $headers = [];

    /*
     * @var integer
     * */
    public $statusCode = 200;

    /*
     * @var header
     * */
    private $header;

    /*
     * store the \Swoole\Http\Response instance
     * */
    public $res;

    /*
     * send body
     * */
    public $body;


    public function __construct()
    {
        $this->header = new Header(array());

    }

    /*
     * set request context
     * @param object $req  \Swoole\Http\Request
     * @return void
     * */
    public function setResponse(\Swoole\Http\Response $response)
    {
        $this->res = $response;
    }


    public function status($code)
    {
        $this->statusCode = $code;

        return $this;
    }

    /*
     * set response header
     * @param string $field
     * @param mixed $value
     * @return void
     * */
    public function header($field, $value)
    {
        $this->headers[$field] = $value;
    }

    /*
     * get all response headers
     * */
    public function getHeaders()
    {
        return $this->header;
    }

    /*
     * set content-type = json,and response json
     * @param array | iterator $data
     * */
    public function json($data)
    {
        if (is_array($data)) {
            $data = json_decode($data);
        } else {
            $data = (array)$data;
        }

        $this->header('Content-Type', 'application/json');
        $this->end($data);
    }

    /*
     * finish request
     * @param mix $data
     * */
    public function end($data)
    {
        foreach ($this->headers as $key => $value) {
            $this->res->header($key, $value);
        }
        $this->res->status($this->statusCode);
        $this->res->end($data);
    }

    /*
     * send string and finish request
     * @param mix $data
     * */
    public function send($str)
    {
        $this->end($str);
    }

    /*
     * send file extend swoole_http_response
     * @param string $file
     * */
    public function sendFile($file)
    {
        $this->res->sendFile($file);
    }

    /*
     * write chunk data extend from swoole_http_response
     * @param mixed $data
     * */
    public function write($data)
    {
        $this->req->write($data);
    }

    /*
     * get header by key
     * */
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
        return $output;
    }

}