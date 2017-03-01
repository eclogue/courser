<?php
/**
 * Created by PhpStorm.
 * User: bugbear
 * Date: 2016/11/28
 * Time: 下午9:28
 */

namespace Courser\Router;


class RouterGroup
{

    public $grep = [];

    public $groups = [];

    public $container = [];

    public function __construct($group, $callable)
    {
        $this->groups[$group] = $callable;
    }

    public function setContainer($container) {
        $this->setContainer = $container;
    }



}