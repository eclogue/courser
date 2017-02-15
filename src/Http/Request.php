<?php
/**
 * Created by PhpStorm.
 * User: crab
 * Date: 2015/4/12
 * Time: 15:23
 */
namespace Barge\Http;

use Barge\Http\StdObject;
use Barge\Http\Header;

class Request extends StdObject
{

    private $params = [];

    public $paramNames = [];

    public $methods = [];

    public $body = [];

    public $header = [];

    public $server = [];

    public $method = '';


    public function __construct(array $arguments = [])
    {
        $this->server = array_change_key_case($_SERVER, CASE_LOWER);
        $this->method = isset($this->server['request_method']) ? $this->server['request_method'] : 'get';
        $this->uri = isset($this->server['request_uri']) ? $this->server['request_uri'] : '/';
        $this->body = $this->getBody();
        $this->header = array_merge(Header::defaultHeader(), $this->server);
        parent::__construct($arguments);
    }


    /*
     * 活取当前http求情的method
     * */
    public function getMethod()
    {
        return $this->method;
    }


    /*
     * 活取所有http请求参数
     * */
    public function getParams()
    {
        return $this->params;
    }

    /*
     * 添加请求参数名
     * @param string $name
     * @return void
     * */
    public function addParamName($name)
    {
        $this->paramNames[] = $name;
    }

    public function setParam($key, $val)
    {
        if (in_array($key, $this->paramNames))
            $this->params[$key] = $val;
    }

    /*
     * 设置http求情方法
     * */
    public function setMethod()
    {
        $argv = func_get_args();
        $this->method = array_merge($this->method, $argv);
    }

    public function header($name)
    {
        return isset($this->header[$name]) ? $this->header[$name] : null;
    }

    public function get($name)
    {
        return $_GET[$name] ?: false;
    }

    public function cookie()
    {

    }

    public function post($key)
    {
        if(empty($_POST)) return false;
        if(isset($_POST[$key])) return $_POST[$key];
        if(isset($this->body[$key])) return $this->body[$key];

        return false;
    }


//    public function accept($) {
//
//    }

    public function __invoke($request)
    {
        return $this;
    }

    /**
     * Returns rest request parameters.
     * @return array the request parameters
     */
    public function getBody()
    {

        $result = [];
        $httpMethod = array('get', 'put', 'delete', 'put', 'patch', 'options');
        if (!isset($_SERVER['REQUEST_METHOD']) || !in_array(strtolower($_SERVER['REQUEST_METHOD']), $httpMethod))
            return $result;
        if (function_exists('mb_parse_str'))
            mb_parse_str(file_get_contents('php://input'), $result);
        else
            parse_str(file_get_contents('php://input'), $result);
        return $result;
    }

}