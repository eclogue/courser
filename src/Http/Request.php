<?php
/**
 * Created by PhpStorm.
 * User: crab
 * Date: 2015/4/12
 * Time: 15:23
 */
namespace Courser\Http;

use Courser\Http\StdObject;
use Courser\Http\Header;

class Request
{

    public $params = [];

    public $paramNames = [];

    public $methods = [];

    public $body = null;

    public $header = [];

    public $server = [];

    public $method = '';

    public $req = '';


    public function setRequest($req)
    {
        $this->req = $req;
        $this->method = $req->server['request_method'];
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
     * get request header by field name
     *
     * @param string $name
     * */
    public function header($name)
    {
        return $this->req->header($name) ?: null;
    }


    public function cookie()
    {

    }

    /*
     * get request body by param name
     *
     * @param string $key param name
     * @return string || null
     * */
    public function body($key)
    {
        if($this->header('content-type') === 'application/x-www-form-urlencoded') {
            return $this->req->post($key);
        } else {
            if($this->body === null) {
                if (function_exists('mb_parse_str'))
                    mb_parse_str(file_get_contents('php://input'), $this->body);
                else
                    parse_str(file_get_contents('php://input'), $this->body);
            } else {
                return isset($this->body[$key]) ? $this->body[$key] : null;
            }
        }
    }

    public function query($key)
    {
        return $this->req->get($key) ?: null;
    }


    public function __invoke($request)
    {
        return $this;
    }

}