<?php

/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/14
 * Time: 下午10:41
 */
namespace Courser;

use Courser\Set\Config;
use Courser\Router\Router;
use Courser\Http\Request;
use Courser\Http\Response;


class Courser
{
    public $env = [];

    public static $middleware = [];

    public static $routes = [];

    public static $group = [];


    public function __construct($env = [])
    {
        $this->env = $env;
    }


    public function createContext($req, $res)
    {
        $router = new Router(new Request, new Response);
        $router->response->setResponse($res);
        $router->request->setRequest($req);
        return $router;

    }

    public static function used($callable)
    {
        self:: $middleware[] = $callable;
    }

    public static function get($route, $callback)
    {
        self::$routes['get'][$route] = $callback;
    }



    public static function post($route, $callback)
    {
        self::$routes['post'][$route] = $callback;
    }

    public function put($route, $callback)
    {
        self::$routes['put'][$route] = $callback;
    }

    public static function delete($route, $callback)
    {
        self::$routes['delete'][$route] = $callback;
    }


    public static function option($route, $callback)
    {
        self::$routes['option'][$route] = $callback;
    }

//
    public static function all($route, $callback)
    {
        foreach (Router::$allowMethods as $method) {
            self::$method($route, $callback);
        }
    }

    public static function group($group, $callback)
    {
        self::$group[$group] = $callback;
    }


    public function listen($port)
    {
        Config::set('port', $port);

    }

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

    public static function run($env = [])
    {
        return function($req, $res) use ($env) {
            $app = Courser::createApplication($env);
            $router = $app->createContext($req, $res);
            $router->addMiddleware(self::$middleware);
            foreach (self::$routes as $method => $routes) {
                foreach ($routes as $path => $route)
                    $router->addRoute($method, $path, $route);
            }
            $uri = isset($req->server['request_uri']) ? $req->server['request_uri'] : '/';
            $router->dispatch($uri);
        };
    }

}