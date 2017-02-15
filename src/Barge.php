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

    public static $appPath = 'App';

    public static $core_path = __DIR__;

    private static $includePath = array();
    /*
     * @var array $_import
     * */
    public static $_import = array();

    /*
     * @var instance $_instance application object
     * */
    public static $_instance = null;


    /*
     * @var autoload classes
     * */
    public static $_classes = array();


    public function __construct()
    {
        spl_autoload_register(array($this, 'loader'));
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

    public function init($appPath, $config = []) {
        self::setAppPath($appPath);
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



    /*
     * @param $callback 注册全局变量
     * */
    public static function registerAutoload($callback)
    {
        spl_autoload_register($callback);
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

    /*
     * @desc 自动加载类，依赖于配置文件
     * @param $className 加载的类名，文件名需和类名一致
     * @return include file;
     * */
    private function loader($className)
    {
        if (strpos($className, '\\') !== false) {
            $classFile = str_replace('\\', DIRECTORY_SEPARATOR, ltrim($className, '\\')) . '.php';
            $corePath = dirname(dirname(__FILE__)) . '/';
            if (is_file($corePath . $classFile) && !isset(self::$_import[$className])) {
                include($corePath . $classFile);
            } else if (is_file(self::$appPath . $classFile) && !isset(self::$_import[$className])) {
                include(self::$appPath . $classFile);
            }
        } else {//路径
            if (self::$includePath = Config::config('autoload_path')) {//array
                $include = '';
                foreach (self::$includePath as $name => $path) {
                    $include .= $path . PATH_SEPARATOR;
                }
                set_include_path($include . get_include_path());
                include($className . '.php');
            }

        }

    }


    /*
     * 设置应用的路径
     * @param string $appPath
     * */
    public static function setAppPath($appPath)
    {
        self::$appPath = $appPath;
    }

    /*
     * 获取应用路径
     * */
    public static function getAppPath()
    {
        return self::$appPath;
    }


    /*
     * include加载，如果存在则加载
     * @param $file
     * */
    public static function import($file)
    {
        $file = self::$appPath . $file;
        if (file_exists($file) && !in_array($file, self::$_import)) {
            self::$_import[$file] = $file;
            return include $file;
        } else {
            throw new \Exception("$file inexistance when Ouno::import");
        }

    }


    public function used($callable)
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