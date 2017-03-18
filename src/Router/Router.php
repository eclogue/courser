<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */

namespace Courser\Router;

use Courser\Courser;
use Courser\Helper\Util;
use Courser\Http\Request;
use Courser\Http\Response;
use Courser\Co\Compose;

class Router
{
    public $routes = [];

    public $patterns = [];

    public $request;

    public $response;

    public $middlewares = [];

    public $container = [];

    private $groups = [];

    private $namespace = '/';

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
    }

    public function group($group, $callable)
    {
        if ($group === '/') return null;
        $group = '/' . trim($group, '/');
        $prefix = $group;
        if (strlen($prefix) > 1) {
            $prefix .= '/*';
        }
        $pattern = $this->getPattern($prefix);
        $this->namespace = '#^' . $pattern . '#';
        $this->groups[$this->namespace] = $group;
        if ($callable instanceof \Closure) {
            $callable = $callable->bindTo($this);
        }
        $callable();
        $this->resetNamespace();
    }

    private function resetNamespace()
    {
        $this->namespace = '/';
    }

    /*
     * $route
     *
     * */
    public function addRoute($method, $route, $callback)
    {

        $method = strtolower($method);
        $route = '/' . trim($route, '/') . '/';
        if ($this->namespace !== '/' && isset($this->groups[$this->namespace])) {
            $route = $this->groups[$this->namespace] . $route;
        }
        $pattern = $this->getPattern($route);
        if ($pattern) {
            $this->patterns[$route] = '#^' . $pattern . '#';
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

    public function addMiddleware($middleware, $namespace = '')
    {
        if ($namespace) {
            $this->namespace = $namespace;
        }
        $this->middlewares[$this->namespace][] = $middleware;
    }

    public function get($route, $callback)
    {
        $this->addRoute('get', $route, $callback);
    }

    public function post($route, $callback)
    {
        $this->addRoute('post', $route, $callback);
    }

    public function put($route, $callback)
    {
        $this->addRoute('put', $route, $callback);
    }

    public function delete($route, $callback)
    {
        $this->addRoute('delete', $route, $callback);
    }

    public function used($middleware)
    {
        $this->addMiddleware($middleware, $this->namespace);
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
        if (!count($this->patterns)) return false;
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


    public function dispatch($uri)
    {
        $uri = $uri ? rtrim($uri) . '/' : '/';
        $method = $this->request->method;
        $found = $this->mapRoute($method, $uri);
        if (!$found) {
            return $this->compose(Courser::$notFounds);
        }

        $md = [];
        foreach ($this->middlewares as $key => $middleware) {
            if ($key === '/') {
                $md = array_merge($md, $middleware);
            } else {
                preg_match($key, $uri, $match);
                if ($match) $md = array_merge($md, $middleware);
            }
        }

        $this->compose($md);
        $this->compose($found);
        return true;
    }


    private function compose($middleware)
    {
        $compose = new Compose();
        foreach ($middleware as $md) {
            $gen = null;
            if (is_array($md)) {
                if (Util::isIndexArray($md)) {
                    $this->compose($md);
                    continue;
                }
                foreach ($md as $class => $action) {
//                    $class = str_replace('\\', '\\\\', $class);
                    $ctrl = new $class($this->request, $this->response);
                    $gen = $ctrl->$action();
                }
            } else {
                if (!is_callable($md)) continue;
                $gen = $md($this->request, $this->response);
            }
            if($gen instanceof \Generator) {
                $compose->push($gen);
            }
        }
        $compose->run();
    }


    public function handleError($err)
    {
        throw $err;
    }


}