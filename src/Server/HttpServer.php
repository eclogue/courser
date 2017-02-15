<?php
/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/17
 * Time: 下午10:29
 */

namespace Barge\Server;

use Barge\Barge;
use Swoole\Http\Server;
use Barge\Set\Config;

class HttpServer
{

    public $worker = 2;

    public $task = 4;

    public $daemonzie = true;

    public $server = '';

    public $host = '127.0.0.1';

    public $port = '5001';

    private $app = '';


    public function __construct($path, $config)
    {
        $this->app = new Barge();
        $this->app->init($path, $config);
        $this->host = Config::get('host', '127.0.0.1');
        $this->port = Config::get('port', '5001');
    }

    public function app() {
        return $this->app;
    }


    public function mount($req, $res) {
        if(!is_file($req->server['request_uri'])) {
//            $request = new \ReflectionClass($req);
//            $response = new \ReflectionClass($res);
            $this->app->setRequest($req);
            $this->app->setResponse($res);
//            var_dump($res->end('dddd'));
            $this->app->run();
        }
    }

    public function start() {
        $this->server = new Server($this->host, $this->port);
        $this->server->on('Request', [$this, 'mount']);
        $this->server->start();
    }
}