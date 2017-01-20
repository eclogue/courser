<?php
/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/20
 * Time: 下午2:15
 */

namespace Barge\Http;


abstract class StdObject
{

    public $extend = [];

    public function __construct(array $arguments = [])
    {
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                $this->{$property} = $argument;
            }
        }

    }

    public function extend($request)
    {
        $reflection = new \ReflectionClass($request);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $prop) {
            $property = $prop->getName();
            $this->{$property} = $request->{$property};
        }

        $methods = $reflection->getMethods();
        foreach ($methods as $key => $value) {
            $name = $value->name;
            $this->$name = function($arguments) use ($request, $name) {
                return $request->$name($arguments);
            };
        }
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        return null;
    }


    public function __call($method, $arguments)
    {
        $arguments = array_merge(array("stdObject" => $this), $arguments);
        if (isset($this->{$method}) && is_callable($this->{$method})) {
            return call_user_func_array($this->{$method}, $arguments);
        } else {
            throw new \Exception("Fatal error: Call to undefined method stdObject::{$method}()");
        }
    }
}