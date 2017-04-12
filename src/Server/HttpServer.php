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
use Courser\Helper\Config;

class HttpServer
{

    public $worker = 2;

    public $task = 4;

    public $daemonzie = true;

    public $server = '';

    public $host = '127.0.0.1';

    public $port = '5001';


    public function __construct($app)
    {
        $this->app = $app;
        $this->host = Config::get('host', '127.0.0.1');
        $this->port = Config::get('port', '5001');
    }


    public function mount($req, $res)
    {
        try {
            $app = $this->app->run($req->server['request_uri']);
            $app($req, $res);
        } catch (\Exception $e) {
            $req->status(500)->end('<h3> Courser Server Error~!</h3>');
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
            'upload_tmp_dir' => $tmpDir,
        ];
        $config = array_merge($config, Config::get('server', []));
        $timeZone = Config::get('time.zone');
        if (!$timeZone) {
            ini_set('date.timezone', 'Asia/Shanghai');
        }
        $this->server->set($config);
        $this->server->on('Request', [$this, 'mount']);
        $this->server->start();
    }
}