<?php

namespace Courser\Http;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use InvalidArgumentException;

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


    public function __construct()
    {
        $this->headers = new Header();
        $this->res = null;

    }

    public function __clone()
    {
        $this->headers = clone $this->headers;
    }

    /**
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return \swoole_http_response
     */
    public function getOriginResponse()
    {
        return $this->res;
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

        $this->withHeader('Content-Type', 'application/json');
        $this->end($data);
    }

    /*
     * finish request
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param mix $data
     * */
    public function end($data = '')
    {
        if($this->finish) {
            throw new RuntimeException('Request has been response, check your code for response');
        }
        $this->finish = true;
        $response = $this->getOriginResponse();
        $headers = $this->getHeaders();
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }
        $response->status($this->statusCode);
        $response->end($data);
    }

    /*
     * send string and finish request
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param mix $data
     * */
    public function send($str = '')
    {
        $this->end($str);
    }

    /*
     * send file extend swoole_http_response
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $file
     * */
    public function sendFile($file)
    {
        $this->getOriginResponse()->sendFile($file);
    }

    /*
     * write chunk data extend from swoole_http_response
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param mixed $data
     * */
    public function write($data)
    {
        $this->getOriginResponse()->write($data);
    }

    // ===================== PSR-7 standard ===================== //

    /*
     * set request context
     * @param object $req  \Swoole\Http\Request
     * @return void
     * */
    public function createResponse($response)
    {
        $clone = clone $this;
        $clone->res = $response;
        return $clone;
    }



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