<?php

/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/5/12
 * @time: 下午7:03
 */
namespace Courser\Tests;

use Courser\App;
use Courser\Context;
use Hayrick\Http\Request;
use Hayrick\Http\Response;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Courser\Tests\Stub\Request as StubRequest;
use Hayrick\Environment\Relay;
use Hayrick\Environment\Reply;

class AppTest extends TestCase
{


//    public function testContainer()
//    {
//        $app = new App();
//        $this->assertInstanceOf('Courser\Http\Request', $app->container['courser.request']);
//        $this->assertInstanceOf('Courser\Http\Response', $app->container['courser.response']);
//    }

    public function testConfig()
    {
        $config = [
            'a' => 1
        ];
        $app = new App();
        $app->config($config);
        $this->assertEquals($app->container['a'], $config['a']);
    }

    public function testCreateContext()
    {
        $app = new App();
        $request = Relay::createFromGlobal();
        $response = new Reply();
        $context = $app->createContext($request, $response);
        $this->assertInstanceOf(Context::class, $context);
    }

    public function testUsed()
    {
        $app = new App();
        $this->assertTrue(empty($app->middleware));
        $app->used(function ($req, $res) {

        });
        $this->assertTrue(!empty($app->middleware));
    }

    public function testGroup()
    {
        $app = new App();
        $path = '/test';
        $self = $this;
        $app->group($path, function () use ($path, $self) {
            // todo
            $self->assertEquals($this->group, $path);
        });

    }

    public function testAddRoute()
    {
        $app = new App();
        $uri = '/test';
        $method = 'get';
        $callable = function ($req, $res) {

        };
        $app->addRoute($method, $uri, $callable);
        $this->assertArrayHasKey($method, $app->routes);
        $this->assertArrayHasKey('route', $app->routes[$method][$uri]);
        $this->assertArrayHasKey('params', $app->routes[$method][$uri]);
        $this->assertArrayHasKey('scope', $app->routes[$method][$uri]);
        $this->assertArrayHasKey('pattern', $app->routes[$method][$uri]);
        $this->assertContains('callable', $app->routes[$method][$uri]);
    }

    public function testMapMiddleware()
    {
        $app = new App();
        $uri = '/test';
        $deep = 1;
        $md = function (...$params) {
            return $params;
        };
        $app->used($md);
        $callable = $app->mapMiddleware($uri, $deep);
        $this->assertContains($md, $callable);
    }
//
    public function testMapRoute()
    {
        $app = new App();
        $uri = '/test/1';
        $route = '/test/:id';
        $method = 'get';
        $called = 1;
        $callable = function () use ($called) {
            return $called;
        };
        $request = new Request(Relay::createFromGlobal());
        $response = new Reply();
        $app->used($callable);
        $app->addRoute($method, $route, $callable);
        $context = $app->createContext($request, $response);
        $result = $app->mapRoute($method, $uri, $context);
        $this->assertContains($callable, $result->middleware);
        $this->assertEquals($method, $result->request->getMethod());
        $this->assertAttributeContains('id', 'paramNames', $result);
        $this->assertEquals(1, $result->request->getParam('id'));
        $uri = $uri . '/test';
        $router = $app->createContext($request, $response);
        $result = $app->mapRoute($method, $uri, $router);
        $this->assertContains($callable, $result->middleware);
    }

    public function testGetPattern()
    {
        $app = new App();
        $getPattern = function ($route) {
            return $this->getPattern($route);
        };
        $getPattern = $getPattern->bindTo($app, $app);
        $route = '/test/:id';
        list($pattern, $params) = $getPattern($route);
        $this->assertStringStartsWith('/test', $pattern);
        $this->assertContains('id', $params);
    }

