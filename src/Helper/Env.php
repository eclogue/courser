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
    public static function getEnv()
    {
        $argument = getopt('e::h::v::', ['env::', 'help::', 'version::']);
        foreach ($argument as $opt => $value) {
            if ($opt === 'h' || $opt === 'help') {
                $help = ' -e,  --env       set env variable' . PHP_EOL;
                $help .= '-v, --version    show version' . PHP_EOL;
                echo $help;
            } else if ($opt === 'v' || $opt === 'version') {
                echo "Version: alpha" . PHP_EOL;
            } else if ($opt === 'e' || $opt === 'env') {
                $env = 'env=' . $value;
                putenv($env);
            }
        }
    }

    public static function add($setting)
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

    public static function remove($name) {
        putenv($name);
    }
}