<?php

namespace Courser\Http;

use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{

    protected $response = '';

    /*
     * @var array
     * */
    protected $headers = [];

    /*
     * @var integer
     * */
    protected $statusCode = 200;


    /*
     * store the \Swoole\Http\Response instance
     * */
    protected $res;

    /*
     * send body
     * */
    protected $body;


    public $finish = false;

    protected $messages = [];


    public function __construct()
    {
        $this->headers = Header::defaultHeader();

    }

    // ===================== PSR-7 standard =====================

    /*
     * set request context
     * @param object $req  \Swoole\Http\Request
     * @return void
     * */
    public function createResponse($response)
    {
        $this->res = $response;
    }

    public function getOriginResponse()
    {
        return $this->res;
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
        return $this->headers;
    }

    /*
     * set content-type = json,and response json
     * @param array | iterator $data
     * */
    public function json($data)
    {
        if (is_array($data)) {
            $data = json_encode($data);
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
    public function end($data = '')
    {
        if($this->finish) {
            throw new \Exception('Request has been response, check your code for response');
        }
        $this->finish = true;
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
    public function send($str = '')
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
        $output = ''; // @todo
        return $output;
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {

        return $this;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {

    }


}