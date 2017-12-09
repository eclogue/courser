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
use Pimple\Container;
use Swoole\Http\Server;
use Courser\App;

class HttpServer
{

    protected $worker = 1;

    protected $task = 1;

    protected $daemonzie = true;

    protected $server;

    protected $host = '127.0.0.1';

    protected $port = '8179';

    protected $setting = [];

    protected $scheduler;

    protected $container;


    public function __construct(App $app)
    {
        $this->app = $app;
        $this->container = [];
        $this->scheduler = new Scheduler();
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
        $this->container[$field] = $value;
    }


    /**
     * @param $req
     * @param $res
     */
    public function mount($req, $res)
    {
        try {
            $handler = $this->app->run($req->server['request_uri']);
            $result = $handler($req, $res);
            if ($result instanceof Generator) {
                $this->scheduler->add($result, true);
                $this->scheduler->run();
            }
        } catch (\Exception $error) {
            $this->app->handleError($req, $res, $error);
        }
    }


    public function start()
    {
        $this->server = new Server($this->host, $this->port);
        $tmpDir = sys_get_temp_dir();
        $config = [
            'daemonize' => false,
            'http_parse_post' => false,
            'dispatch_mode' => 3,
//            'log_file' => $tmpDir . '/courser.log',
            'upload_tmp_dir' => $tmpDir,
            'worker_num' => 2,
        ];
        $config = array_merge($config, $this->setting);
        $this->app->config($config);
        $this->server->set($config);
        $this->server->on('Request', [$this, 'mount']);
        foreach ($this->container as $key => $value) {
            $this->server->on($key, $value);
        }

        $this->server->start();
    }
}
