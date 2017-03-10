<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/3/10
 * @time      : 下午1:19
 */

namespace Courser\Interfaces;


interface StoreInterface
{

    public function get($key);

    public function set($key, $value);

    public function save();

    public function setId($id);
}