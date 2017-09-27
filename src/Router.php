<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */

namespace Courser;

use Courser\Helper\Util;
use Courser\Http\Request;
use Courser\Http\Response;
use Courser\Co\Compose;
use Bulrush\Scheduler;
use Bulrush\Poroutine;

class Router
{
    public $request;

    public $response;

    public $middleware = [];

    public $callable = [];

    public $paramNames = [];

    public $env = 'dev';

    protected static $scheduler;

    public static $allowMethods = [
        'get',
        'post',
        'put',
        'delete',
        'options',
        'patch',
    ];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /*
     * $route
     * */
    public function add($callback)
    {
        if (is_array($callback)) {
            $this->callable = array_merge($this->callable, $callback);
        } else if (!in_array($callback, $this->callable)) {
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
        $this->compose($this->middleware);
        if ($this->response->finish) return true;
        $this->compose($this->callable);
        return true;
    }

    /**
     * handle request stack
     *
     * @param $middleware
     */
    public function compose($middleware)
    {
        $scheduler = static::getScheduler();
        foreach ($middleware as $md) {
            $gen = null;
            if (is_array($md)) {
                if (Util::isIndexArray($md)) {
                    $this->compose($md);
                    continue;
                }
                foreach ($md as $class => $action) {
                    $ctrl = new $class($this->request, $this->response);
                    $gen = $ctrl->$action();
                }
            } else {
                if (!is_callable($md)) continue;
                $gen = $md($this->request, $this->response);
            }
            if ($gen instanceof \Generator) {
                $scheduler->add($gen);
            }
            if ($this->response->finish) {
                break;
            }
        }
        $scheduler->run();
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

}