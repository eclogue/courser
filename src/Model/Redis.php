<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2016
 * @author    : bugbear
 * @date      : 2016/12/2
 * @time      : 下午4:51
 */

namespace Courser\Model;


class Redis
{


    public static function getInstance($config = null)
    {
        $config = $config ?: ['host' => '127.0.0.1', 'port'=> '6379'];
        $redis = new \Redis();
        if(isset($config['pconnect']) && $config['pconnect']) {
            return $redis->pconnect($config['host'], $config['port']);
        }
        return $redis->connect($config['host'], $config['port']);
    }


}