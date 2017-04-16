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
        return $this->store->get($key);
    }

    public function __set($name, $value)
    {
        $name = $this->prefix . $name;
        return $this->store->set($name, $value);
    }

    public function sessionId($req) {
        if(!$this->sId) {
            $this->sId = $req->cookie[$this->sessionKey];
        }

        return $this->sId;
    }

    public function save()
    {
        $this->store->save();
    }

    public function create($res, $req)
    {
        if($this->sId) return $this->sId;
        if(!empty($req->cookie[$this->sessionKey])){
            return $this->sId = $req->cookie[$this->sessionKey];
        }
        $this->sId = md5(uniqid('courser:sessId', true));
        $res->res->cookie($this->sessionKey, $this->sId, time() + $this->config['expired']);
        return $this->sId;
    }

}
