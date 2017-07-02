<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午12:58
 */

namespace Courser\Http;
use Courser\Interfaces\ResponseInterface;

abstract class ResponseAbstract implements ResponseInterface
{
    public $response = '';

    /*
     * @var array
     * */
    public $headers = [];

    /*
     * @var integer
     * */
    public $statusCode = 200;

    /*
     * @var header
     * */
    private $header;

    /*
     * store the \Swoole\Http\Response instance
     * */
    public $res;

    /*
     * send body
     * */
    public $body;


}