<?php

/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/19
 * @time      : 上午12:09
 */
namespace Barge\Co;

class Coroutine {

    protected $coroutine;

    protected $first = true;

    protected $value = null;

    public function __construct(\Generator $coroutine) {
        $this->coroutine = $coroutine;
    }


    public function run() {
        if ($this->first) {
            $this->first = false;
            return $this->Current();
        } else {
            return $this->coroutine->send($this->value);
        }
    }

    public function Current() {
        $this->value = $this->coroutine->current();
        return$this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function isFinished() {
        return !$this->coroutine->valid();
    }
}