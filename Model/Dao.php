<?php

/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2016
 * @author    : bugbear
 * @date      : 2016/11/30
 * @time      : 上午10:54
 */
namespace Barge\Model;

use medoo;
use Barge\Model\Cache;

abstract class Dao
{
    protected $db = '';

    protected $cache = null;

    protected $config = [];

    protected $table = '';

    protected $sql = '';

    protected $flag = '';

    protected $result = '';

    protected $debug = false;



    public function __construct($config, $cache = null)
    {
        $this->config = $config;
        $this->db = new medoo($config);
        $this->cache = Cache::getCache($cache);
    }

    public function cache()
    {
        $this->cache->register($this->table, $this->flag);
        $key = $this->cacheKey();
        $this->cache->set($key, $this->result);
        return $this;
    }

    public function cacheKey () {
        $str =  json_encode($this->config) . strtolower($this->sql) . $this->flag;
        return md5(str);
    }

    public function register($value) {
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
        if($this->debug) $this->db = $this->db->debug();
        $this->before();

        $this->result = $this->db->update($this->table, $data, $where);
        $this->after();
        $this->sql = $this->db->last_query();
    }

    public function insert($data)
    {
        if($this->debug) $this->db = $this->db->debug();
        $this->before();
        $this->result = $this->db->insert($this->table, $data);
        $this->after();
        $this->sql = $this->db->last_query();

        return $this->result;
    }

    public function delete($where)
    {
        if($this->debug) $this->db = $this->db->debug();
        $this->before();
        $this->result = $this->db->delete($this->table, $where);
        $this->sql = $this->db->last_query();
        $this->after();

        return $this->result;
    }

    public function findOne($column, $where) {
        if($this->debug) $this->db->debug();
        $this->before();
        if($this->enableCache) {
            $data = $this->cache->get();
        }
        $this->result = $this->db->get($column, $where);
        $this->sql = $this->db->last_query();
        $this->register($this->sql);
        $this->after();

        return $this->result;
    }

    public function findByIndex($index, $value, $column = '*') {
        return $this->findOne([$index => $value], $column);
    }


    public function findById($id, $column = '*') {
        return $this->findOne(['id' => $id], $column);
    }

    public function find($columns = '*', $where = '') {
        if($this->debug) $this->db->debug();
        $this->before();
        $this->result = $this->db->select($this->table, $columns, $where);
        $this->sql = $this->db->last_query();
        $this->after();

        return $this->result;
    }

    public function before()
    {

    }

    public function after()
    {

    }
}