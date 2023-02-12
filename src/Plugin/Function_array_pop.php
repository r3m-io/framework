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
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_array_pop(Parse $parse, Data $data, $selector=''){
    if(substr($selector, 0, 1) == '$'){
        $selector = substr($selector, 1);
    }
    $array = $data->data($selector);
    $result = false;
    if($array && is_array($array)){
        $result = array_pop($array);
        $data->data($selector, $array);
    } else {
        d($array);
        ddd($selector);
    }
    return $result;
}
