<?php

/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/14
 * Time: 下午10:41
 */
namespace Barge;

use Barge\Set\Config;
use Barge\Router\Router;
use Pimple\Container;
use Barge\Http\Request;
use Barge\Http\Response;


class Barge
{
    public $env = '';

    public static $middleware = [];

    public $request = null;

    public $response = '';

    public $router = '';


    public function __construct()
    {
        $container = new Container();

        $container['request'] = $container->factory((function($c) {
//            var_dump($c);
            return new Request();
        }));
        $container['response'] = $container->factory((function($c) {
            return new Response();
        }));
        $container['router'] = function ($c) {
            return new Router($c['request'], $c['response']);
        };
        $this->container = $container;
    }

    public function init($config = []) {
        Config::set($config);
        $this->container['router']->setContainer($this->container);
    }


    /*
     * 运行框架
     * @param string $appPath 应用的路径
     * @param string $config 配置文件的名称
     * */
    public function run()
    {
        $router = $this->container['router'];
        $request = $router->request;
        $uri = isset($request->header['request_uri']) ? $request->header['request_uri'] : '/';
        $router->dispatch($uri);
    }

    public function getRouter() {
        return $this->container['router'];
    }

    public function setRequest($request)
    {
        $router = $this->container['router'];
        $router->request->setRequest($request);
    }

    public function setResponse($response)
    {
        $router = $this->container['router'];
        $router->response->setResponse($response);
    }

    public function register($key, $instance)
    {
        if (!$this->container->offsetExists($key))
            $this->container->offsetSet($key, $instance);

        return true;
    }

    /*
     * 销毁挂载实力
     * @param $key string
     * */
    public function unRegister($key)
    {
        if ($this->container->offsetExists([$key]))
            $this->container->offsetUnset($key);

        return true;
    }


    public function use($callable)
    {
        $this->container['router']->addMiddleware($callable);
    }

    public function get($route, $callback)
    {
        $this->container['router']->addRoute('GET', $route, $callback);
    }


    public function group($group, $callback) {
        $this->container['router']->group($group, $callback, $this);
    }

    public function post($route, $callback)
    {
        $this->container['router']->addRoute('POST', $route, $callback);
    }

    public function put($route, $callback)
    {
        $this->container['router']->addRoute('PUT', $route, $callback);
    }


    public function delete($route, $callback)
    {
        $this->container['router']->addRoute('PUT', $route, $callback);
    }

    public function patch()
    {

    }


    public function option($route, $callback)
    {
        $this->container['router']->addRoute('OPTION', $route, $callback);
    }

    public function all($route, $callback)
    {
        $this->container['router']->addRoute('OPTION', $route, $callback);
    }


    public function listen($port)
    {
        Config::set('port', $port);

    }

}