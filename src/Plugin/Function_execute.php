<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-14
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Core;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_execute(Parse $parse, Data $data, $command='', $notification=''){
    $object = $parse->object();
    $command = (string) $command;
    $command = escapeshellcmd($command);
    $output = false;
    Core::execute($object, $command, $output, $notify);
    d($notify);
    if($notification){
        if(
            is_string($notification) &&
            substr($notification, 0, 1) === '$'
        ){
            $notification = substr($notification, 1);
        }
        $object->data($notification, $notify);
    }
//    exec($command, $output);
    return $output;
}
