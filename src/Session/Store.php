<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/4
 * @time      : 下午10:14
 */

namespace Courser\Session;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;


class Store
{

    private $id = '';

    private $request;

    private $response;

    private $cookieName = 'courser::session';

    private $value = [];

    private $options = [];

    private $first = false;

    private $config = [];

    private $key = '';

    public static $store;

    public function __construct($req, $res, $config)
    {
        $this->request = $req;
        $this->response = $res;
        $this->config = $this->init($config);
        $this->key = $this->config['key'];
        $this->options = $config['options'];
    }

    public function init($config)
    {
        if (!isset($config['issuer'])) {
            $config['issuer'] = 'https://github.com/racecourse';
        }
        if (!isset($config['audience'])) {
            $config['issuer'] = 'https://github.com/racecourse/crane';
        }
        if(!isset($config['expired'])) {
            $config['expired'] = 1800;
        }
        if(!isset($config['options'])) {
            $config['options'] = [];
        }
        if(!isset($config['key'])) {
            throw new \Exception('Session store must provide a private key');
        }

        return $config;
    }


    public static function getStore($req, $res, $config){
        if(!self::$store) {
            return static::$store = new static($req, $res, $config);
        }

        return static::$store;
    }


    public function signer()
    {
        return new Sha256();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getToken($data, $id)
    {
        $signer = $this->signer();
        $token = (new Builder())->setIssuer($this->config['issuer'])
            ->setAudience($this->config['audience'])
            ->setId($id, true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + $this->config['expired'])
            ->set('data', $data)
            ->sign($signer, $this->key)
            ->getToken();

        $this->token = $token;

        return $token->__toString();
    }


    public function validate($token)
    {
        $token = (new Parser())->parse((string)$token); // Parses from a string
        var_dump($token->getClaim('exp'));
        var_dump($token->getHeader('jti'));
        var_dump($token->getClaim('iss'));
        var_dump($token->getClaim('data'));
        var_dump($token->getClaim('aud'));

//        if ($token->getClaim('exp') >= time() + $this->expired) return false;
        if ($token->getHeader('jti') !== $this->id) return false;
        if ($token->getClaim('iss') !== $this->config['issuer']) return false;// will print "http://example.com"
        if ($token->getClaim('aud') !== $this->config['audience']) return false;

        return $token->getClaim('data');
    }


    public function get($key)
    {
        if (!$this->first) {
            $cookie = $this->request->cookie;
            $token = isset($cookie[$this->cookieName]) ? $cookie[$this->cookieName] : null;
            if (!$token) return null;
            $value = $this->validate($token);
            if (!$value) {
                $this->response->res->cookie($this->cookieName, '', -1);
                $this->first = true;
                return null;
            }
            $this->value = (array)$value;
        }

        if (empty($this->value)) return null;

        return isset($this->value[$key]) ? $this->value[$key] : null;
    }


    public function set($key, $value)
    {
        $this->value[$key] = $value;
        $this->save();
    }

    public function save()
    {
        $token = $this->getToken($this->value, $this->id);
        $this->response->res->cookie($this->cookieName, $token, time() + 100000, ...$this->options);
    }

}