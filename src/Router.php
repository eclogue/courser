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

class Router
{
    public $request;

    public $response;

    public $middleware = [];

    public $callable = [];

    public $paramNames = [];

    public $env = 'dev';

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
        $this->request->method = $method;
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
        $compose = new Compose();
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
                $compose->push($gen);
            }
            if ($this->response->finish) {
                break;
            }
        }
        $compose->run();
    }


    public function handleError($err)
    {
        throw $err;
    }


}