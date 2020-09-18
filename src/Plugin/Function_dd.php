<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_dd(Parse $parse, Data $data, $debug=null){
    $trace = debug_backtrace(true);
    ob_start();
    if(!defined('IS_CLI')){
        echo '<pre class="priya-debug">';
    }
    echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;
    ob_flush();
    var_dump($debug);
    $debug = ob_get_contents();
    ob_end_clean();
    $explode = explode(PHP_EOL, $debug);
    array_shift($explode);
    if(defined('IS_CLI')){
        echo implode(PHP_EOL, $explode);
    } else {
        echo implode('<br>' . PHP_EOL, $explode);
        echo '</pre>';
    }
    exit;
}
