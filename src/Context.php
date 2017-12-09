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
        $this->container = new Container();
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = clone $container;
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

//        var_dump($this->middleware, $this->callable);
        $this->middleware = array_merge($this->middleware, $this->callable);
        $this->middleware = array_reverse($this->middleware);

        $response = $this->transducer($this->request);
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

        $terminator = $this->terminator;
        if (!is_callable($terminator)) {
            $terminator = $this->respond();
        }

        return $terminator($this->response);
    }

    /**
     * Iterative process the request
     *
     * @param mixed $request
     * @return int
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

            return $response;
        }

        return $response;
    }

    public function error($err)
    {
        return function ($callable) use ($err) {
            if (!$this->error) {
                return null;
            }

            if (is_array($callable)) {
                $response = call_user_func_array($callable, [$this->request, $err]);
            } else {
                $response = $callable($this->request, $err);
            }

            if ($response instanceof Generator) {
                $response = Poroutine::resolve($response);
            }

            $terminator = $this->terminator;
            if (!is_callable($terminator)) {
                $this->terminator = $this->respond();
            }

            return $terminator($response);
        };
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
