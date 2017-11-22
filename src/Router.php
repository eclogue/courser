<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */

namespace Courser;

use Bulrush\Poroutine;
use Hayrick\Http\Request;
use Hayrick\Http\Response;
use Psr\Http\Message\ResponseInterface;
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

    protected $stack = [];

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
        $request = new Request();
        $this->request = $request->createRequest($req);
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
        $this->middleware = array_reverse($this->middleware);
        $response = $this->compose($this->request);
        if ($response instanceof ResponseInterface) {
            $this->response = $response;
        } else if ($response instanceof Response) {
            $this->response = $response;
        } else if (
            $response instanceof \ArrayIterator ||
            $response instanceof \ArrayObject ||
            $response instanceof \JsonSerializable ||
            is_array($response)
        ) {
            $reply = new Response();
            $reply = $reply->withHeader('Content-Type', 'application/json');
            $reply = $reply->write($response);
            $this->response = $reply;
        } else {
            $this->response = new Response();
            $this->response->write($response);
        }

        return $this->respond();
    }


    public function compose($request)
    {

        if (!empty($this->middleware)) {
            $md = array_pop($this->middleware);
            $next = function ($request) {
                echo '_________________' . count($this->middleware) . PHP_EOL;
                return $this->compose($request);
            };

            $response = null;
            if (is_callable($md)) {
                $response = $md($request, $next);
            } elseif (is_array($md)) {
                list($class, $action) = $md;
                $instance = is_object($class) ? $class : new $class();
                $response = $instance->$action($request, $next);
            }
//
            if ($response instanceof Generator && $response->valid()) {
                $po = new Poroutine($response, true);
                $response = $po->resolve();
            }


            return $response;
//
//            if ($response instanceof Generator && $response->valid()) {
//                self::$scheduler->add($response, true);
//                continue;
//            }

        }

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


        return $response->end($output->getContent());
    }
}
