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
use R3m\Io\Module\Cli;

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
    function trace($length=null): void
    {
        $trace = debug_backtrace(1);
        if(!is_numeric($length)){
            $length = count($trace);
        }
        if(!defined('IS_CLI')){
            echo '<pre class="priya-trace">';
        }
        // don't need the first one (0)
        // we do, where did we put it...

        echo Cli::debug('Trace') . PHP_EOL . PHP_EOL;

        for($i = 0; $i < $length; $i++){
            if(array_key_exists($i, $trace)){
                if(
                    array_key_exists('file', $trace[$i]) &&
                    array_key_exists('line', $trace[$i]) &&
                    array_key_exists('function', $trace[$i])
                ){
                    echo cli::notice($trace[$i]['function']) . ':' . $trace[$i]['file'] .':' . $trace[$i]['line']  . PHP_EOL;
                }
                elseif(
                    array_key_exists('file', $trace[$i]) &&
                    array_key_exists('line', $trace[$i])
                ) {
                    echo $trace[$i]['file'] . ':' . $trace[$i]['line'] . PHP_EOL;
                }
            }
        }
        if(!defined('IS_CLI')){
            echo '</pre>' . PHP_EOL;
        }
    }
}
