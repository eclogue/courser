<?php
/**
 * Created by PhpStorm.
 * User: crab
 * Date: 15-10-15
 * Time: 上午12:34
 */

namespace Barge\Router;

use Barge\Http\Request;
use Barge\Http\Response;
use Barge\Co\Compose;

class Router
{
    public $routes = [];

    public $patterns = [];

    public $request;

    public $response;

    public $middlewares = [];

    public $container = [];

    private $groups = [];

    public static $allowMethods = [
        'get',
        'post',
        'put',
        'delete',
        'options',
        'patch',
    ];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
//        $this->queue = new \SplQueue();
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function group($group, $callable, $scope = null)
    {
        $group = rtrim($group) . '/';
        if (substr(strlen($group) - 1, 1) !== '*') $group .= '*';
        $pattern = $this->getPattern($group);
        $pattern = "#^$pattern#";
        if ($callable instanceof \Closure && $scope) {
            $callable = $callable->bindTo($scope, $scope);
        }
        $this->groups[$pattern][] = $callable;
    }

    /*
     * $route
     *
     * */
    public function addRoute($method, $route, $callback)
    {
        $method = strtolower($method);
        $pattern = $this->getPattern($route);
        if (substr(strlen($route) - 2, 1) === '*') {
            $pattern .= '';
        }
        if ($pattern) {
            $this->patterns[$route] = "#^$pattern$#";
        }
        $this->routes[$method][$route] = $this->isIndexArray($callback) ? $callback : [$callback];
        $this->request->methods[] = strtolower($method);
    }

    public function isIndexArray($node)
    {
        if (!is_array($node)) return false;
        $keys = array_keys($node);
        return is_numeric($keys[0]);
    }


    private function getPattern($route)
    {
        return preg_replace_callback('#:([\w]+)|{([\w]+)}|(\*)#', array($this, 'mapPattern'), $route);
    }

    public function addMiddleware($middleware)
    {
        if (!is_array($middleware)) return $this->middlewares[] = $middleware;
        return $this->middlewares = array_merge($this->middlewares, $middleware);
    }

    private function mapPattern($match)
    {
        $name = array_pop($match);
        $type = $match[0][0];
        if ($type === '*') {
            return '(.*)';
        }
        $type = $type === ':' ? '\d' : '\w';
        $this->request->addParamName($name);
        return "(?P<$name>[$type]+)";
    }

    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    public function mapRoute($method, $path)
    {
        $method = strtolower($method);
        if (!in_array($method, $this->request->methods)) return false;
        if ($path === '/') {
            return isset($this->routes[$method][$path]) ? $this->routes[$method][$path] : false;
        }
        if(!count($this->patterns)) return false;
        foreach ($this->patterns as $regex => $pattern) {
            preg_match($pattern, $path, $match);
            if (!$match) continue;
            foreach ($match as $param => $value) {
                if (in_array($param, $this->request->paramNames)) {
                    if (is_string($param)) {
                        $this->request->setParam($param, $value);
                    }
                }
            }
            return $this->routes[$method][$regex];
        }

        return false;
    }

    public function mapGroup($path)
    {
        $path = $path ?: '*';
        if (!count($this->groups)) return null;
        foreach ($this->groups as $key => $callable) {
            preg_match($key, $path, $match);
            if (!$match) continue;
            $postfix = '/' . substr($path, strlen($path) - strlen($match[1]));
            foreach ($callable as $func) {
                call_user_func($func);
            }
            return $postfix;
        }

        return false;
    }

    public function dispatch($uri)
    {
        $uri = $uri ?: '/';
        $method = $this->request->method;
        $group = $this->mapGroup($uri);
        if ($group) { // @todo 去掉匹配部分
            $uri = $group;
        }
        $found = $this->mapRoute($method, $uri);
        if (!$found) {
            return 404;
        }

        $this->compose($this->middlewares);
        $this->compose($found);
        return true;
    }


    private function compose($middleware)
    {
        $compose = new Compose();
        foreach ($middleware as $md) {
            $gen = $md($this->request, $this->response);
            $compose->push($gen);
        }
        $compose->run();
    }


    public function handleError($err)
    {
        throw $err;
    }


}