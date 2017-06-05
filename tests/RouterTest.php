<?php
/**
 * @license https://github.com/racecourse/courser/licese.md
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/6/3
 * @time: 下午2:46
 */

namespace Courser\Tests;

use Courser\Router;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Courser\Http\Request;
use Courser\Http\Response;
use Courser\Tests\Stub\Request as StubRequest;
use Courser\Tests\Stub\Request as StubResponse;


class RouterTest extends TestCase
{
    public $request;

    public $response;


    public function setUp()
    {
        $this->request = new Request();
        $this->request->setRequest(new StubRequest());
        $this->response = new Response();
        $this->response->setResponse(new StubResponse());

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

    public function testMethod() {
        $router = new Router($this->request, $this->response);
        $method = 'get';
        $router->method($method);
        $this->assertEquals($this->request->method, $method);
    }

    /**
     * @expectedException \Exception
     */
    public function testCompose()
    {
        $router = new Router($this->request, $this->response);
        $md = function ($req, $res) {
          $res->status(200)->end();
        };
        $router->add([$md]);
        $router->compose($router->callable);
        $this->assertEquals($router->response->finish, true);
        $router = new Router($this->request, $this->response);
        $mds = [$md, $md];
        $router->compose([$mds]);
        $this->expectException(Exception::class);
        $md = 'string';
        $router = new Router($this->request, $this->response);
        $router->compose([$mds]);
        $this->assertEquals($router->response->finish, false);
    }


}