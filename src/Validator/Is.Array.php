<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\App;

function validate_is_array(App $object, $array=null, $field='', $argument='', $function=false): bool
{
    if(is_array($argument)){
        foreach($argument as $nr => $record){
            if(
                $record === null &&
                $array === null
            ){
                return true;
            }
            elseif(is_bool($record)){
                if($record === true){
                    if($array === ''){
                        return false;
                    }
                    return is_array($array);
                } else {
                    if($array === ''){
                        return true;
                    }
                    return !is_array($array);
                }
            }
        }
        return false;
    }
    if(is_bool($argument)){
        if($argument === true){
            if($array === ''){
                return false;
            }
            return is_array($array);
        } else {
            if($array === ''){
                return true;
            }
            return !is_array($array);
        }
    } else {
        return is_array($array);
    }
}
