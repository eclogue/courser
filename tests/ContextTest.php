<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/6/3
 * @time: 下午2:46
 */

namespace Courser\Tests;

use Courser\Context;
use Courser\Relay;
use Hayrick\Environment\Reply;
use PHPUnit\Framework\TestCase;
use Hayrick\Http\Request;
use Hayrick\Http\Response;
use DI\Container;


class ContextTest extends TestCase
{
    public $request;

    public $response;

    public $container;


    public function setUp()
    {
        $this->request = Relay::createFromGlobal();
        $this->response = new Response();
        $container = new Container();
        $container['request'] = function () {
            return Relay::createFromGlobal();
        };

        $container['response'] = function() {
            return function () {
                return new Reply();
            };
        };
        $this->container = $container;

    }

    public function testAdd()
    {
        $context = new Context($this->request, $this->response, $this->container);
        $callable = function () {
        };
        $context->add($callable);
        $this->assertContains($callable, $context->callable);
    }

    public function testMiddleware()
    {
        $context = new Context($this->request, $this->response, $this->container);
        $callable = function () {
        };
        $context->used($callable);
        $this->assertAttributeContains($callable, 'middleware', $context);
    }

    public function testSetParam()
    {
        $mock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['setParam'])
            ->getMock();
        $mock->expects($this->once())
            ->method('setParam')
            ->with($this->equalTo('key'), $this->equalTo('value'));
        $context = new Context($this->request, $this->response, $this->container);
        $replace = function () use ($mock) {
            $this->request = $mock;
        };
        $replace = $replace->bindTo($context, Context::class);
        $replace();
        $context->setParam('key', 'value');
    }

    public function testMethod()
    {
        $method = 'get';
        $stub = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['withMethod'])
            ->getMock();
        $stub->expects($this->once())
            ->method('withMethod')
            ->with($this->equalTo($method));
        $context = new Context($this->request, $this->response, $this->container);
        $replace = function () use ($stub) {
            $this->request = $stub;
        };
        $replace = $replace->bindTo($context, Context::class);
        $replace();
        $context->method($method);
        $this->assertEquals($context->method, $method);
    }

    public function testTransducer()
    {
        $context = new Context($this->request, $this->response, $this->container);
        $md = function (Request $req, \Closure $next) {
            $response = $next($req);
            $this->assertInstanceOf(Response::class, $response);
        };
        $context->add($md);

        $relay = Relay::createFromGlobal();
        $request = new Request($relay);
        $response = $context->transducer($request);

        $this->assertInstanceOf(Response::class, $response);

        $md =   $md = function (Request $req, \Closure $next) {
           yield 1;
        };

        $context->add($md);
        $relay = Relay::createFromGlobal();
        $request = new Request($relay);
        $response = $context->transducer($request);

        $this->assertInstanceOf(Response::class, $response);

//        $this->assertInstanceOf(\Generator::class, $gen);
//        $this->assertEquals($context->response->isFinish(), true);
//        $md = function ($req, $res) {
//            $res->end();
//        };
//        $mds = [$md, $md];
//        list($request, $response) = $this->serverProvider();
//        $context = new Context($request, $response);
//        $scheduler->run();
//        $this->assertEquals($context->response->isFinish(), true);
    }


}