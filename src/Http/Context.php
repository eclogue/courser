<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/15
 * @time      : 下午1:48
 */

namespace Barge\Http;


class Context
{

    public $request;

    public $response;

    public function __construct($req, $res)
    {
        $this->request = $req;
        $this->response = $res;
    }

    public function __toString()
    {
        return '';
    }

    public function __invoke()
    {
        return [
            'request' => $this->toArray($this->request),
            'response' => $this->toArray($this->response)
        ];
    }

    public function __get($name)
    {

    }

    public function __set($name, $value)
    {

    }

    private function toArray($object)
    {
        $reflection = new \ReflectionClass($object);
        return $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
    }
}