<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
if(!function_exists('d')){
    function d($data=null): void
    {
        $trace = debug_backtrace(1);
        if(!defined('IS_CLI')){
            echo '<pre class="priya-debug">' . PHP_EOL;
        }
        echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;
        var_dump($data);
        if(!defined('IS_CLI')){
            echo '</pre>' . PHP_EOL;
        }
    }
}

if(!function_exists('dd')){
    function dd($data=null): void
    {
        $trace = debug_backtrace(1);
        if(!defined('IS_CLI')){
            echo '<pre class="priya-debug">' . PHP_EOL;
        }
        echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;
        var_dump($data);
        if(!defined('IS_CLI')){
            echo '</pre>' . PHP_EOL;
        }
        exit;
    }
}

if(!function_exists('ddd')){
    function ddd($data=null): void
    {
        $trace = debug_backtrace(1);
        if(!defined('IS_CLI')){
            echo '<pre class="priya-debug">';
        }
        echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;
        if(!defined('IS_CLI')){
            echo '</pre>';
        }
        dd($data);
    }
}
