<?php
/**
 * Created by PhpStorm.
 * User: crab
 * Date: 2015/4/12
 * Time: 15:23
 */
namespace Courser\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/*
 * Http request extend swoole_http_request
 * the main properties and method are base on swoole
 * see https://wiki.swoole.com/wiki/page/328.html
 * */

class Request extends Message implements RequestInterface
{

    protected $params = [];

    /*
     * @var array
     * */
    public $methods = [];

    /*
     * @var array
     * */
    protected $body = [];

    /*
     * @var array
     * */
    protected $server = [];

    /*
     * @var string
     * */
    protected $method = 'get';

    /*
     * @var object
     * */
    protected $req;

    /*
     * @var array
     * */
    protected $cookie = [];

    /*
    * @var array
    * */
    protected $files = [];


    protected $uri;

    protected $requestTarget;

    protected $queryParams = [];

    protected $payloads = [];

    protected $query;

    /*
     * set request context
     * @param object $req  \Swoole\Http\Request
     * @return void
     * */
    public function createRequest($req)
    {
        $this->req = $req;
        $this->headers = $req->header;
        $this->cookie = isset($req->cookie) ? $req->cookie : []; // @todo psr-7 standard
        $this->server = $req->server;
        if (!isset($this->server['http_host']) && $this->hasHeader('http_host')) {
            $this->server['http_host'] = $this->getHeader('https_host');
        }
        $this->uri = new Uri($this->server);
        $this->files = isset($req->files) ? $req->files : []; // @todo
        $method = $req->server['request_method'] ?? 'get';
        $this->withMethod($method);
        $this->getRequestTarget();
        $this->query = $this->uri->getQuery();
        $this->queryParams = $this->parseQuery($this->query);
        $this->parseBody();
    }

    public function getOriginRequest()
    {
        return $this->req;
    }

    protected function parseQuery($query)
    {
        if (!is_string($query)) return [];
        parse_str($query, $output);
        return $output;
    }


    /*
     * get all params
     *
     * @return array
     * */
    public function getParams()
    {
        return $this->params;
    }

    /*
     * add param name
     *
     * @param string $name
     * @return void
     * */
    public function addParamName($name)
    {
        $this->paramNames[] = $name;
    }

    /*
     * set param
     *
     * @param string $key
     * @param string $val
     * @return void
     * */
    public function setParam($key, $val)
    {
        $this->params[$key] = $val;
    }

    /*
     * Get param by name
     *
     * @param string $name
     * @return mix
     * */
    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    /**
     * Set header
     *
     * @param $name
     * @param $value
     */
    public function setHeader($name, $value)
    {
        $this->header[$name] = $value;
    }


    /*
     * get cookie by key
     * @param string $key
     * @return mixed
     * */
    public function getCookie($key)
    {
        if (isset($this->cookie[$key])) return $this->cookie[$key];

        return null;
    }

    /*
     * get request body by param name
     *
     * @param string $key param name
     * @return string || null
     * */
    public function parseBody()
    {
        if (
            !empty($this->uri->post) &&
            $this->getHeader('content-type') === 'application/x-www-form-urlencoded'
        ) {
            $this->payloads = $this->req->post;
        } else {
            if (empty($this->payload)) {
                $this->payloads = json_decode($this->req->rawContent(), true);
            }
        }

        return $this->payloads;
    }

    /*
     * get url query param by name
     * @param string $key
     * @return mixed
     * */
    public function getQuery($key, $default = null)
    {
        return $this->queryParams[$key] ?? $default;
    }

    /**
     * Get request payload by key
     * this is not a part of PSR-7 standard
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function payload($key, $default = null)
    {
        return $this->payloads[$key] ?? $default;
    }

    /**
     * @param $request
     * @return $this
     */
    public function __invoke($request)
    {
        return clone $this;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->req->$name)) {
            return $this->req->$name;
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->req->$name = $value;
    }

    /**
     * @param $func
     * @param $params
     * @return bool|mixed
     */
    public function __call($func, $params)
    {
        if (is_callable([$this->req, $func])) {
            return call_user_func_array([$this->req, $func], $params);
        }

        return false;
    }

    // ===================== PSR-7 standard =====================

    /**
     * get request context method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Retrieves the URI instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri()
    {
        return $this->uri;
    }


    public function withRequestTarget($requestTarget)
    {
        $this->requestTarget = $requestTarget;

        return $this->requestTarget;
    }

    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }
        $target = $this->uri->getPath();
        if ($this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }
        if (empty($target)) {
            $target = '/';
        }
        $this->requestTarget = $target;
        return $this->requestTarget;
    }

    /**
     * @param string $method
     */
    public function withMethod($method)
    {
        $this->method = $method;
    }


    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if (!$preserveHost) {
            if ($uri->getHost() !== '') {
                $this->headers->set('Host', $uri->getHost());
            }
        } else {
            if ($uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeaderLine('Host') === '')) {
                $this->setHeader('Host', $uri->getHost());
            }
        }

    }


}