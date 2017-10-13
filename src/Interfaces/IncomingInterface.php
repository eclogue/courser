<?php
/**
 * @license https://github.com/racecourse/courser/license.md
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/8/22
 * @time: 下午10:27
 */

namespace Courser\Interfaces;


interface IncomingInterface
{

    public function __call($name, $arguments);

    public function __get($key, $value);
}