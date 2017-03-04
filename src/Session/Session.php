<?php

/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/16
 * Time: 上午12:12
 */
namespace Courser\Session;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;


class Session
{
    public $config = [];

    public $store;

    private $cookieKey = 'COURSER::SESS';

    private $key = 'some key';

    public $expired = 3600;

    protected $request;

    protected $response;

    public function __construct($config)
    {
        $this->config = $this->init($config);
        $this->store = $this->config['store'];
        $this->expired = $this->config['expired'];

        $signer = new Sha256();
        $data = [
            'username' => 'fuck',
            'password' => '123123123123',
        ];
        $token = (new Builder())->setIssuer('http://example.com')// Configures the issuer (iss claim)
        ->setAudience('http://example.org')// Configures the audience (aud claim)
        ->setId('4f1g23a12aa', true)// Configures the id (jti claim), replicating as a header item
        ->setIssuedAt(time())// Configures the time that the token was issue (iat claim)
        ->setNotBefore(time() + 60)// Configures the time that the token can be used (nbf claim)
        ->setExpiration(time() + 3600)// Configures the expiration time of the token (nbf claim)
        ->set('uid', $data)// Configures a new claim, called "uid"
        ->sign($signer, $this->key)// creates a signature using "testing" as key
        ->getToken(); // Retrieves the generated token

        $len = $token->__toString();
        $token = (new Parser())->parse((string)$len); // Parses from a string
//        $token->getHeaders(); // Retrieves the token header
//        $token->getClaims(); // Retrieves the token claims
        echo $token->getHeader('jti'); // will print "4f1g23a12aa"
        echo $token->getClaim('iss'); // will print "http://example.com"
        var_dump($token->getClaim('uid')); // will print "1
        echo $token->getClaim('aud') .PHP_EOL;
        echo $token->getClaim('exp'). PHP_EOL;
        echo $token->getClaim('iat') .PHP_EOL;

        echo strlen($len);
        var_dump($token->verify($signer, 'testing 1')); // false, because the key is different
        var_dump($token->verify($signer, 'testing')); // true, because the key is the same
    }

    public function __invoke($req, $res)
    {
        $this->request = $req;
        $this->response = $res;
        $key = $this->cookieKey;
        $signKey = $this->cookieKey . '.sig';
        $sId = $req->cookie[$signKey];
        if (!$sId) $sId = $this->generateSig();
    }

    private function init($config)
    {
        $config['expired'] = isset($config['expired']) ?: 1800;
        $config['domain'] = isset($config['domain']) ?: '';
        $config['store'] = isset($config['store']) ?: null;

        return $config;
    }

    public function signer()
    {
        return new Sha256();
    }

    public function token($sId, $data)
    {
        $signer = $this->signer();
        $issuer = $this->config['audience'] ?: $this->request->sever['http_host'];
        $audience = $this->config['domain'] ?: $issuer;
        $token = (new Builder())->setIssuer($issuer)
            ->setAudience($audience)
            ->setId($sId, true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + $this->expired)
            ->set('data', $data)
            ->sign($signer, $this->key)
            ->getToken();

        return $token->__toString();
    }


    public function save()
    {

    }


    public function getSessionId()
    {
        return $this->request->cookie[$this->key];
    }

    public function setSig($sId)
    {
        return $this->request->cookie[$this->key] = $sId;
    }


    public function getSigKey()
    {
        return $this->cookieKey . '.sig';
    }

    public function getSig()
    {
        $key = $this->getSigKey();
        return $this->request->cookie[$key];
    }

    public function get()
    {
        $token = $this->request->cookie[$this->cookieKey];
        if (!$token) return null;
        $sig = $this->getSig();
        if (!$sig) return null;


    }

    public function validate($token)
    {
        $token = (new Parser())->parse((string) $token); // Parses from a string
        $token->getHeaders(); // Retrieves the token header
        $token->getClaims(); // Retrieves the token claims
        echo $token->getHeader('jti'); // will print "4f1g23a12aa"
        echo $token->getClaim('iss'); // will print "http://example.com"
        var_dump($token->getClaim('uid'));
    }

    public function set($value)
    {

    }

    public function generateSig()
    {
        $sId = md5(uniqid('sess:', true));
        $this->setSig($sId);
        return $sId;
    }
}
