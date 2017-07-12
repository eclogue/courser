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
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;

/*
 * Http request extend swoole_http_request
 * the main properties and method are base on swoole
 * see https://wiki.swoole.com/wiki/page/328.html
 * */

class Request extends Message implements RequestInterface, ServerRequestInterface
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

    protected $payload = [];

    protected $query;

    protected $attributes = [];


    /*
     * set request context @todo
     * @param object $req  \Swoole\Http\Request
     * @return void
     * */
    public function createRequest(SwooleRequest $req)
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
        $this->getParsedBody();
        $this->query = $this->uri->getQuery();
        $this->queryParams = $this->parseQuery($this->query);
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

    //======================= ServerRequestInterface =======================//

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->server;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookie;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $this->cookie = array_merge($this->cookie, $cookies);
        return $this;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *     $_GET.
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $this->queryParams = array_merge($this->queryParams, $query);
        return $this;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $this->files;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->files = array_merge($this->files, $uploadedFiles);
        return $this;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        if (
            !empty($this->uri->post) &&
            $this->getHeader('content-type') === 'application/x-www-form-urlencoded'
        ) {
            $this->payload = $this->req->post;
        } else {
            if (empty($this->payload)) {
                $this->payload = json_decode($this->req->rawContent(), true);
            }
        }

        return $this->payload;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *     typically be in an array or object.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data)
    {
        $this->payload = array_merge($this->payload, $data);
        return $this;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return static
     */
    public function withoutAttribute($name)
    {
        unset($this->attributes[$name]);
        return $this;
    }


}