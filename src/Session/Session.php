<?php

/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/16
 * Time: 上午12:12
 */
namespace Courser\Session;

use Courser\Session\Store;


class Session
{
    private $config = [];

    private $store;

    private $prefix = '{course:session}';

    private $sessionKey = 'courser::sess';


    private $sId;

    public static $session;

    public function __construct($config = [])
    {
        $this->config = $this->format($config);
    }

    public function format($config)
    {
        $config['store'] = isset($config['store']) ? $config['store'] : null;
        $config['expired'] = isset($config['expired']) ? $config['expired'] : 1800;
        $config['expired'] = time() + $config['expired'];
        $config['options'] = isset($config['options']) ? $config['options'] : [];

        return $config;
    }


    public function __invoke($req, $res)
    {
        if($this->config['store'] === 'php'){
            $req->session = $_SESSION;
        } else if(isset($this->config['store'])) {
            $this->store = $this->config['store'];
        }else {
            $this->store = new Store($req, $res, $this->config);
            if(!$this->sId) $this->create($res);
            $this->store->setId($this->sId);
        }

//        var_dump($this->store);
        $req->session = $this;
    }

    static public function getSession($config)
    {
        if(!self::$session) {
            self::$session = new self($config);
        }

        return self::$session;
    }

    public function __get($key)
    {
        $key = $this->prefix . $key;
        echo "$key --=-=-=>\n";
        return $this->store->get($key);
    }

    public function __set($name, $value)
    {
        $name = $this->prefix . $name;
        echo "$name~~~~~~ $value \n";
        return $this->store->set($name, $value);
    }

    public function sessionId($req) {
        if(!$this->sId) {
            $this->sId = $req->cookie[$this->sessionKey];
        }

        return $this->sId;
    }

    public function create($res)
    {
        $this->sId = $this->generateId($res);
    }

    public function generateId($res)
    {
        $sId = md5(uniqid('sess:', true));
        echo "****** sId: :::: $sId {$this->config['expired']} \n";
        echo $this->sessionKey .PHP_EOL;
        $res->res->cookie($this->sessionKey, $sId, time() + 11111);
        return $sId;
    }
}
