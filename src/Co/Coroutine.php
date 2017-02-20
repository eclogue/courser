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
    protected $taskId;
    protected $coroutine;
    protected $first = true;
    protected $sendValue = null;

    public function __construct($taskId, \Generator $coroutine) {
        $this->taskId = $taskId;
        $this->coroutine = $coroutine;
    }


    public function run() {
        if ($this->first) {
            $this->first = false;
            return $this->coroutine->current();
        } else {
            $retval = $this->coroutine->next();
            $this->sendValue = null;
            return $retval;
        }
    }

    public function getTaskId() {
        return $this->taskId;
    }

    public function setSendValue($sendValue) {
        $this->sendValue = $sendValue;
    }

    public function isFinished() {
        return !$this->coroutine->valid();
    }
}