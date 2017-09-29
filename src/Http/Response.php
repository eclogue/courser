<?php

namespace Courser\Http;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use InvalidArgumentException;
use Courser\Environment\Output;

class Response extends Message implements ResponseInterface
{

    protected $response = '';

    /*
     * @var array
     * */
    protected $headers;

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

    protected $reasonPhrase;

    protected $context;


    public function __construct()
    {
        $this->headers = new Header();
        $this->context = new Output();
    }


    public function __clone()
    {
        $this->headers = clone $this->headers;
    }


    /*
     * set content-type = json,and response json
     * @param array | iterator $data
     * */
    public function json(array $data):Output
    {
        $data = json_encode($data);
        $this->withHeader('Content-Type', 'application/json');
        $this->end($data);
    }

    /*
     * finish request
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param mix $data
     * */
    public function end(string $data = ''):Output
    {
        if ($this->finish) {
            throw new RuntimeException('Request has been response, check your code for response');
        }
        $this->finish = true;
        $headers = $this->getHeaders();
        foreach ($headers as $key => $value) {
            $this->context->header($key, $value);
        }
        $this->context->status($this->statusCode);
        $this->context->body($data);

        return $this->context;
    }

    /**
     * @return Output
     */
    public function getContext(): Output
    {
        return $this->context;
    }


    // ===================== PSR-7 standard ===================== //

    public function withStatus($code, $reasonPhrase = '')
    {
        if (!is_string($reasonPhrase) && !method_exists($reasonPhrase, '__toString')) {
            throw new InvalidArgumentException('ReasonPhrase must be a string');
        }
        $clone = clone $this;
        $clone->statusCode = $code;
        if ($reasonPhrase === '' && isset(Header::$messages[$code])) {
            $reasonPhrase = Header::$messages[$code];
        }
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /*
     * set response header
     * @param string $field
     * @param mixed $value
     * @return void
     * */
    public function withHeader($field, $value)
    {
        $this->headers->setHeader($field, $value);
        $clone = clone $this;

        return $clone;
    }

    /*
     * get all response headers
     * */
    public function getHeaders()
    {
        return $this->headers->getHeaders();
    }


    /*
     * get header by key
     * */
    public function getHeader($key, $default = null)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : $default;
    }


    public function __toString()
    {
        return (string)$this->context;
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

        if ($this->reasonPhrase) {
            return $this->reasonPhrase;
        }
        if (isset(Header::$messages[$this->statusCode])) {
            return Header::$messages[$this->statusCode];
        }
        return '';
    }

}