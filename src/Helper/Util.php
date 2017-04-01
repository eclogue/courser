<?php
/**
 * @license
 * @copyright Copyright (c) 2016
 * @author    : bugbear
 * @date      : 2016/11/29
 * @time      : 下午5:26
 */

namespace Courser\Helper;


class Util
{
    public static function isIndexArray($array)
    {
        if (!is_array($array)) return false;
        $keys = array_keys($array);
        return isset($keys[0]) && is_numeric($keys[0]);
    }
}