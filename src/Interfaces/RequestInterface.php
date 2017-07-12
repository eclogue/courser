<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午1:04
 */
namespace Courser\Interfaces;

use Psr\Http\Message\RequestInterface as ReqInterface;


interface RequestInterface extends ReqInterface
{
    /*
     * add param name
     * @param string $name
     * @return void
     * */
    public function addParamName($name);

    /*
     * set param
     * @param string $key
     * @param string $val
     * */
    public function setParam($key, $val);


    /*
     * get cookie by key
     * @param string $key
     * */
    public function cookie($key);

    /*
     * get request body by param name
     *
     * @param string $key param name
     * */
    public function body($key);

    /*
     * get url query param by name
     * @param string $key
     * */
    public function query($key);

}