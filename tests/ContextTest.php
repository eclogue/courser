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
use Courser\Route;
use PHPUnit\Framework\TestCase;
use Hayrick\Http\Request;
use Hayrick\Http\Response;
use DI\Container;
use Courser\Terminator;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;


class ContextTest extends TestCase
{
    public $request;

    public $response;

    public $container;

    public $context;


    public function setUp()
    {
        $this->request = Relay::createFromGlobal();
        $this->response = null;
        $container = new Container();
        $container->set('request.resolver', [Relay::class, 'createFromGlobal']);
        $container->set('response.resolver', Terminator::class);
        $this->container = $container;

    }

    public function middlewareProvider()
    {
        $md = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle($request);
            }
        };

        return $md;
    }

    public function contextProvider()
    {
        return  new Context($this->request, $this->response, $this->container);
    }


    public function testUse()
    {
        $context = $this->contextProvider();
        $md = $this->middlewareProvider();
        $context->use($md);
        $this->assertContains($md, $context->middleware);
    }

    public function testAdd()
    {
        $context = $this->contextProvider();
        $route = new Route('get', '/', []);
        $context->add($route);
        $this->assertEmpty($context->callable);
        $callable = function() {

        };
        $route = new Route('get', '/', [$callable]);
        $context->add($route);
        $this->assertContains($callable, $context->callable);
        $key = 'id';
        $value = 1;
        $route = new Route('get', '/test/:id', [$callable]);
        $mock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['setParam'])
            ->getMock();
        $mock->expects($this->once())
            ->method('setParam')
            ->with($this->equalTo($key), $this->equalTo($value));
        $context->request = $mock;
        $context->add($route, [$key => $value]);
//        $this->assertAttributeContains($callable, 'callable', $context);
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
        $context = $this->contextProvider();
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
        $context = $this->contextProvider();
        $md = $this->middlewareProvider();
        $context->use($md);
        $relay = Relay::createFromGlobal();
        $request = Request::createRequest($relay);
        $response = $context->transducer($request);
        $this->assertInstanceOf(Response::class, $response);
        $md =  $this->middlewareProvider();
        $context->use($md);
        $request = Request::createRequest($relay);
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