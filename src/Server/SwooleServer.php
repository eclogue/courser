<?php
/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/17
 * Time: 下午10:29
 */

namespace Courser\Server;

use Bulrush\Scheduler;
use Generator;
use Hayrick\Environment\Relay;
use Pimple\Container;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Server;
use Courser\App;
use Hayrick\Http\Response;

class SwooleServer implements ServerInterface
{

    protected $worker = 1;

    protected $task = 1;

    protected $daemonzie = true;

    protected $server;

    protected $host = '127.0.0.1';

    protected $port = '8179';

    protected $setting = [];

    protected $events = [];

    protected $scheduler;

    protected $container;

    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->container = new Container();
        $this->container['scheduler'] = new Scheduler();
        $this->container['response'] = function () {

            return $this->respond();
        };
        $this->container['request'] = function () {
            return $this->buildRequest();
        };
    }

    /**
     * @param $host
     * @param $port
     */
    public function bind($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @param array $setting
     */
    public function setting($setting = [])
    {
        $setting = is_array($setting) ? $setting : [];
        $this->setting = $setting;
    }

    /**
     * @param string $field
     * @param $value
     */
    public function register(string $field, $value)
    {
        $this->events[$field] = $value;
    }

    public function respond()
    {
        return function ($context) {
            return function (ResponseInterface $response) use ($context) {
                $output = $response ?? new Response();
                $headers = $output->getHeaders();
                foreach ($headers as $key => $header) {
                    $context->header($key, $header);
                }

                $context->status($output->getStatusCode());

                return $context->end($output->getContent());
            };
        };
    }

    public function buildRequest()
    {
        return array(Relay::class, 'createFromSwoole');
    }


    /**
     * @param $req
     * @param $res
     */
    public function mount($req, $res)
    {
        $app = clone $this->app;
        try {
            $handler = $app->run($req->server['request_uri']);
            $result = $handler($req, $res);
            if ($result instanceof Generator) {
                $scheduler = $this->container['scheduler'];
//                $scheduler = new Scheduler();
                $scheduler->add($result, true);
                $scheduler->run();
            }
        } catch (\Exception $error) {
            $app->handleError($req, $res, $error);
        }
        $app = null;
    }


    public function start()
    {
        $this->server = new Server($this->host, $this->port);
        $tmpDir = sys_get_temp_dir();
        $config = [
            'daemonize' => false,
            'http_parse_post' => false,
            'dispatch_mode' => 3,
            'upload_tmp_dir' => $tmpDir,
            'worker_num' => 1,

        ];
        $config = array_merge($config, $this->setting);
        $this->app->config($config);
        $this->app->setContainer($this->container);
        $this->server->set($config);
        $this->server->on('Request', [$this, 'mount']);
        foreach ($this->events as $key => $value) {
            $this->server->on($key, $value);
        }

        $this->server->start();
    }
}
