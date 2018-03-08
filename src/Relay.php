<?php
/**
 * @license   MIT
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午1:04
 */
namespace Courser;

use Hayrick\Http\Stream;
use Hayrick\Environment\RelayAbstract;
use Psr\Http\Message\StreamInterface;

class Relay extends RelayAbstract
{
    public $server = [];

    public $cookie = [];

    public $files = [];

    public $headers = [];

    public $query = [];

    public $request;

    public $body;

    public function __construct(
        array $server,
        array $headers,
        array $cookie,
        array $files,
        array $query,
        StreamInterface $stream
    ) {
        $this->server = $server;
        $this->headers = $headers;
        $this->cookie = $cookie;
        $this->files = $files;
        $this->query = $query;
        $this->body = $stream;
    }


    /**
     * build Relay
     *
     * @return Relay
     */
    public static function createFromGlobal(): Relay
    {
        $server = array_change_key_case($_SERVER, CASE_LOWER);
        $cookie = array_change_key_case($_COOKIE, CASE_LOWER);
        $files = array_change_key_case($_FILES, CASE_LOWER);
        $query = $_GET;
        $headers = [];
        if (!function_exists('getallheaders')) {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $key = strtolower(str_replace('_', ' ', substr($name, 5)));
                    $key = str_replace(' ', '-', $key);
                    $headers[$key] = $value;
                }
            }
        }

        if (!isset($server['http_host']) && isset($headers['http_host'])) {
            $server['http_host'] = $headers['https_host'];
        }

        $stream = fopen('php://temp', 'w+');
        stream_copy_to_stream(fopen('php://input', 'r'), $stream);
        rewind($stream);
        $body = new Stream($stream);
        $relay = new static(
            $server,
            $headers,
            $cookie,
            $files,
            $query,
            $body
        );

        return $relay;
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
}
