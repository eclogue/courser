<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/2/14
 * @time: 下午7:53
 */

namespace Courser;


use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class Test implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        var_dump($request);
        var_dump($handler);

        $response = $handler->handle($request);
        echo 'middleware response::<hr>';
        var_dump($response);

        return $response;
    }
}