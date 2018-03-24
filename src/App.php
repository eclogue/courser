<?php
/**
 * @license  MIT
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */
namespace Courser;

use Psr\Container\ContainerInterface;
use Throwable;
use DI\Container;
use Psr\Http\Server\MiddlewareInterface;

class App
{
    public $notFounds = [];

    /*
     * @var array
     * */
    public $setting = [];

    /*
     * global middle ware
     * @var array
     * */
    public $middleware;

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
    public $reporter;

    /**
     * @var array|Container
     */
    public $container = [];

    /**
     * @var array
     */
    public $loader = [];

    public $layer = [];


    /**
     * App constructor.
     *
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->middleware = new Middleware();
        $this->container = $container ?? new Container();
        spl_autoload_register([$this, 'load'], true, true);
    }

    public function setContain(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }


    public function config(array $config)
    {
        foreach ($config as $key => $value) {
            $this->container->set($key, $value);
        }
    }

    /**
     * @param null $req
     * @param null $res
     * @return Context
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function createContext($req = null, $res = null):Context
    {
        $context = new Context($req, $res, $this->container);

        return $context;
    }

    /*
     * add a middleware
     * @param function | object $md callable function
     * @return void
     * */
    public function add(MiddlewareInterface $md)
    {
        $this->middleware->add($md);
    }

    /*
     * add a group route,the callback param is bind to router instance
     * it should use $this->$method to add route
     * @param string $group
     * @param function | array $callable
     *
     * @return mixed
     * */
    public function group(string $group, callable $callback)
    {
        $group = rtrim($group, '/');
        if (!$group) {
            return null;
        }

        $this->group = rtrim($this->group, '/') . $group ;
        $this->middleware->group($this->group);
        $callback($this);
        $this->resetGroup();
    }


    public function resetGroup()
    {
        $this->group = '/';
        $this->middleware->group($this->group);
    }

    /**
     * add a route to stack
     *
     * @param string $method
     * @param string $route
     * @param array $callback
     * @return bool
     */
    public function addRoute(string $method, string $route, ...$callback)
    {
        $method = strtolower($method);
        $route = ltrim($route, '/');
        $group = rtrim($this->group, '/') . '/';
        $route = $group . $route;
        $scope = $this->middleware->count();
        $this->layer[$method][] = new Route(
            $method,
            $route,
            $callback,
            $scope,
            $this->group
        );

        return true;
    }

    /**
     * @param string $uri
     *
     * @param int $deep
     * @return array
     */
    public function mapMiddleware(string $uri, int $deep = 1): array
    {
        $match = $this->middleware->match($uri, $deep);

        return $match;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param Context $router
     * @return mixed
     */
    public function mapRoute(string $method, string $path, Context $context): Context
    {
        $path = parse_url($path, PHP_URL_PATH);
        $method = strtolower($method);
        $routes = $this->layer[$method] ?? [];
        foreach ($routes as $route) {
            if (!$route instanceof Route) {
                continue;
            }

            $found = $route->find($method, $path);
            if (!$found) {
                continue;
            }

            $context->add($route, $found);
            $scope = $route->getScope();
            $middleware = $this->mapMiddleware($path, $scope);
            if (!empty($middleware)) {
                $context->use($middleware);
            }
        }

        if (!$context->isMount()) {
            $md = $this->mapMiddleware($path, $this->middleware->count());
            $context->use($md);
        }

        return $context;
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
    public function notFound(callable $callback)
    {
        $this->notFounds[] = $callback;
    }

    /**
     * set error handle
     *
     * @param callable $callback
     * @return void
     */
    public function setReporter(callable $callback)
    {
        $this->reporter = $callback;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param object $request
     * @param object $response
     * @param object $err
     * @throws Throwable
     */
    public function handleError(Throwable $err, $request = null, $response = null)
    {
        if (!is_callable($this->reporter) && !is_array($this->reporter)) {
            throw $err;
        }

        $context = $this->createContext($request, $response);
        $handler = $context->error($err);

        return $handler($this->reporter);
    }


    /**
     * @param string $uri
     * @param null $req
     * @param null $res
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function run(string $uri, $req = null, $res = null)
    {
        $uri = $uri ?: '/';
        $context = $this->createContext($req, $res);
        try {
            $context = $this->mapRoute($context->method, $uri, $context);
            if (empty($context->callable)) {
                $context->use($this->notFounds);
            }

            $context->dispatch();
        } catch (Throwable $err) {
            $handler = $context->error($err);
            $handler($this->reporter);
        }
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
            $this->container->set($alias, function ($c) use ($alias, $namespace) {
                if (is_callable([$namespace, 'make'])) {
                    call_user_func_array($namespace . '::make', [$alias, $c]);
                }

                return new $namespace();
            });
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
        if (!$this->container->has($class)) {
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
