<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/2/2
 * @time: 下午12:39
 */

namespace Courser;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Generator;
use Bulrush\Poroutine;
use Hayrick\Http\Response;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware
{

    protected $middleware = [];

    protected $group = '/';

    public function __construct(callable $middleware, string $group = '/')
    {
        $this->middleware = $middleware;
    }

    public function add(RequestHandlerInterface $handler)
    {
        $this->middleware[] = $handler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = null;
        if (count($this->middleware)) {
            $md = array_pop($this->middleware);
            $next = function (ServerRequestInterface $request) {
                return $this->handle($request);
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

        if (!$response instanceof ResponseInterface) {
            $res = new Response();
            if ($response) {
                $res->write($response);
            }

            return $res;
        } else {
            return $response;
        }
    }
}