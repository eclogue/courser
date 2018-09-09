<?php
/**
 * @license   MIT
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午1:04
 */
namespace Courser;

use Psr\Http\Message\UriInterface;
use Slim\Http\Stream;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Uri;
use Slim\Http\Headers;

class Relay
{
    public $server = [];

    public $cookie = [];

    public $files = [];

    public $headers = [];

    public $query = [];

    public $request;

    public $body;

    public $uri;


    /**
     * build Relay
     *
     * @return Relay
     */
//    public static function createFromGlobal(): Relay
//    {
//        $server = $_SERVER;
//        $cookie = Headers::createFromGlobals($_COOKIE);;
//        $files =    $_FILES;
//        $query = $_GET;
//        $headers = [];
//        if (!function_exists('getallheaders') || empty(getallheaders())) {
//            foreach ($_SERVER as $name => $value) {
//                if (substr($name, 0, 5) == 'HTTP_') {
//                    $key = strtolower(str_replace('_', ' ', substr($name, 5)));
//                    $key = str_replace(' ', '-', $key);
//                    $headers[$key] = $value;
//                }
//            }
//        } else {
//            $headers = getallheaders();
//        }
//
//        if (!isset($server['http_host']) && isset($headers['http_host'])) {
//            $server['http_host'] = $headers['https_host'];
//        }
//
//        $stream = fopen('php://temp', 'w+');
//        stream_copy_to_stream(fopen('php://input', 'r'), $stream);
//        $body = new Stream($stream);
//        $uri = Uri::createFromGlobals($_SERVER);
//        $relay = new static(
//            $server,
//            $headers,
//            $cookie,
//            $files,
//            $query,
//            $body,
//            $uri
//        );
//
//        return $relay;
//    }

    public function __construct(
        array $server,
        array $headers,
        array $cookie,
        array $files,
        array $query,
        StreamInterface $stream,
        UriInterface $uri
    )
    {
        $this->server = $server;
        $this->headers = $headers;
        $this->cookie = $cookie;
        $this->files = $files;
        $this->query = $query;
        $this->body = $stream;
        $this->uri = $uri;
    }


    /**
     * @param null $parser body parser
     * @return mixed
     */
    public function getBody(callable $parser = null)
    {
        if (is_callable($parser)) {
            return $parser($this->body);
        } else {
            return $this->body;
        }
    }

    public function toArray(): array
    {
        return [
            $this->server,
            $this->headers,
            $this->cookie,
            $this->files,
            $this->body,
            $this->query,
        ];
    }

}
