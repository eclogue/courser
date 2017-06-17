<?php
/**
 * @license https://github.com/racecourse/courser/licese.md
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/6/17
 * @time: 下午3:16
 */

namespace Courser\Helper;


class Env
{
    public function __construct($env = 'develop')
    {
        putenv('env=' . $env);
    }

    public function add($setting)
    {
        if (is_array($setting)) {
            foreach ($setting as $key => $value) {
                $env = $key . '=' . $value;
                putenv($env);
            }
        } else {
            putenv($setting);
        }
    }

    public function remove($name) {
        putenv($name);
    }
}