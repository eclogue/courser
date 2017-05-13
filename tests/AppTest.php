<?php

/**
 * @license https://github.com/racecourse/courser/licese.md
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/5/12
 * @time: 下午7:03
 */
namespace Courser\Tests;

use Courser\App;
use PHPUnit\Framework\TestCase;
use Courser\Tests\Entity\Response;
use Courser\Tests\Entity\Request;


class AppTest extends TestCase
{
    public static $config = [];

    public static function setupBeforeClass()
    {
        static::$config = [

        ];
    }

    public function testContainer()
    {
        $app = new App();
        $this->assertInstanceOf('Courser\Http\Request', $app->container['courser.request']);
        $this->assertInstanceOf('Courser\Http\Response', $app->container['courser.response']);
    }

//    public function testCreateContext() {
//        $app = new App();
//        $req = new Request();
//        $res = new Response();
//        $router = $app->createContext($req, $res);
//        $this->assertInstanceOf('Courser\Router\Router', $router);
//    }

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
        $this->assertArrayHasKey('route', $app->routes[$method][0]);
        $this->assertArrayHasKey('params', $app->routes[$method][0]);
        $this->assertArrayHasKey('scope', $app->routes[$method][0]);
        $this->assertArrayHasKey('pattern', $app->routes[$method][0]);
        $this->assertArrayHasKey('callable', $app->routes[$method][0]);
    }

    public function testMapMiddleware()
    {
        $app = new App();
        $uri = '/test';
        $deep = 1;
        $md = function ($req, $res) {
            return 1;
        };
        $app->used($md);
        $callable = $app->mapMiddleware($uri, $deep);
        $this->assertContains($md, $callable);
    }

    public function testMapRoute()
    {
        $app = new App();
        $uri = '/test/1';
        $route = '/test/:id';
        $method = 'get';
        $callable = function ($req, $res) {
            return 1;
        };
        $res = $this->responseProvider();
        $app->used($callable);
        $app->addRoute($method, $route, $callable);
        $req = $this->requestProvider($method, $uri);
        $router = $app->createContext($req, $res);
        $result = $app->mapRoute($method, $uri, $router);
        $this->assertContains($callable, $result->middleware);
        $this->assertEquals($method, $result->request->method);
        $this->assertContains('id', $result->paramNames);
        $this->assertEquals(1, $result->request->param('id'));
        $uri = $uri . '/test';
        $req = $this->requestProvider($method, $uri);
        $router = $app->createContext($req, $res);
        $result = $app->mapRoute($method, $uri, $router);
        $this->assertEquals($router, $result);
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

    public function requestProvider($method, $uri)
    {
        $req = new Request();
        $req->cookie = [];
        $req->header = [];
        $req->get = [];
        $req->post = [];
        $req->server = array_change_key_case($_SERVER, CASE_LOWER);
        $req->server['request_method'] = $method;
        $req->server['request_uri'] = $uri;
        $req->files = [];
        return $req;
    }

    public function responseProvider()
    {
        return new Response();
    }
}