    public function testGet()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->get('/test', $callable);
        $this->assertArrayHasKey('get', $app->routes);
        $this->assertTrue(!empty($app->routes['get']));
    }

    public function testPut()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->put('/test', $callable);
        $this->assertArrayHasKey('put', $app->routes);
        $this->assertTrue(!empty($app->routes['put']));
    }

    public function testPost()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->post('/test', $callable);
        $this->assertArrayHasKey('post', $app->routes);
        $this->assertTrue(!empty($app->routes['post']));
    }

    public function testDelete()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->delete('/test', $callable);
        $this->assertArrayHasKey('delete', $app->routes);
        $this->assertTrue(!empty($app->routes['delete']));
    }

    public function testOptions()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->options('/test', $callable);
        $this->assertArrayHasKey('options', $app->routes);
        $this->assertTrue(!empty($app->routes['options']));
    }

    public function testAny()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->any('/test', $callable);
        $this->assertArrayHasKey('get', $app->routes);
        $this->assertArrayHasKey('put', $app->routes);
        $this->assertArrayHasKey('post', $app->routes);
        $this->assertArrayHasKey('delete', $app->routes);
        $this->assertArrayHasKey('options', $app->routes);
        $this->assertTrue(!empty($app->routes['get']));
        $this->assertTrue(!empty($app->routes['post']));
        $this->assertTrue(!empty($app->routes['put']));
        $this->assertTrue(!empty($app->routes['delete']));
        $this->assertTrue(!empty($app->routes['options']));
    }

    public function testNotFound()
    {
        $app = new App();
        $callable = function ($req) {

        };
        $app->notFound($callable);
        $this->assertContains($callable, $app->notFounds);
    }

    public function testSetContainer()
    {
        $app = new App();
        $callable = function ($req, $err) {

        };
        $app->setReporter($callable);
        $this->assertEquals($callable, $app->reporter);
    }

    public function testHandleError()
    {
        $app = new App();
        $handle = function ($req, $err)  {
            $this->assertInstanceOf(Request::class, $req);
            $this->assertInstanceOf(\Exception::class, $err);
        };
        $app->setReporter(function ($req, $err)  {
            $this->assertInstanceOf(Request::class, $req);
            $this->assertInstanceOf(\Exception::class, $err);
        });
        $err = new Exception();
        $request = Relay::createFromGlobal();
        $response = new Reply();
        $app->handleError($request, $response, $err);
    }
//
//    public function testRun() // @todo
//    {
//        $app = new App();
//        $uri = '/';
//        $run = $app->run($uri);
//        $this->assertInstanceOf(\Closure::class, $run);
//
//    }
//
//    public function testImport()
//    {
//        $app = new App();
//        $loader = [
//            'TestImport' => 'Courser\Tests\Stub\Test',
//        ];
//        $app->import($loader);
//        $this->assertTrue(class_exists('TestImport'));
//        return $app;
//    }
//
//
//    public function testAlias()
//    {
//        $app = new App();
//        $func = function ($name) {
//            return $this->alias($name);
//        };
//        $func = $func->bindTo($app, $app);
//        $alias = $func('TestImport');
//        $this->assertEquals('courser.loader.TestImport', $alias);
//        return $alias;
//    }
//
//    /**
//     * @depends testImport
//     */
//    public function testLoad(App $app)
//    {
//        $isNull = $app->load('BadClass');
//        $this->assertNull($isNull);
//        $name = 'TestImport';
//        $test = new \TestImport();
//        $this->assertInstanceOf($app->loader[$name], $test);
//        $this->assertInstanceOf($app->loader[$name], $app->container['courser.loader.' . $name]);
//    }
//
//
//    public function requestProvider($method, $uri)
//    {
//        $req = new StubRequest();
//        $req->cookie = [];
//        $req->header = [];
//        $req->get = [];
//        $req->post = [];
//        $req->server = array_change_key_case($_SERVER, CASE_LOWER);
//        $req->server['request_method'] = $method;
//        $req->server['request_uri'] = $uri;
//        $req->files = [];
//
//        return $req;
//    }
//
//    public function responseProvider()
//    {
//        return new StubRequest();
//    }
}