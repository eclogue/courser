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
use Courser\Router\Router;
use Courser\Http\Request;
use Courser\Http\Response;


class Courser
{
    public static $notFounds = [];
    /*
     * instance env
     * @var array
     * */
    public $env = [];

    /*
     * global middle ware
     * @var array
     * */
    public static $middleware = [];

    /*
     * @var array
     * */
    public static $routes = [];

    /*
     * @var array
     * */
    public static $group = [];


    public function __construct($env = [])
    {
        $this->env = $env;
    }

    /*
     * create request context set req and response
     * @param object $req Swoole\Http\Request
     * @param object $res Swoole\Http\Response
     * @return object self
     * */
    public function createContext($req, $res)
    {
        $router = new Router(new Request, new Response);
        $router->response->setResponse($res);
        $router->request->setRequest($req);
        return $router;

    }

    /*
     * add a middleware
     * @param function | object $callable callable function
     * @return void
     * */
    public static function used($callable)
    {
        self:: $middleware[] = $callable;
    }

    /*
     * add get method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public static function get($route, $callback)
    {
        self::$routes['get'][$route] = $callback;
    }


    /*
     * add a post method route
     * @param string $route
     * @param function | array
     *
     * @return void
     * */
    public static function post($route, $callback)
    {
        self::$routes['post'][$route] = $callback;
    }

    /*
     * add a put method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public function put($route, $callback)
    {
        self::$routes['put'][$route] = $callback;
    }

    /*
     * add a delete method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public static function delete($route, $callback)
    {
        self::$routes['delete'][$route] = $callback;
    }

    /*
     * add a option method route
     * @param string $route
     * @param function | array
     * @return void
     * */
    public static function option($route, $callback)
    {
        self::$routes['option'][$route] = $callback;
    }

    // @fixme
    public static function any($route, $callback)
    {
        foreach (Router::$allowMethods as $method) {
            self::$method($route, $callback);
        }
    }

    /*
     * add a group route,the callback param is bind to router instance
     * it should use $this->$method to add route
     * @param string $group
     * @param function | array $callable
     *
     * @return void
     * */
    public static function group($group, $callback)
    {
        self::$group[$group] = $callback;

    }

    /*
     * add 404 not found handle
     * @param function $callback access params same as route
     * @return void
     * */
    public static function notFound($callback)
    {
        self::$notFounds[] = $callback;
    }

    // @todo
    public function listen($port)
    {
        Config::set('port', $port);
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

    public static function __callStatic($name, $args)
    {
        if (is_callable(['Courser', $name])) {
            self::$routes[$name] = $args;
        }
    }

    /*
     * run app handle request
     * @param array $env
     * @return void
     * */
    public static function run($env = [])
    {
        return function($req, $res) use ($env) {
            $app = Courser::createApplication($env);
            $router = $app->createContext($req, $res);
            foreach (static::$group as $namespace => $callable) {
                $router->group($namespace, $callable);
            }
            $router->addMiddleware(self::$middleware);
            foreach (self::$routes as $method => $routes) {
                foreach ($routes as $path => $route)
                    if(!strcasecmp($method, $req->server['request_method']))
                        $router->addRoute($method, $path, $route);
            }
            $uri = isset($req->server['request_uri']) ? $req->server['request_uri'] : '/';
            $router->dispatch($uri);
        };
    }
}