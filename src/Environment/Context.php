<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/10/12
 * @time: 上午11:01
 */

namespace Courser\Environment;


class Context
{
    protected $attributes;

    protected $methods = [];

    protected $request;

    protected $response;

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }


    public function extend($target)
    {
        $origin = new \ReflectionClass($target);
        $properties = $origin->getProperties(\ReflectionMethod::IS_PUBLIC);
        $methods = $origin->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $key => $method) {
            $name = $method['name'];
            if (!isset($this->methods[$name])) {
                $this->methods[$name] = function (...$args) use ($target, $name)
                {
                    $target->$name(...$args);
                };
            }
        }

        foreach ($properties as $key => $attr) {
            $name = $attr['name'];
            $this->attributes[$name] = $target->$name;
        }


    }

    public function send()
    {

    }

    /**
     * @param $name string
     * @return null
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param $name string
     * @param $value mixed
     * @return bool
     */
    public function __set($name, $value)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name] = $value;
        }

        return false;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (isset($this->methods[$name])) {
            return ($this->methods[$name])(...$arguments);
        }

        return false;
    }


}