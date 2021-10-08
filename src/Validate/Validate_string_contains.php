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
use R3m\Io\Module\Parse\Token;

function validate_string_contains(R3m\Io\App $object, $field='', $argument=''){
    $string = $object->request('node.' . $field);
    $argument = $object->request('node.' . $argument);
    if(stristr($string, $argument) !== false){
        return true;
    }
    return false;
}
