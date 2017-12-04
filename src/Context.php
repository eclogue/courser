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
use Pimple\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Bulrush\Scheduler;
use Generator;

class Context
{
    public $request;

    public $response;

    public $middleware = [];

    public $callable = [];

    public $paramNames = [];

    protected $context = [];

    protected $container = [];

    protected $terminator;

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
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * set request process terminator
     *
     * @param callable $terminator
     */
    public function setTerminator(callable $terminator)
    {
        $this->terminator = $terminator;
    }

    /**
     * add request handle
     *
     * @param callable|array $callback
     */
    public function add($callback)
    {
        if (is_array($callback)) {
            $this->callable = array_merge($this->callable, $callback);
        } elseif (!in_array($callback, $this->callable)) {
            $this->callable[] = $callback;
        }
    }

    /**
     * mount middleware
     *
     * @param callable|array $middleware
     */
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

    /**
     * handle the request
     *
     * @return mixed
     */
    public function handle()
    {
        $this->middleware = array_merge($this->middleware, $this->callable);
        $this->middleware = array_reverse($this->middleware);
        $scheduler = new Scheduler();
        $response = $this->transducer($this->request);
        if ($response instanceof Generator && $response->valid()) {
            $scheduler->add($response, true);
            $scheduler->run();
            $response = $response->getReturn();
        }


        if ($response instanceof ResponseInterface) {
            $this->response = $response;
        } elseif ($response instanceof Response) {
            $this->response = $response;
        } elseif ($response instanceof \ArrayIterator ||
            $response instanceof \ArrayObject ||
            $response instanceof \JsonSerializable ||
            is_array($response)
        ) {
            $reply = new Response();
            $reply = $reply->withHeader('Content-Type', 'application/json');
            $reply = $reply->write($response);
            $this->response = $reply;
        } elseif (is_object($response) && method_exists($response, 'getContent')) {
            $this->response = $response;
        } else {
            $this->response = new Response();
            $this->response->write($response);
        }

        $terminator = $this->terminator ?? $this->respond();

        return $terminator($this->response);
    }

    /**
     * Iterative process the request
     *
     * @param RequestInterface $request
     * @return int
     */
    public function transducer(RequestInterface $request)
    {
        $response = 0;
        if (count($this->middleware)) {
            $md = array_pop($this->middleware);
            $next = function (RequestInterface $request) {
                $response = $this->transducer($request);

                return $response;
            };

            if (is_callable($md)) {
                $response = yield $md($request, $next);
            } elseif (is_array($md)) {
                list($class, $action) = $md;
                $instance = is_object($class) ? $class : new $class();
                $response = yield $instance->$action($request, $next);
            }

            return $response;
        }

        return $response;
    }




    public function handleError($err)
    {
        throw $err;
    }


    /**
     * default terminator
     *
     * @return \Closure
     */
    public function respond(): \Closure
    {
        return function ($response) {
            $output = $response ?? new Response();
            $response = $this->context['response'];
            $headers = $output->getHeaders();
            foreach ($headers as $key => $header) {
                $response->header($key, $header);
            }

            return $response->end($output->getContent());
        };
    }
}
