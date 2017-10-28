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
        $scheduler = new Scheduler();
        $scheduler->add($this->compose($this->middleware));
        $scheduler->run();
        if (!$this->response->isFinish()) {
            $scheduler->add($this->compose($this->callable));
            $scheduler->run();
        }

        $this->respond();

        return true;
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
                    $ctrl = new $class($this->request, $this->response);
                    yield $ctrl->$action();
                }
            } else {
                if (!is_callable($md)) {
                    continue;
                }
                yield $md($this->request, $this->response);
            }

            if ($this->response->isFinish()) {
                break;
            }
        }
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
        $output = $this->response->getContext();
        $response = $this->context['response'];
        $headers = $output->getHeaders();
        foreach ($headers as $key => $header) {
            $response->header($key, $header);
        }

        return $response->end($output->getBody());
    }
}
