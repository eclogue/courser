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
use Courser\Terminator;
use DI\Container;
use Hayrick\Http\Request;
use Hayrick\Http\Response;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Courser\Relay;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AppTest extends TestCase
{



    public function testSetContainer()
    {
        $app = new App();
        $container = new Container();
        $app->setContain($container);
        $this->assertSame($container, $app->getContainer());
    }

    public function testGetContainer()
    {
        $container = new Container();
        $app = new App($container);
        $app->setContain($container);
        $this->assertSame($container, $app->getContainer());
    }


    public function testConfig()
    {
        $config = [
            'a' => 1
        ];
        $app = new App();
        $app->config($config);
        $this->assertEquals($app->container->get('a'), $config['a']);
    }

    public function testCreateContext()
    {
        $app = new App();
        $request = Relay::createFromGlobal();
        $context = $app->createContext($request, null);
        $this->assertInstanceOf(Context::class, $context);
    }

    public function testAdd()
    {
        $app = new App();
        $md = $this->middlewareProvider();
        $app->add($md);
        $this->assertTrue(!empty($app->middleware->count()));
    }

    public function testGroup()
    {
        $app = new App();
        $path = '/test';
        $app->group($path, function ($app) use ($path) {
            // todo
            $this->assertEquals($app->group, $path);
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
        $this->assertArrayHasKey($method, $app->layer);
    }

    public function testMapMiddleware()
    {
        $app = new App();
        $uri = '/test';
        $deep = 1;
        $md = $this->middlewareProvider();
        $app->add($md);
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
        $md = $this->middlewareProvider();
        $request = Request::createRequest(Relay::createFromGlobal());
        $response = null;
        $app->add($md);
        $app->addRoute($method, $route, $callable);
        $context = $app->createContext($request, $response);
        $context = $app->mapRoute($method, $uri, $context);
        $this->assertTrue(true, $context->isMount());
        $this->assertContains($md, $context->middleware);
        $this->assertEquals($method, $context->method);
        $this->assertEquals(1, $context->request->getParam('id'));
    }
//
//    public function testGetPattern()
//    {
//        $app = new App();
//        $getPattern = function ($route) {
//            return $this->getPattern($route);
//        };
//        $getPattern = $getPattern->bindTo($app, $app);
//        $route = '/test/:id';
//        list($pattern, $params) = $getPattern($route);
//        $this->assertStringStartsWith('/test', $pattern);
//        $this->assertContains('id', $params);
//    }

    public function testGet()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->get('/test', $callable);
        $this->assertArrayHasKey('get', $app->layer);
        $this->assertTrue(!empty($app->layer['get']));
        $route = $app->layer['get'][0];
        $this->assertContains($callable, $route->callable);
    }

    public function testPut()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->put('/test', $callable);
        $this->assertArrayHasKey('put', $app->layer);
        $this->assertTrue(!empty($app->layer['put']));
        $route = $app->layer['put'][0];
        $this->assertContains($callable, $route->callable);
    }

    public function testPost()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->post('/test', $callable);
        $this->assertArrayHasKey('post', $app->layer);
        $this->assertTrue(!empty($app->layer['post']));
        $route = $app->layer['post'][0];
        $this->assertContains($callable, $route->callable);
    }

    public function testDelete()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->delete('/test', $callable);
        $this->assertArrayHasKey('delete', $app->layer);
        $this->assertTrue(!empty($app->layer['delete']));
        $route = $app->layer['delete'][0];
        $this->assertContains($callable, $route->callable);
    }

    public function testOptions()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->options('/test', $callable);
        $this->assertArrayHasKey('options', $app->layer);
        $this->assertTrue(!empty($app->layer['options']));
        $route = $app->layer['options'][0];
        $this->assertContains($callable, $route->callable);
    }

    public function testAny()
    {
        $app = new App();
        $callable = function ($req, $res) {

        };
        $app->any('/test', $callable);
        $this->assertArrayHasKey('get', $app->layer);
        $this->assertArrayHasKey('put', $app->layer);
        $this->assertArrayHasKey('post', $app->layer);
        $this->assertArrayHasKey('delete', $app->layer);
        $this->assertArrayHasKey('options', $app->layer);
        $this->assertTrue(!empty($app->layer['get']));
        $this->assertTrue(!empty($app->layer['post']));
        $this->assertTrue(!empty($app->layer['put']));
        $this->assertTrue(!empty($app->layer['delete']));
        $this->assertTrue(!empty($app->layer['options']));
    }

    public function testNotFound()
    {
        $app = new App();
        $callable = function ($req) {

        };
        $app->notFound($callable);
        $this->assertContains($callable, $app->notFounds);
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
        $app->handleError($request, null, $err);
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
}