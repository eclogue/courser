<?php
/**
 * @license   https://github.com/Init/licese.md
 * @copyright Copyright (c) 2017
 * @author    : bugbear
 * @date      : 2017/2/20
 * @time      : 上午10:15
 */

namespace Courser\Co;


class Compose
{
    /*
     * @var object SplQueue
     * */
    protected $queue;

    /*
     * @var boolean
     * */
    protected $first = true;

    /*
     * $var mixed
     * */
    private $value = null;


    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    /*
     * push a generator enqueue
     * @param object $co instance of generator
     * @return void
     * */
    public function push($co)
    {
        $this->queue->enqueue($co);
    }

    /*
     * execute composer of generator
     *
     * */
    public function run()
    {
        while (!$this->queue->isEmpty()) {
            $co = $this->queue->dequeue();
            if (!$co instanceof \Generator) {
                $this->value = $co;
                continue;
            }
            if($this->first) {
                $this->first = false;
                $this->value = $co->current();
                $this->push($co);
                continue;
            }
            if ($this->value) {
                $co->send($this->value);
            } else {
                $co->next();
            }
            $this->value = $co->current();
            if ($co->valid()) {
                $this->push($co);
                continue;
            }
        }
    }

}