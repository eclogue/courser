<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */

namespace Courser;

use Courser\Helper\Config;
use Courser\Helper\Util;
use Courser\Router\Router;
use Courser\Http\Request;
use Courser\Http\Response;
use Pimple\Container;


class Courser
{
    public $notFounds = [];
    /*
     * instance env
     * @var array
     * */
    public $env = [];

    /*
     * global middle ware
     * @var array
     * */
    public $middleware = [];

    /*
     * @var array
     * */
    public $routes = [];

    /*
     * @var array
     * */
    public $group = '/';

    public $stack = [];

    private $methods = [
        'get',
        'post',
        'delete',
        'put',
        'options'
    ];

    public $container = [];

    public function __construct($env = 'dev')
    {
        $this->env = $env;
        $container = new Container();
        $container['courser.request'] = $container->factory(function ($c) {
            return new Request();
        });
        $container['courser.response'] = $container->factory(function ($c) {
            return new Response();
        });
        $container['courser.router'] = $container->factory(function ($c) {
            return new Router($c['courser.request'], $c['courser.response']);
        });
        $this->container = $container;
        spl_autoload_register([$this, 'loader']);
    }

    /*
     * create request context set req and response
     * @param object $req Swoole\Http\Request
     * @param object $res Swoole\Http\Response
     * @return object self
     * */
    public function createContext($req, $res)
    {
        $router = $this->container['courser.router'];
        $router->response->setResponse($res);
        $router->request->setRequest($req);
        return $router;
    }

    /*
     * add a middleware
     * @param function | object $callable callable function
     * @return void
     * */
    public function used($callable)
    {
        $this->middleware[] = [
            'group' => $this->group,
            'middleware' => $callable
        ];
    }

    /*
     * add a group route,the callback param is bind to router instance
     * it should use $this->$method to add route
     * @param string $group
     * @param function | array $callable
     *
     * @return void
     * */
    public function group($group, $callback)
    {
        if (!is_string($group)) throw new \Exception('Group name must be string');
        $group = rtrim($group, '/');
        $this->group = $group;
        if ($callback instanceof \Closure) {
            $callback = $callback->bindTo($this);
        }
        $callback();
        $this->group = '/';
    }

    public function mapMiddleware($uri, $deep = 1)
    {
        $md = [];
        if (empty($this->middleware)) return $md;
        $tmp = $this->middleware;
        $apply = array_splice($tmp, $deep - 1);
        foreach ($apply as $index => $middleware) {
            $group = '#^' . $middleware['group'] . '(.*)#';
            preg_match($group, $uri, $match);
            if (empty($match)) continue;
            $md[] = $middleware['middleware'];
        }
        return $md;
    }

    public function addRoute($method, $route, $callback)
    {
        $method = strtolower($method);
        $route = trim($this->group . $route, '/');
        $route = implode('/', [$route]);
        $route = '/' . $route;
        $scope = count($this->middleware);
        list($pattern, $params) = $this->getPattern($route);
        if ($pattern) {
            $pattern = '#^' . $pattern . '$#';
        }
        $callback = Util::isIndexArray($callback) ? $callback : [$callback];
        $this->routes[$method][] = [
            'route' => $route,
            'params' => $params,
            'pattern' => $pattern,
            'callable' => $callback,
            'scope' => $scope,
        ];
    }

    public function mapRoute($method, $uri, $router)
    {
        $method = strtolower($method);
        if (empty($this->routes[$method])) return $router;
        foreach ($this->routes[$method] as $route) {
            preg_match($route['pattern'], $uri, $match);
            if (empty($match)) continue;
            if ($route['scope']) {
                $middleware = $this->mapMiddleware($uri, $route['scope']);
                if (!empty($middleware)) $router->used($middleware);
            }
            $router->method($method);
            $router->add($route['callable']);
            $router->paramNames = array_merge($router->paramNames, $route['params']);
            foreach ($match as $param => $value) {
                if (in_array($param, $router->paramNames)) {
                    if (is_string($param)) $router->setParam($param, $value);
                }
            }
        }
        return $router;
    }

    private function getPattern($route)
    {
        $params = [];
        $regex = preg_replace_callback('#:([\w]+)|{([\w]+)}|(\*)#',
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
            $route);
        return [$regex, $params];
    }


    /*
     * add get method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public function get($route, $callback)
    {
        $this->addRoute('get', $route, $callback);
    }


    /*
     * add a post method route
     * @param string $route
     * @param function | array
     *
     * @return void
     * */
    public function post($route, $callback)
    {
        $this->addRoute('post', $route, $callback);
    }

    /*
     * add a put method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public function put($route, $callback)
    {
        $this->addRoute('put', $route, $callback);
    }

    /*
     * add a delete method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public function delete($route, $callback)
    {
        $this->addRoute('delete', $route, $callback);
    }

    /*
     * add a option method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public function options($route, $callback)
    {
        $this->addRoute('options', $route, $callback);
    }

    // @fixme
    public function any($route = '/', $callback)
    {
        foreach ($this->methods as $method) {
            $this->$method($route, $callback);
        }
    }


    /*
     * add 404 not found handle
     * @param function $callback access params same as route
     * @return void
     * */
    public function notFound($callback)
    {
        $this->notFounds[] = $callback;
    }

    /*
     * create a new instance
     * @param array $env
     * @return object
     * */
    public static function createApplication($env)
    {
        return new Courser($env);
    }

    /*
     * run app handle request
     * @param array $env
     * @return void
     * */
    public function run($uri)
    {
        $uri = $uri ?: '/';
        return function ($req, $res) use ($uri) {
            $router = $this->createContext($req, $res);
            $router = $this->mapRoute($router->request->method, $uri, $router);
            if (empty($router->callable)) {
                $router->add($this->notFounds);
            }
            $router->handle();
        };
    }

    /*
     * @desc 自动加载类，依赖于配置文件
     * @param $className 加载的类名，文件名需和类名一致
     * @return include file;
     * */
    public function loader($class)
    {
        $instance = $this->container[$class];
        if (is_object($instance)) {
            return $instance;
        }

        return null;
    }

    public function import()
    {
        $loader = Config::get('courser.loader');
        foreach ($loader as $alias => $namespace) {
            $this->container[$alias] = function ($c) use ($namespace) {
                return new $namespace();
            };
        }

    }
}