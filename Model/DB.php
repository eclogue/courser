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

    private $linkr = null;

    private $linkw = null;

    private $links = [];


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