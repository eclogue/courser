<?php

namespace Barge\Model;

class Parse
{

    public static $logical = [
        '$and' => ' and ',
        '$or' => ' or ',
        '$not' => ' not ',
//        '$xor',
    ];

    private static $operator = [
        '$eq' => '=',
        '$neq' => '!=',
        '$gt' => '>',
        '$lt' => '<',
        '$gte' => '>=',
        '$lte' => '<=',
        '$like' => 'LIKE',
        '$isNull' => 'IS NULL',
        '$isNotNull' => 'IS NOT NULL',
        '$in' => 'IN(%s)',
    ];

    public $tree = [];

    public $root = 'root';

    public $token = [];

    public $sql = '';


    public function __construct()
    {
        $this->sql = '';
    }

    /*
     * 递归入栈生成节点树
     * @param array $entities
     *
     * */
    public function generateNode($entities, $child = false)
    {
        echo "-------------->\n";
        var_dump($entities);
        echo "<--------------\n";

        foreach ($entities as $key => $value) {
            $node = [];
            $value = !is_array($value) ? ['$eq' => $value] : $value;
            if (!in_array($key, array_keys(self::$logical))) {
                $operator = array_keys($value);
                $operators = array_keys(self::$operator);
                $intersect = array_intersect($operator, $operators);
                if (count($intersect)) {
                    $node['type'] = 'field';
                    $node['name'] = $key;
                    $node['value'] = $value;
                    $length = count($this->tree);
                    $length = $length ? $length - 1 : 0;
                    $prev = $length ? $this->tree[$length] : [];
                    if (isset($prev['type']) && $prev['type'] === 'field') {
                        $this->tree[] = $this->getDefaultNode($child);
                    }
                    $this->tree[] = $node;
                } else if ($this->isIndexArray($value)) {
                    foreach ($value as $item) {
                        $this->generateNode($item, true);
                    }
                }
            } else {
                $node['type'] = 'operator';
                $node['name'] = self::$logical[$key];
                $node['value'] = $child ? 0 : 1;
                $this->tree[] = $node;
                $this->generateNode($value, true);
            }
        }
    }

    public function getDefaultNode($child)
    {
        return [
            'type' => 'operator',
            'name' => ' and ',
            'value' => $child ? 0 : 1,
        ];
    }


    public function isIndexArray($node)
    {
        if (!is_array($node)) return false;
        $keys = array_keys($node);
        return is_numeric($keys[0]);
    }

    public function build()
    {

        foreach ($this->tree as $key => $node) {
            if ($node['type'] === 'field') {
                $this->sql .= $this->parseFieldNode($node);
            } else {
                if ($node['value'] === 1) {
                    $this->sql .= ')';
                }
                $this->sql .= $this->parseLogicalNode($node);
            }
        }
    }

    private function parseFieldNode($node)
    {
        $string = '';
        $filed = '`' . $node['name'] . '`';
        $connector = ' and ';
        foreach ($node['value'] as $operator => $value) {
            $temp = [$filed];
            $temp[] = self::$operator[$operator];
            $temp[] = $value;
            $temp[] = $connector;
            $string .= implode('', $temp);
        }

        return rtrim($string, $connector);
    }

    private function parseLogicalNode($node)
    {
        $string = '';
        $string .= $node['name'];
        if ($node['value'] === 1) {
            $string .= '(';
        }
        return $string;
    }

}


$condition = [
    'id' => ['$gt' => 1, '$lt' => 100, '$neq' => 23],
    '$or' => [
        'email' => ['eq' => 'aaxx@scac.com'],
        'nick' => '1',
    ],
    'age' => ['$lt' => 70]
];


$p = new Parse();
$p->generateNode($condition);
$p->build();

var_dump($p->tree);
var_dump($p->sql);

