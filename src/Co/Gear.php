<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/19
 * @time      : 下午3:48
 */

namespace Barge\Co;


class Gear
{

    public $callback = null;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke($croutine) {
        $callback = $this->callback;
        return $callback($croutine);
    }
}