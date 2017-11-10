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
        $this->response = new Response();
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
            $this->middleware = array_merge($this->callable, $callback);
        } elseif (!in_array($callback, $this->callable)) {
            $this->middleware[] = $callback;
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
        $response = $this->dispatch($this->request);
        if ($response instanceof $response) {
            return $this->respond();
        }

        self::$scheduler->run();
        return $this->respond();

//        $scheduler->add($this->compose($this->middleware));
//        $scheduler->run();
//        if (!$this->response->isFinish()) {
//            $scheduler->add($this->compose($this->callable));
//            $scheduler->run();
//        }
//
//
//        return true;
    }

    /**
     * handle request stack
     *
     * @param $middleware
     */
    public function compose($middleware)
    {
        foreach ($middleware as $md) {
            if (is_array($md)) {
                foreach ($md as $class => $action) {
                    $ctrl = new $class($this->request);
                    yield $ctrl->$action($this->request);
                }
            } else {
                if (!is_callable($md)) {
                    continue;
                }
                yield $md($this->request);
            }

            if ($this->response->isFinish()) {
                break;
            }
        }
    }

    public function dispatch($request)
    {
        echo '++++++++++ count:' . count($this->middleware) . PHP_EOL;
        if (empty($this->middleware)) {
            return null;
        }
        $md = array_shift($this->middleware);
        $pass = $md($request, function ($request) {
            echo '--------> next' .PHP_EOL;
            $this->dispatch($request);
        });

        if ($pass instanceof Generator) {
            self::$scheduler->add($pass);
        } elseif ($pass instanceof Response) {
            return $this->response = $pass;
        }

        return null;
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
