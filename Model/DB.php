<?php
namespace Barge\Model;

use Barge\Model\Parse;

class DB
{

    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }


    public function where($condition)
    {

    }

    public function parseWhere($where)
    {
        if (is_string($where)) return $where;
    }

    public function find()
    {

    }

    public function findOne()
    {

    }

    public function findById()
    {

    }

    public function findByIds()
    {

    }


    public function count()
    {

    }

    public function update()
    {

    }

    public function insert()
    {

    }

    public function multiInsert()
    {

    }

    public function query()
    {

    }

    public function execute()
    {

    }

    public function beforeQuery()
    {

    }

}