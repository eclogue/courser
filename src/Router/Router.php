<?php
/**
 * Created by PhpStorm.
 * User: crab
 * Date: 15-10-15
 * Time: 上午12:34
 */

namespace Barge\Router;

use Barge\Set\Config;
use Barge\Co\Coroutine;
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

    private $stack = [];

    public static $allowMethods = [
        'get',
        'post',
        'put',
        'delete',
        'options',
        'patch',
    ];

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
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
//        $route = rtrim($route, '/');
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
        $group = $this->mapGroup($uri);
        if ($group) { // @todo 去掉匹配部分
            $uri = $group;
        }
        $found = $this->mapRoute($method, $uri);
        if (!$found) {
            return 404;
        }

        $this->middlewares = array_merge($this->middlewares, $found);
        $this->middleware();
        return true; // @todo
    }


    private function middleware()
    {
        $compose = new Compose();
        foreach ($this->middlewares as $middleware) {
            $generator = null;
            if (is_array($middleware)) {
                foreach ($middleware as $class => $action) {
                    $method = $action;
                    $key = 'Barge.classes.' . $class;
                    $obj = Config::get($key);
                    if (!$obj) {
                        $obj = new $class();
                        Config::set($key, $obj);
                    }
                    $generator = call_user_func_array([$obj, $method], [$this->request, $this->response]);

                }
            } else {
                $generator = $middleware($this->request, $this->response);
            }
            if ($generator === null) continue;
            if ($generator instanceof \Generator) {
                $compose->add($generator);
            }
        }
        $compose->run();
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
                    $key = 'Barge.classes.' . $class;
                    $obj = Config::get($key);
                    if (!$obj) {
                        $obj = new $class();
                        Config::set($key, $obj);
                    }
                    yield call_user_func_array([$obj, $method], [$this->request, $this->response]);
                }
            } else {
                yield $callback($this->request, $this->response);
            }
        } catch (\Exception $err) {
            $this->handleError($err);
        }
    }
}