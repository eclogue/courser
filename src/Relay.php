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
use SebastianBergmann\CodeCoverage\Report\PHP;

class Relay extends RelayAbstract
{
    public $server = [];

    public $cookie = [];

    public $files = [];

    public $headers = [];

    public $query = [];

    public $request;

    public $body;


    /**
     * build Relay
     *
     * @return Relay
     */
    public static function createFromGlobal(): Relay
    {
        $server = $_SERVER;
        $cookie = $_COOKIE;
        $files =    $_FILES;
        $query = $_GET;
        $headers = [];
        if (!function_exists('getallheaders') || empty(getallheaders())) {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $key = strtolower(str_replace('_', ' ', substr($name, 5)));
                    $key = str_replace(' ', '-', $key);
                    $headers[$key] = $value;
                }
            }
        } else {
            $headers = getallheaders();
        }

        if (!isset($server['http_host']) && isset($headers['http_host'])) {
            $server['http_host'] = $headers['https_host'];
        }

        $stream = fopen('php://temp', 'w+');
        stream_copy_to_stream(fopen('php://input', 'r'), $stream);
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
