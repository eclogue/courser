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

    private $audience;

    private $id = '';

    private $expired = 1800;

    private $request;

    private $response;

    private $cookieName = 'courser::sess';

    private $sigName = 'courser::sess.sig';

    private $value = [];

    private $token;

    private $options = [];

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

    public function setId ($id)
    {
        $this->id = $id;
    }

    public function getToken($data, $id)
    {
        $signer = $this->signer();
        $token = (new Builder())->setIssuer($this->issuer)
            ->setAudience($this->audience)
            ->setId($id, true)
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

        if (!$this->first) {
            $cookie = ($this->request->cookie);
            $token = isset($cookie[$this->cookieName]) ? $cookie[$this->cookieName] : null;
            if (!$token) return null;
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


    public function set($key, $value)
    {
        echo "set cookie $key => $value \n";
        $this->value[$key] = $value;
        $this->save();
    }

    public function save()
    {
        echo "this is sissss sava \n";
        $token = $this->getToken($this->value, $this->id);
        $this->response->res->cookie($this->cookieName, $token, time() + 100000, ...$this->options);
    }

    public function generateId()
    {
        $sId = md5(uniqid('sess:', true));
        $this->response->res->cookie[$this->sigName] = $sId;
        return $this->id = $sId;
    }

    public function getId()
    {
        if (!$this->id) {
            $cookie = ($this->request->cookie);
            if (isset($cookie[$this->sigName])) {
                $this->id = $cookie[$this->sigName];
            } else {
                $id = $this->generateId();
                ($this->request->cookie)[$this->sigName] = $id;
            }
        }

        return $this->id;
    }

    public function __destruct()
    {
        $this->save();
    }


}