<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/12/9
 * @time: 下午7:07
 */

namespace Courser\Server;

use Bulrush\Scheduler;
use Generator;
use Courser\App;
use Hayrick\Environment\Relay;
use Pimple\Container;
use Hayrick\Environment\Reply;
use Psr\Http\Message\ResponseInterface;

class CGIServer implements ServerInterface
{


    protected $setting = [];

    protected $scheduler;

    protected $container;

    protected $app;


    public function __construct(App $app)
    {
        $this->app = $app;
        $this->container = new Container();
        $this->container['scheduler'] = new Scheduler();
        $this->container['terminator'] = function () {
            return $this->respond();
        };

        $this->container['request'] = function () {
            return $this->buildRequest();
        };
    }

    /**
     * @param array $setting
     */
    public function setting($setting = [])
    {
        $setting = is_array($setting) ? $setting : [];
        $this->setting = $setting;
    }

    public function respond()
    {
        return function (Reply $context) {
            return function (ResponseInterface $response) use ($context) {
                return $context($response);
            };
        };
    }

    public function buildRequest()
    {
        return (function () {
            return Relay::createFromCGI();
        })->bindTo(null, null);
    }

    /**
     * @param $req
     * @param $res
     */
    public function mount($req, $res)
    {
        try {
            // @todo
            $handler = $this->app->run($req->server['request_uri']);
            $result = $handler($req, $res);
            if ($result instanceof Generator) {
                $scheduler = $this->container['scheduler'];
                $scheduler->add($result, true);
                $scheduler->run();
            }
        } catch (\Exception $error) {
            $this->app->handleError($req, $res, $error);
        }
    }


    public function start()
    {
        $config = $this->setting;
        $this->app->config($config);
        $request = null;
        $response = new Reply();
        $this->app->setContainer($this->container);
        $this->mount($request, $response);
    }
}