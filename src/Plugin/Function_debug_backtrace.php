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

function function_debug_backtrace(Parse $parse, Data $data, $options=true){
    $trace = debug_backtrace($options);
    foreach($trace as $nr => $record){
        unset($record['object']);
    }
    return $trace;
}
