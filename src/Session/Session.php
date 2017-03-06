<?php

/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/16
 * Time: 上午12:12
 */
namespace Courser\Session;


class Session
{
    private $config = [];

    private $store;

    private $prefix = '{course:session}';

    private $sessionKey = 'PHPSESSID';

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
        if(!empty($config['sessionIdName'])) {
            $this->sessionKey = $config['sessionIdName'];
        }

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
            $this->create($res, $req);
            echo PHP_EOL;
            echo $this->sId . "sssssssssssssssId\n";
            $this->store->setId($this->sId);
        }

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

    public function create($res, $req)
    {
        if($this->sId) return $this->sId;
        if(isset($req->cookie[$this->sessionKey])){
            return $this->sId = $req->cookie[$this->sessionKey];
        }
        $res->res->cookie($this->sessionKey, $this->sId, time() + 11111);
        return $this->sId;
    }

}
