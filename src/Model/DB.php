<?php
namespace Courser\Model;

use Courser\Model\Parse;

class DB
{

    protected $config = [];

    protected $parser = null;

    protected $sql = [];

    protected $table = '';

    protected $fields = [];

    private $linkr = null;

    private $linkw = null;

    private $links = [];

    private $values = [];


    public function __construct()
    {
        $this->parser = new Parse();
    }

    public function add($config, $type = 'single')
    {
        $type = strtolower($type);
        if ($type === 'master') {
            $this->config['master'] = $config;
        } else if ($type === 'slave') {
            $this->config['slave'] = $config;
        } else {
            $this->config['single'] = $config;
        }
    }

    public function connect()
    {
        try {
            foreach ($this->config as $type => $config) {
                $connection = $this->getDB($config);
                $dsn = $this->dsn($connection);
                if (!empty($this->links[$dsn])) {
                    $this->linkw = $this->links[$dsn]['linkw'];
                    $this->linkr = $this->links[$dsn]['linkr'];
                    continue;
                }

                $link[$dsn] = new \PDO(
                    $dsn,
                    $connection['username'],
                    $connection['password'],
                    $connection['options']
                );
                if ($type === 'master') {
                    $this->linkw = $link[$dsn];
                } else if ($type === 'slave') {
                    $this->linkr = $link[$dsn];
                } else {
                    $this->linkr = $this->linkw = $link[$dsn];
                }
                $this->links[$dsn]['linkw'] = $this->linkw;
                $this->links[$dsn]['linkr'] = $this->linkr;
            }
        } catch (\Exception $err) {
            throw new \Error('DB connect error,' . $err->getMessage());
        }
    }

    public function getDB($connection)
    {
        if (!is_array($connection)) {
            throw new \Error('DB connection must be array');
        }
        $len = count($connection);
        $index = mt_rand(0, $len - 1);
        return $connection[$index];
    }

    private function dsn($config)
    {
        $dsn = 'mysql:';
        if (is_array($config)) {

        }
        if (isset($config) && $config['socket']) {
            $dsn .= 'unix_socket=' . $config['socket'];
        } else {
            $dsn .= 'host=' . $config['host'] . ';port=' . $config['port'];
        }
        $dsn .= ';dbname=' . $config['db'];

        return $dsn;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function field($field)
    {
        $this->fields = $this->wrapField($field);
        return $this;
    }

    public function where($condition)
    {
        list($sql, $values) = $this->parser->build($condition);
        $this->sql[] = $sql;
        $this->values[] = $values;
        return $this;
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
        return $this;

    }

    public function limit($limit)
    {
        $this->sql[] = 'LIMIT ' . $limit;
        return $this;
    }


    public function group($field)
    {
        $this->sql[] = 'GROUP BY `' . $field . '`';
        return $this;
    }

    public function select()
    {
        $sql = 'SELECT %s FROM `%s` %s';
        $sql = printf($sql, $this->fields, $this->table, $this->sql);
        $this->sql = $sql;
        return $this->linkr->query($sql, $this->values);
    }

    public function delete()
    {
        $sql = 'DELETE FROM `%s` %s';
        $sql = printf($sql, $this->table, $this->sql);
        $this->sql = $sql;
        return $this->linkw->query($sql, $this->values);
    }

    public function update($data)
    {
        $set = '';
        foreach ($data as $field => $value) {
            if (is_string($value)) {
                $set .= '`' . $field . '`=?';
                $this->values[] = $value;
                continue;
            }
            if (is_array($value)) {
                if (isset($value['$increment'])) {
                    $set .= '`' . $field . '`=' . $field . '+' . $value;
                } else {
                    $set .= '`' . $field . '`=?' . json_encode($value);
                    $this->values[] = $value;
                }
            }
        }
        $sql = 'UPDATE `%s` SET %s %s';
        $sql = printf($sql, $this->fields, $this->table, $this->sql);
        $this->sql = $sql;

        return $this->linkw->query($sql, $this->values);
    }

    public function insert($data) {
        $fields = [];
        $values = [];
        foreach ($data as $field => $value) {
            $field[] = '`'. $field .'`';
            $values[] = $value;
        }
        $fields = implode(',', $fields);
        $values = implode(',', $values);
        $sql = 'INSERT INTO `%s`(%s)VALUE(%s)';
        $this->sql = printf($sql, $this->table, $fields, $values);

        return $this->linkw->query($sql, $this->values);
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