<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/2/2
 * @time: 下午2:56
 */

namespace Courser;


class Route
{
    public $method = 'get';

    public $path = '/';

    public $callable = [];

    public $group = '/';

    public $scope = 1;

    public $paramNames = [];

    public $middleware;

    protected $pattern = '';


    public function __construct(string $method, string $route, callable $callable, int $scope = 1, string $group = '/')
    {
        $this->method = $method;
        $this->route = $route;
        $this->callable[] = $callable;
        $this->group = $group;
        $this->scope = $scope;
        $this->middleware = new \SplQueue();
        $this->getPattern();
    }


    /**
     * @param $route string
     * @return void
     */
    private function getPattern()
    {
        $params = [];
        $regex = preg_replace_callback(
            '#:([\w]+)|{([\w]+)}|(\*)#',
            function ($match) use (&$params) {
                $name = array_pop($match);
                $type = $match[0][0];
                if ($type === '*') {
                    return '(.*)';
                }
                $type = $type === ':' ? '\d' : '\w';
                $params[] = $name;
                return "(?P<$name>[$type]+)";
            },
            $this->route
        );

        $this->pattern = $regex;
        $this->paramNames = $params;
    }

    public function add(callable $callable)
    {
        $this->callable[] = $callable;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setParamName(string $name, string $value)
    {
        $this->paramNames[$name] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $method
     * @param string $path
     * @return null|array
     */
    public function find(string $method, string $path)
    {
        if ($this->method !== $method) {
            return null;
        }

        preg_match($this->pattern, $path, $match);
        if (empty($match)) {
            return null;
        }

        return $match;
    }
}