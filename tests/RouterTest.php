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
use RuntimeException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Courser\Http\Request;
use Courser\Http\Response;
use Courser\Tests\Stub\Request as StubRequest;
use Courser\Tests\Stub\Response as StubResponse;


class RouterTest extends TestCase
{
    public $request;

    public $response;


    public function setUp()
    {
        $this->request = new Request();
        $this->request->createRequest(new StubRequest());
        $response = new Response();
        $this->response = $response->createResponse(new StubResponse());

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
        $router = new Router($mock, $this->response);
        $router->setParam('key', 'value');
    }

    public function testMethod()
    {
        $router = new Router($this->request, $this->response);
        $method = 'get';
        $router->method($method);
        $this->assertEquals($router->request->getMethod(), $method);
    }

    public function testCompose()
    {
        $router = new Router($this->request, $this->response);
        $md = function ($req, $res) {
            $res->end();
        };
        $router->add([$md]);
        $router->compose($router->callable);
        $this->assertEquals($router->response->finish, true);
        list($request, $response) = $this->serverProvider();
        $router = new Router($request, $response);
        $md = 'string';
        $router->compose([$md]);
        $this->assertEquals($router->response->finish, false);
        $md = function ($req, $res) {
            $res->end();
        };
        $mds = [$md, $md];
        list($request, $response) = $this->serverProvider();
        $router = new Router($request, $response);
        $router->compose([$mds]);
//        $this->expectException(\RuntimeException::class);
        $this->assertEquals($router->response->finish, true);
    }

    public function serverProvider()
    {
        $request = new Request();
        $request->createRequest(new StubRequest());
        $response = new Response();
        $response = $response->createResponse(new StubResponse());
        return [$request, $response];
    }


}