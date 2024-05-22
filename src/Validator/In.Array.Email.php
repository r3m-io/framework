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

use R3m\Io\Module\Core;

use R3m\Io\Exception\ObjectException;

/**
 * @throws ObjectException
 */
function validate_in_array_email(App $object, $array=null, $field='', $argument='', $function=false): bool
{
    if(
        is_string($array) &&
        substr($array, 0, 1) === '[' &&
        substr($array, -1, 1) === ']'
    ){
        $array = Core::object($array, Core::OBJECT_ARRAY);
    }
    d($array);
    d($argument);
    if(
        is_array($argument) &&
        in_array(null, $argument, true) &&
        $array === null
    ){
        return true;
    }
    if(is_array($array)){
        foreach($array as $nr => $value){
            if(
                is_array($argument)
            ){
                if(in_array(true, $argument, true)){
                    if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        // invalid address
                        return false;
                    }
                }
                elseif(in_array(false, $argument, true)){
                    if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        // invalid address
                        return false;
                    }
                }
            }
            elseif(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                // invalid address
                return false;
            }
        }
        return true;
    }
    return false;
}
