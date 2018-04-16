<?php

define('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';

use Symfony\Component\Process\Process;


$process = new Process(['php', 'example/swoole.php']);
$process->start();


foreach ($process as $type => $data) {
    if ($process::OUT === $type) {
        echo "\nRead from stdout: ".$data;
    } else { // $process::ERR === $type
        echo "\nRead from stderr: ".$data;
    }
}