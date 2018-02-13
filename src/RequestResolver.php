<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/2/5
 * @time: 下午4:12
 */

namespace Courser;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Hayrick\Http\Response;
use Bulrush\Poroutine;
use Generator;

class RequestResolver implements RequestHandlerInterface
{
    protected $pipeline;

    protected $callable;

    public function __construct(array $callable)
    {
        $this->callable = new \SplQueue();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->next($request);

        return $response ?? new Response();
    }


    public function next(ServerRequestInterface $request): ResponseInterface
    {
        $response = null;
        if (!$this->callable->isEmpty()) {
            $callable = $this->callable->dequeue();
            if (is_callable($callable)) {
                $response = $callable($request, $this);
            } elseif ($callable instanceof MiddlewareInterface) {
//                list($class, $action) = $callable;
//                $instance = is_object($class) ? $class : new $class();
//                $response = $instance->$action($request, $this);
                $callable->process($request, $this);
            }

            if ($response instanceof Generator) {
                $response = Poroutine::resolve($response);
            }
        }

        return $response;
    }

    public function push(callable $callable)
    {
        $this->callable->enqueue($callable);
    }


    public function count()
    {
        return $this->callable->count();
    }

}