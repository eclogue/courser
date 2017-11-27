<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/6/3
 * @time: 下午2:46
 */

namespace Courser\Tests;

use Courser\Router;
use PHPUnit\Framework\TestCase;
use Hayrick\Http\Request;
use Hayrick\Http\Response;
use Courser\Tests\Stub\Request as StubRequest;
use Courser\Tests\Stub\Response as StubResponse;
use Bulrush\Scheduler;


class RouterTest extends TestCase
{
    public $request;

    public $response;


    public function setUp()
    {
        $this->request = new StubRequest();
        $this->response = new StubResponse();

    }

    public function testAdd()
    {
        $router = new Router($this->request, $this->response);
        $callable = [function () {
        }];
        $router->add($callable);
        $this->assertContains($callable[0], $router->callable);
    }

    public function testMiddleware()
    {
        $router = new Router($this->request, $this->response);
        $callable = [function () {
        }];
        $router->used($callable);
        $this->assertContains(array_pop($callable), $router->middleware);
    }

    public function testSetParam()
    {
        $mock = $this->getMockBuilder(Request::class)
            ->setMethods(['setParam'])
            ->getMock();
        $mock->expects($this->once())
            ->method('setParam')
            ->with($this->equalTo('key'), $this->equalTo('value'));
        $router = new Router($this->request, $this->response);
        $replace = function () use ($mock) {
            $this->request = $mock;
        };
        $replace = $replace->bindTo($router, Router::class);
        $replace();
        $router->setParam('key', 'value');
    }

    public function testMethod()
    {
        $router = new Router($this->request, $this->response);
        $method = 'get';
        $router->method($method);
        $this->assertEquals($router->request->getMethod(), $method);
    }

    public function testTransducer()
    {
        $router = new Router($this->request, $this->response);
        $md = function (Request $req, \Closure $next) {
            $response = $next($req);
            $this->assertInstanceOf(Response::class, $response);
        };
        $router->add($md);
        $gen = $router->transducer();
        $this->assertInstanceOf(\Generator::class, $gen);
        $scheduler = new Scheduler();
        $scheduler->add($gen);
        $scheduler->run();
        $this->assertEquals($router->response->isFinish(), true);
        $md = function ($req, $res) {
            $res->end();
        };
        $mds = [$md, $md];
        list($request, $response) = $this->serverProvider();
        $router = new Router($request, $response);
        $scheduler->add($router->compose($mds));
        $scheduler->run();
        $this->assertEquals($router->response->isFinish(), true);
    }

    public function serverProvider()
    {
        $request = new Request();
        $request->createRequest(new StubRequest());
        $response = new Response();
        return [$request, $response];
    }


}