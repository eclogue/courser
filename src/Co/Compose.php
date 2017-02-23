<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */

namespace Barge\Co;
use Barge\Co\Coroutine;
use Barge\Co\Gear;


class Compose {
    protected $queue;

    public function __construct() {
        $this->queue = new \SplQueue();
    }

    public function add(\Generator $coroutine) {
        $co = new Coroutine($coroutine);
        $this->convey($co);
    }

    public function convey(Coroutine $co) {
        $this->queue->enqueue($co);
    }


    public function run() {
        while (!$this->queue->isEmpty()) {
            $task = $this->queue->dequeue();
            $task->run();
            if (!$task->isFinished()) {
                $this->convey($task);
            }
        }
    }

}