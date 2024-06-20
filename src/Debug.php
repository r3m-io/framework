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

use JetBrains\PhpStorm\NoReturn;

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
    #[NoReturn]
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
    #[NoReturn]
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

if(!function_exists('trace')){
    function trace($length=5): void
    {
        $trace = debug_backtrace(1);
        if(!is_numeric($length)){
            $length = count($trace);
        }
        if(!defined('IS_CLI')){
            echo '<pre class="priya-trace">';
        }
        for($i = 0; $i < $length; $i++){
            if(array_key_exists($i, $trace)){
                if(
                    array_key_exists('file', $trace[$i]) &&
                    array_key_exists('line', $trace[$i]) &&
                    array_key_exists('function', $trace[$i])

                ){
                    echo $trace[$i]['file'] . ':' . $trace[$i]['line'] . ':' . $trace[$i]['function']. PHP_EOL;
                }
                elseif(
                    array_key_exists('file', $trace[$i]) &&
                    array_key_exists('line', $trace[$i]) &&
                    array_key_exists('class', $trace[$i])
                ){
                    echo $trace[$i]['file'] . ':' . $trace[$i]['line'] . ':' . $trace[$i]['class']. PHP_EOL;
                } else {
                    echo $trace[$i]['file'] . ':' . $trace[$i]['line'] . PHP_EOL;
                }
            }
        }
        if(!defined('IS_CLI')){
            echo '</pre>' . PHP_EOL;
        }
    }
}
