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


    private $key;

    private $issuer;

    public $audience;

    public $id = '';

    public $expired = 1800;

    public $request;

    public $response;

    public $cookieName = 'courser::sess';

    public $sigName = 'courser::sess.sig';

    public $value = [];

    private $token;

    public $options = [];

    private $first = false;

    public function __construct($req, $res, $config)
    {
        $this->request = $req;
        $this->response = $res;
        $this->key = $config['key'];
        $this->audience = $config['audience'];
        $this->issuer = $config['issuer'];
        $this->options = $config['options'];
    }


    public function signer()
    {
        return new Sha256();
    }

    public function getToken($data)
    {
        $signer = $this->signer();
        $token = (new Builder())->setIssuer($this->issuer)
            ->setAudience($this->audience)
            ->setId($this->id, true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + $this->expired)
            ->set('data', $data)
            ->sign($signer, $this->key)
            ->getToken();

        $this->token = $token;

        return $token->__toString();
    }


    public function validate($token)
    {
        $this->getId();
        $token = (new Parser())->parse((string)$token); // Parses from a string
        if ($token->getClaim('exp') <= time() + $this->expired) return false;
        if ($token->getHeader('jti') !== $this->id) return false;
        if ($token->getClaim('iss') !== $this->issuer) return false;// will print "http://example.com"
        if ($token->getClaim('aud') !== $this->audience) return false;

        return $token->getClaim('data');
    }


    public function get($key)
    {
        $token = $this->request->cookie[$this->cookieName];
        if (!$this->first) {
            $value = $this->validate($token);
            if (!$value) {
                $this->response->cookie($this->cookieName, '', -1);
                $this->first = true;
                return null;
            }
            $this->value = $value;
        }

        if (empty($this->value)) return null;

        return isset($this->value[$key]) ? $this->value[$key] : null;
    }


    public function set($value)
    {
        return $this->value = array_merge($this->value, $value);
    }

    public function save()
    {
        $this->getId();
        $token = $this->getToken($this->value);
        $this->response->cookie($this->cookieName, $token, $this->expired, ...$this->options);
    }

    public function generateId()
    {
        $sId = md5(uniqid('sess:', true));
        $this->request->cookie[$this->sigName] = $sId;
        return $this->id = $sId;
    }

    public function getId()
    {
        if (!$this->id) {
            $this->id = $this->request->cookie[$this->sigName];
        }

        return $this->id;
    }


}