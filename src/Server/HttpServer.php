<?php
/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/17
 * Time: 下午10:29
 */

namespace Courser\Server;

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


    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function bind($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function set($setting = [])
    {
        $setting = is_array($setting) ? $setting : [];
        $this->setting = $setting;
    }


    public function mount($req, $res)
    {
        try {
            $app = $this->app->run($req->server['request_uri']);
            $app($req, $res);
        } catch (\Exception $e) {
            $this->app->handleError($req, $res, $e);
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
            'log_file' => $tmpDir . '/courser.log',
            'upload_tmp_dir' => $tmpDir,
            'worker_num' => 2,
        ];
        $config = array_merge($config, $this->setting);
        $this->server->set($config);
        $this->server->on('Request', [$this, 'mount']);
        $this->server->start();
    }
}
