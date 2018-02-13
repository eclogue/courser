<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2018
 * @author: bugbear
 * @date: 2018/2/2
 * @time: 下午12:39
 */

namespace Courser;

use Psr\Http\Server\MiddlewareInterface;

class Middleware
{

    protected $middleware = [];

    protected $group = '/';


    /**
     * @param MiddlewareInterface $handler
     * @return $this
     */
    public function add(MiddlewareInterface $handler)
    {
        list($regex, $params) = Route::parseRoute($this->group);
        $regex = '#^' . $regex . '(.*?)#';
        $middleware = [
            'regex' => $regex,
            'params' => $params,
            'group' => $this->group,
            'handler' => $handler
        ];
        $this->middleware[] = $middleware;

        return $this;
    }

    public function group($group)
    {
        $this->group = $group;
    }

    public function match($path, $deep)
    {
        if (empty($this->middleware)) {
            return [];
        }


        $md = [];
        foreach ($this->middleware as $index => $middleware) {
            if ($index > $deep) {
                break;
            }

            preg_match($middleware['regex'], $path, $match);
            if (empty($match)) {
                continue;
            }

            $md[] = $middleware['handler'];
        }

        return $md;
    }
    public function count()
    {
        return count($this->middleware);
    }
}