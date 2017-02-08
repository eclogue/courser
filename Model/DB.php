<?php
namespace Barge\Model;

use Barge\Model\Parse;

class DB
{

    protected $config = [];
    protected $parser = null;

    protected $sql = [];

    protected $table = '';

    protected $fields = [];


    public function __construct($config)
    {
        try {
            $this->config = $config;
            $this->parser = new Parse();
            $dsn = $this->dsn($config);
            $this->db = new \PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (\Exception $err) {
            throw new \Exception($err->getMessage());
        }
    }

    private function dsn($config)
    {
        $dsn = 'mysql:';
        if(isset($config) && $config['socket']) {
            $dsn .= 'unix_socket=' . $config->socket;
        } else {
            $dsn .= 'host=' . $config['host'] . ';port=' . $config['port'];
        }
        $dsn .= ';dbname=' . $config['db'];

        return $dsn;
    }

    public function table($table)
    {
        $this->table = $table;
    }

    public function field($field)
    {
        $this->fields = $this->wrapField($field);
    }

    public function where($condition)
    {
        $this->sql[] = 'WHERE ' . $this->parser->build($condition);
    }

    public function order($orderBy)
    {
        foreach ($orderBy as $field => $sort) {
            $this->sql[] = 'ORDER BY `' . $field . '` ' . $sort;
        }
    }

    public function skip($offset)
    {
        $this->sql[] = 'OFFSET ' . $offset;
    }

    public function limit($limit)
    {
        $this->sql[] = 'LIMIT ' . $limit;
    }


    public function group($field)
    {
        $this->sql[] = 'GROUP BY `' . $field . '`';
    }

    public function select()
    {
        $sql = 'SELECT %s FROM `%s` %s';
        $sql = printf($sql, $this->fields, $this->table, $this->sql);
        return $this->db->query($sql); // @fixme
    }

    public function wrapField($fields)
    {
        $handled = [];
        foreach ($fields as $field) {
            if (!$field !== '*') {
                $handled[] = '`' . $field . '`';
            } else {
                $handled[] = $field;
            }
        }
        return rtrim(implode(',', $handled), ',');
    }


    public function beforeQuery()
    {

    }

    public function debug()
    {

    }

}