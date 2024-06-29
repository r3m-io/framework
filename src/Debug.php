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

        echo Cli::debug('Trace') . PHP_EOL . PHP_EOL;

        for($i = 1; $i < $length; $i++){
            if(array_key_exists($i, $trace)){
                if(
                    array_key_exists('file', $trace[$i]) &&
                    array_key_exists('line', $trace[$i]) &&
                    array_key_exists('function', $trace[$i]) &&
                    array_key_exists('class', $trace[$i])

                ){
                    echo Cli::notice($trace[$i]['line']) . ':' . cli::notice($trace[$i]['function']) . ':' .  cli::info($trace[$i]['class']) . ':' . $trace[$i]['file'] . PHP_EOL;
                }
                elseif(
                    array_key_exists('file', $trace[$i]) &&
                    array_key_exists('line', $trace[$i]) &&
                    array_key_exists('function', $trace[$i])
                ){
                    echo Cli::notice($trace[$i]['line']) . ':' . Cli::notice($trace[$i]['function']) . ':' . $trace[$i]['file'] . PHP_EOL;
                }
                elseif(
                    array_key_exists('file', $trace[$i]) &&
                    array_key_exists('line', $trace[$i]) &&
                    array_key_exists('class', $trace[$i])
                ){
                    echo Cli::notice($trace[$i]['line']) . ':' . $trace[$i]['class'] . ':' . $trace[$i]['file'] . PHP_EOL;
                }
                elseif(
                    array_key_exists('file', $trace[$i]) &&
                    array_key_exists('line', $trace[$i])
                ) {
                    echo Cli::notice($trace[$i]['line']) . ':' . $trace[$i]['file'] . PHP_EOL;
                }
            }
        }
        if(!defined('IS_CLI')){
            echo '</pre>' . PHP_EOL;
        }
    }
}
