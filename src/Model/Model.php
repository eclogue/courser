<?php

/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2016
 * @author    : bugbear
 * @date      : 2016/11/30
 * @time      : 上午10:54
 */
namespace Courser\Model;

use Courser\Model\DB;
use Courser\Model\Cache;

abstract class Model
{
    protected $db = '';

    protected $cache = null;

    protected $config = [];

    protected $table = '';

    protected $sql = '';

    protected $flag = '';

    protected $result = '';

    protected $debug = false;

    public $attr = [];

    public $fields = [];


    public function __construct($config, $cache = null)
    {
        $this->config = $config;
        $this->db = new DB();
        $this->db->add($config);
        $this->cache = Cache::getCache($cache);
    }

    public function cache()
    {
        $this->cache->register($this->table, $this->flag);
        $key = $this->cacheKey();
        $this->cache->set($key, $this->result);
        return $this;
    }

    public function cacheKey()
    {
        $str = json_encode($this->config) . strtolower($this->sql) . $this->flag;
        return md5($str);
    }

    public function register($value)
    {
        $key = $this->table . '#' . $this->flag;
        $this->cache->set($key, $value);
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function update($data, $where)
    {
        if ($this->debug) $this->db->debug();
        $this->before();

    }

    public function insert($data)
    {
        if ($this->debug) $this->db = $this->db->debug();
        $this->before();
        $this->result = $this->db->insert($this->table, $data);
        $this->after();
        $this->sql = $this->db->last_query();

        return $this->result;
    }

    public function delete($where)
    {
        $this->result = $this->db->delete($this->table, $where);
        $this->sql = $this->db->last_query();
        $this->after();

        return $this->result;
    }

    public function findOne($where)
    {
        if ($this->debug) $this->db->debug();
        $this->result = $this->db->where($where)->select();
//        $this->register($this->sql);
//        $this->after();

        return $this->result;
    }

    public function findByIndex($index, $value)
    {
        return $this->findOne([$index => $value]);
    }


    public function findById($id)
    {
        return $this->findOne(['id' => $id]);
    }

    public function find($where, $options)
    {
        $this->result = $this->db->where($where)->select();

        return $this->result;
    }

    public function build()
    {
        return $this->db;
    }

    public function before()
    {

    }

    public function after()
    {

    }


    public function __call()
    {

    }

    public function __get()
    {

    }

    public function __set($key, $value)
    {
        $this->attr[$key] = $value;
    }

    protected function checkFields($fields)
    {
        if (is_string($fields)) return isset($this->fields[$fields]);
        foreach ($fields as $field => $value) {
            if (!isset($this->fields[$field])) return false;
        }

        return true;
    }
}