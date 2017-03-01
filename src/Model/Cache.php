<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2016
 * @author    : bugbear
 * @date      : 2016/12/2
 * @time      : 下午3:49
 */

namespace Courser\Model;

use Courser\Set\Config;
use Courser\Model\Redis;

class Cache
{

    private $_cache = null;

    private $_config = null;

    protected $flag = '';

    public $prefix = 'init_cache';

    public $enable = false;

    public $length = 50;

    public $expire = 600;

    public $registryExpire = 6000;

    public function __construct($config)
    {

        $this->_config = $config;
        $this->_cache = Redis::getInstance($config);
    }


    public static function getCache($config)
    {
        $cache = Config::get('init.cache.redis');
        if ($cache) return $cache;
        $config = new static($config);
        Config::set($config);
        return $config;
    }

    public function get($key, $immediate = true)
    {
        if (!$this->enable) return null;
        $key = $this->getKey($key);
        $data = $this->_cache->hGetAll($key);
        if (!$data || !isset($data['value']) || !isset($data['change'])) return null;
        if ($immediate && $data['changed']) return null;
        return $data['value'];
    }

    public function set($key, $value, $expire = 0)
    {
        if (!$this->enable) return null;
        $expire = $expire ?: $this->expire;
        $data = ['value' => $value, 'changed' => 0];
        $this->_cache->hMSet($key, $data);
        $this->_cache->expire($key, $expire);
        return true;
    }


    public function flag($table, $unique)
    {
        $this->flag = $table . $unique;
        return $this;
    }

    public function register($string, $table)
    {
        $string = $this->sort($string);
        $setKey = $this->prefix . $table;
        $list = $this->_cache->lRange($setKey, 0, -1);

        if (!$list || in_array($string, $list)) return true;
        $length = count($list);
        if ($length >= $this->length) {
            $this->_cache->rPop();
        }
        $this->_cache->lPush($setKey, $string);
        if (!$length) {
            $this->_cache->expire($setKey, $this->registryExpire);
        }

        return true;
    }

    public function update($force = true)
    {
        $data = $this->_cache->lRange($this->flag);
        if (!$data) return false;
        foreach ($data as $key) {
            if ($force) {
                $this->_cache->del($key);
            } else {
                $this->_cache->hSetNx($key, 'changed', 1);
            }
        }

        return true;
    }

    public function enable($enable)
    {
        $this->enable = boolval($enable);
    }

    public function sort($param)
    {
        if (is_array($param)) $param = implode(sort($param));
        $string = strtolower($param);

        return $string;
    }

    public function getKey($key)
    {
        return md5($this->prefix . $key . $this->flag);
    }

    public function flush()
    {
        $list = $this->_cache->lRange($this->flag, 0, -1);
        if(!count($list)) return true;
        foreach ($list as $key) {
            $this->_cache->del($key);
        }

        return true;
    }

    public function cleanRegistry()
    {

    }

}