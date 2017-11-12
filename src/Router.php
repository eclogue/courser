<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */

namespace Courser;

use Hayrick\Http\Request;
use Hayrick\Http\Response;
use Bulrush\Scheduler;
use Generator;

class Router
{
    public $request;

    public $response;

    public $middleware = [];

    public $callable = [];

    public $paramNames = [];

    protected $context = [];

    protected static $scheduler;

    public static $allowMethods = [
        'get',
        'post',
        'put',
        'delete',
        'options',
        'patch',
    ];


    public function __construct($req, $res)
    {
        $this->request = new Request();
        $this->request = $this->request->createRequest($req);
        $this->context['request'] = $req;
        $this->context['response'] = $res;
        self::$scheduler = new Scheduler();

    }

    /*
     * $route
     * */
    public function add($callback)
    {
        if (is_array($callback)) {
            $this->callable = array_merge($this->callable, $callback);
        } elseif (!in_array($callback, $this->callable)) {
            $this->callable[] = $callback;
        }
    }

    public function used($middleware)
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function setParam($name, $value)
    {
        $this->request->setParam($name, $value);
    }

    /**
     * @param $method
     */
    public function method($method)
    {
        $this->request = $this->request->withMethod($method);
    }


    public function handle()
    {
        $this->middleware = array_merge($this->middleware, $this->callable);
        $this->compose($this->request);

        self::$scheduler->run();

        return $this->respond();
    }


    public function compose($request)
    {
//        echo '++++++++++ count:' . count($this->middleware) . PHP_EOL;
        if (empty($this->middleware)) {
            return $this->response;
        }

        $md = array_shift($this->middleware);
        $pass = null;
        $next = function ($request) {
//            echo '--------> next' . PHP_EOL;
            return $this->compose($request);
        };

        if (is_callable($md)) {
            $pass = $md($request, $next);
        }

        if (is_array($md)) {
            list($class, $action) = $md;
            $instance = is_object($class) ?? new $class();
            $pass = $instance->$action($request, $next);
        }

//        var_dump($pass);
        if ($pass instanceof Generator && $pass->valid()) {
            self::$scheduler->add($pass);
            return $next($request);
        } elseif ($pass instanceof Response) {
            $this->response = $pass;
        } else {
            $response = new Response();
            $response->write($pass);
            $this->response = $response;
        }

        return $this->response;
    }

    public function __invoke()
    {

    }


    public function handleError($err)
    {
        throw $err;
    }


    public static function getScheduler(): Scheduler
    {
        if (!static::$scheduler) {
            static::$scheduler = new Scheduler();
        }

        return static::$scheduler;
    }

    public function respond()
    {
        $output = $this->response ?? new Response();
        $response = $this->context['response'];
        $headers = $output->getHeaders();
        foreach ($headers as $key => $header) {
            $response->header($key, $header);
        }


        return $response->end($output->getContext());
    }
}
