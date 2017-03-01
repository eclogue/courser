<?php
/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/17
 * Time: 下午10:29
 */

namespace Courser\Server;

use Courser\Courser;
use Swoole\Http\Server;
use Courser\Set\Config;

class HttpServer
{

    public $worker = 2;

    public $task = 4;

    public $daemonzie = true;

    public $server = '';

    public $host = '127.0.0.1';

    public $port = '5001';


    public function __construct($config)
    {
        Config::set($config);
        $this->config = $config;
        $this->host = Config::get('host', '127.0.0.1');
        $this->port = Config::get('port', '5001');
    }


    public function mount($req, $res)
    {
        if ($req->server['request_uri'] !== '/favicon.ico') {
            $env = $this->config;
            $app = Courser::run($env);
            $app($req, $res);
        }
    }

    public function start()
    {
        $this->server = new Server($this->host, $this->port);
        $tmpDir = sys_get_temp_dir();
        $config = [
            'daemonize' => false,
            'dispatch_mode' => 3,
            'log_file' => $tmpDir . '/Courser.log',
            'upload_tmp_dir'=> $tmpDir,
        ];
        $config = array_merge($config, Config::get('server', []));
        $this->server->set($config);
        $this->server->on('Request', [$this, 'mount']);
        $this->server->start();
    }
}