<?php
/**
 * @license   MIT
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午1:04
 */
namespace Courser\Environment;

use InvalidArgumentException;

class Incoming
{
    public $server = [];

    public $cookie = [];

    public $files = [];

    public $headers = [];

    public $request;

    public $body = [];

    public function __construct($request = null)
    {
        if (!$request) {
            $this->server = array_change_key_case($_SERVER, CASE_LOWER);
            $this->cookie = array_change_key_case($_COOKIE, CASE_LOWER);
            $this->files = array_change_key_case($_FILES, CASE_LOWER);
            if (!function_exists('getallheaders'))
            {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $key = strtolower(str_replace('_', ' ', substr($name, 5)));
                        $key = str_replace(' ', '-', $key);
                        $headers[$key] = $value;
                    }
                }
                $this->headers = $headers;
            } else {
                $this->headers = getallheaders();
            }
        } else {
            $this->server = $request->server;
            $this->cookie = $request->cookie;
            $this->files = $request->files;
        }

        $this->request = $request;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __call($name, $arguments)
    {
        var_dump($this->request->$name, $name, $arguments);
        if (is_callable([$this->request, $name], true)) {
            return call_user_func_array([$this->request, $name], $arguments);
        } else {
            $message = 'Call undefined function of ' . get_class($this->request);
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __get($name)
    {
        if (property_exists($this->request, $name)) {
            return $this->request->$name;
        } else {
            $message = 'Try to get Illegal property `%s` of %s';
            $message = sprintf($message, $name, get_class($this->request));
            throw new InvalidArgumentException($message);
        }
    }


}