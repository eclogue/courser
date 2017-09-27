<?php
/**
 * @license   MIT
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午1:04
 */
namespace Courser\Incoming;


abstract class RequestAbstract
{
    public $server = [];

    public $cookie = [];

    public $files = [];

    public $headers = [];



}