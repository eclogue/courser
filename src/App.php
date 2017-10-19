<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */
namespace Courser;

use Pimple\Container;
use RuntimeException;

class App
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
    /*
     * @var array
     * routes stack
     * */
    public $stack = [];
    /*
     * @var $methods
     * allow method
     * */
    private $methods = [
        'get',
        'post',
        'delete',
        'put',
        'options'
    ];
    /*
     * @var $errors array
     * custom exception handle
     * */
    public static $errors = [];

    /**
     * @var array|Container
     */
    public $container = [];

    /**
     * @var array
     */
    public $loader = [];

    public function __construct($env = 'dev')
    {
        $this->env = $env;
        $this->container = new Container();
        spl_autoload_register([$this, 'load'], true, true);
    }

    /*
     * create request context set req and response
     * @param object $req Swoole\Http\Request
     * @param object $res Swoole\Http\Response
     * @return object self
     * */
    public function createContext($req, $res)
    {
        $router = new Router($req, $res);
//        $router->createContext($req, $res);

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
    public function group(string $group, $callback)
    {
        $group = rtrim($group, '/');
        $this->group = $group;
        if ($callback instanceof \Closure) {
            $callback = $callback->bindTo($this);
        }
        $callback();
        $this->group = '/';
    }

    /**
     * add a route to stack
     *
     * @param string $method
     * @param string $route
     * @param callable $callback
     */
    public function addRoute(string $method, string $route, ...$callback)
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
        $this->routes[$method][] = [
            'route' => $route,
            'params' => $params,
            'pattern' => $pattern,
            'callable' => $callback,
            'scope' => $scope,
        ];
    }

    /**
     * @param string $uri
     * @param int $deep
     * @return array
     */
    public function mapMiddleware(string $uri, int $deep = 1)
    {
        $deep = $deep > 0 ? $deep : 1;
        $md = [];
        if (empty($this->middleware)) {
            return $md;
        }

        $tmp = $this->middleware;
        $apply = array_slice($tmp, 0, $deep);
        foreach ($apply as $index => $middleware) {
            $group = '#^' . $middleware['group'] . '(.*?)#';
            preg_match($group, $uri, $match);
            if (empty($match)) {
                continue;
            }
            $md[] = $middleware['middleware'];
        }

        return $md;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $router
     * @return mixed
     */
    public function mapRoute(string $method, string $uri, Router $router): Router
    {
        $method = strtolower($method);
        $routes = $this->routes[$method] ?? [];
        foreach ($routes as $route) {
            preg_match($route['pattern'], $uri, $match);
            if (empty($match)) {
                continue;
            }

            if ($route['scope']) {
                $middleware = $this->mapMiddleware($uri, $route['scope']);
                if (!empty($middleware)) {
                    $router->used($middleware);
                }
            }

            $router->method($method);
            $router->add($route['callable']);
            $router->paramNames = array_merge($router->paramNames, $route['params']);
            foreach ($match as $param => $value) {
                if (in_array($param, $router->paramNames)) {
                    if (is_string($param)) {
                        $router->setParam($param, $value);
                    }
                }
            }
        }

        if (empty($router->callable)) {
            foreach ($this->middleware as $key => $md) {
                if ($md['group'] === '/') {
                    $router->used($md['middleware']);
                }
            }
        }

        return $router;
    }

    /**
     * @param $route string
     * @return array
     */
    private function getPattern(string $route): array
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
    public function get(string $route, $callback)
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
    public function post(string $route, $callback)
    {
        $this->addRoute('post', $route, $callback);
    }

    /*
     * add a put method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public function put(string $route, $callback)
    {
        $this->addRoute('put', $route, $callback);
    }

    /*
     * add a delete method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public function delete(string $route, $callback)
    {
        $this->addRoute('delete', $route, $callback);
    }

    /*
     * add a option method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public function options(string $route, $callback)
    {
        $this->addRoute('options', $route, $callback);
    }

    // @fixme
    public function any(string $route, $callback)
    {
        foreach ($this->methods as $method) {
            $this->$method($route, $callback);
        }
    }


    /**
     * add 404 not found handle
     *
     * @param  callable $callback params same as route
     * @return void
     * */
    public function notFound($callback)
    {
        $this->notFounds[] = $callback;
    }

    /**
     * set error handle
     *
     * @param $env
     * @return void
     */
    public function error($callback)
    {
        static::$errors[] = $callback;
    }

    /**
     * @param $req
     * @param $res
     * @param $err
     */
    public function handleError($req, $res, $err)
    {
        $request = $this->container['courser.request'];
        $request = $request->createRequest($req);
        $response = $this->container['courser.response'];
        $response = $response->createResponse($res);
        if (empty(static::$errors)) {
            throw $err;
        }
        foreach (static::$errors as $callback) {
            $callback($request, $response, $err);
        }
    }


    /*
     * run app handle request
     * @param array $env
     * @return void
     * */
    public function run(string $uri)
    {
        $uri = $uri ?: '/';
        return function ($req, $res) use ($uri) {
            $router = $this->createContext($req, $res);
            $router = $this->mapRoute($router->request->getMethod(), $uri, $router);
            if (empty($router->callable)) {
                $router->add($this->notFounds);
            }
            $router->handle();
        };
    }

    /**
     * import custom files keep to psr-4
     *
     * @param $loader
     */
    public function import(array $loader)
    {
        $this->loader = $loader;
        foreach ($loader as $alias => $namespace) {
            $alias = $this->alias($alias);
            $this->container[$alias] = function ($c) use ($alias, $namespace) {
                if (is_callable([$namespace, 'make'])) {
                    call_user_func_array($namespace . '::make', array($alias, $c));
                }

                return new $namespace();
            };
        }
    }

    /*
     * @desc 自动加载类，依赖于配置文件
     * @param $className 加载的类名，文件名需和类名一致
     * @return include file;
     * */
    public function load(string $class)
    {
        $alias = $this->loader;
        if (isset($alias[$class])) {
            class_alias($alias[$class], $class);
        }
        $class = $this->alias($class);
        if (!$this->container->offsetExists($class)) {
            return null;
        }
        $instance = $this->container[$class];
        if (is_object($instance)) {
            return $instance;
        }

        return null;
    }

    /**
     * @param $name
     * @return string
     */
    private function alias(string $name)
    {
        return 'courser.loader.' . $name;
    }
}
