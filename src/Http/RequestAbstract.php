<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午12:58
 */

namespace Courser\Http;

use Courser\Interfaces\RequestInterface;

abstract class RequestAbstract
{
    public $params = [];
    /*
     * @var array
     * */
    public $paramNames = [];

    /*
     * @var array
     * */
    public $methods = [];

    /*
     * @var array
     * */
    public $body = [];

    /*
     * @var array
     * */
    public $header = [];

    /*
     * @var array
     * */
    public $server = [];

    /*
     * @var string
     * */
    public $method = 'get';

    /*
     * @var object
     * */
    public $req;

    /*
     * @var array
     * */
    public $cookie = [];

    /*
    * @var array
    * */
    public $files = [];

    /*
     * @var array
     * */
    private $callable = [];

    /*
     * @var array
     * */
    public $query = [];

    public function setRequest($request)
    {
        $this->req = $request;
    }

}