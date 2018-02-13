<?php
/**
 * @license   MIT
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */

namespace Courser;

use Bulrush\Poroutine;
use Hayrick\Http\Request;
use Hayrick\Http\Response;
use Pimple\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Generator;

class Context
{
    public $request;

    public $response;

    public $middleware = [];

    public $callable = [];

    public $paramNames = [];

    protected $context = [];

    protected $terminator;

    protected $container;

    protected $error;

    public $method = 'get';

    public $collection;

    public static $allowMethods = [
        'get',
        'post',
        'put',
        'delete',
        'options',
        'patch',
    ];


    public function __construct($req, $res, Container $container = null)
    {

        $this->context['request'] = $req;
        $this->context['response'] = $res;
        $this->container = $container;
        $request = $this->createRequest($req);
        $this->request = $request;
        $this->method = $request->getMethod();
    }

    /**
     * @param Container $container
     */
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
        $this->method = $method;
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
        $response = $this->transducer($this->request);
        if ($response instanceof ResponseInterface) {
            $this->response = $response;
        } elseif ($response instanceof Response) {
            $this->response = $response;
        } elseif ($response instanceof Generator) {
            $this->response = $response->getReturn();
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

        return $this->respond($this->response);
    }

    /**
     * Iterative process the request
     *
     * @param mixed $request
     * @return mixed|Response
     */
    public function transducer(RequestInterface $request)
    {
        $response = null;
        if (count($this->middleware)) {
            $md = array_pop($this->middleware);
            $next = function (RequestInterface $request) {
                return $this->transducer($request);
            };

            if (is_callable($md)) {
                $response = $md($request, $next);
            } elseif (is_array($md)) {
                list($class, $action) = $md;
                $instance = is_object($class) ? $class : new $class();
                $response = $instance->$action($request, $next);
            }

            if ($response instanceof Generator) {
                $response = Poroutine::resolve($response);
            }
        }

        return $response ?? new Response();
    }

    public function error($err)
    {
        return function ($callable) use ($err) {
            if (is_array($callable) || is_callable($callable)) {
                $response = call_user_func_array($callable, [$this->request, $err]);
            }  else {
                $response = $callable($this->request, $err);
            }

            if ($response instanceof Generator) {
                $response = Poroutine::resolve($response);
            }

            return $this->respond($response);
        };
    }


    /**
     * default terminator
     *
     * @return mixed
     */
    public function respond($response)
    {
        $response = $response ?? new Response();
        $terminator = $this->container['response.resolver'];

        $respond = $terminator($this->context['response']);

        var_dump($response);
        return $respond($response);
    }


    /*
     * set request context @todo @fixme
     * @param object|null $req
     * @return void
     * */
    public function createRequest($req = null)
    {
        $builder = $this->container['request.resolver'];
        $incoming = null;
        if (is_object($builder)) {
            $incoming = $builder;
        } else if (is_callable($builder, true, $callable)) {
            if (is_array($builder)) {
                $incoming = call_user_func_array($callable, [$req]);
            } else {
                $incoming = $builder($req);
            }
        } else {
            throw new \RuntimeException('Request builder invalid');
        }

        $request = new Request($incoming);

        return $request;
    }

    public function isMount()
    {
        return count($this->callable) + count($this->middleware);
    }
}
