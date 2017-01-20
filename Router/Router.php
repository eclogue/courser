<?php
/**
 * Created by PhpStorm.
 * User: crab
 * Date: 15-10-15
 * Time: 上午12:34
 */

namespace Barge\Router;

use Barge\Init;
use Barge\Set\Config;

class Router
{
    public $routes = [];

    public $patterns = [];

    public $request;

    public $response;

    public $middleware = [];

    public $container = [];

    private $groups = [];

    public function __construct($request, $response)
    {
//        var_dump($request->server);
        $this->request = $request;
        $this->response = $response;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function group($group, $callable, Init $scope = null)
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
//        $route = rtrim($route, '/');
        $pattern = $this->getPattern($route);
        if (substr(strlen($route) - 2, 1) === '*') {
            $pattern .= '';
        }
        if ($pattern) {
            $this->patterns[$route] = "#^$pattern$#";
        }
        $this->routes[$method][$route] = is_array($callback) ? $callback : [$callback];
        $this->request->methods[] = strtolower($method);
    }

    private function getPattern($route)
    {
        return preg_replace_callback('#:([\w]+)|{([\w]+)}|(\*)#', array($this, 'mapPattern'), $route);
    }

    public function addMiddleware($group, $middleware)
    {
        if (!isset($this->middleware)) {
            $this->middleware[$group] = [];
        }
        $this->middleware[$group] = array_merge($this->middleware[$group], $middleware);
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
        if ($path === '/') { // without params
            return isset($this->routes[$method][$path]) ? $this->routes[$method][$path] : false;
        }
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
        $route = $this->middleware;
        $group = $this->mapGroup($uri);
        if ($group) { // @todo 去掉匹配部分
            $uri = $group;
        }
        $found = $this->mapRoute($method, $uri);
        if (!$found) {
            return 404;
        }
        $route = array_merge($route, $found);
        if ($route === false) {
            return 0; // method not allow 405
        }
        if (!count($route)) {
            return false; // return not found;
        }
        foreach ($route as $key => $callback) {
            if (is_string($key)) {
                $this->handle([$key => $callback]);
            } else {
                $this->handle($callback);
            }
        }
        return true; // @todo
    }


    public function handleError($err)
    {
        throw $err;
    }

    private function handle($callback)
    {
        try {
            if (is_array($callback)) {
                $obj = null;
                $method = null;
                foreach ($callback as $class => $action) {
                    $method = $action;
                    $key = 'init.classes.' . $class;
                    $obj = Config::get($key);
                    if ($obj) break;
                    $obj = new $class();
                    Config::set($key, $obj);
                }
                $next = call_user_func_array([$obj, $method], [$this->request, $this->response]);
            } else {
                $next = $callback($this->request, $this->response);
            }
            if ($next && is_callable($next)) {
                $this->handle($next);
            }
        } catch (\Exception $err) {
            $this->handleError($err);
        }
    }
}