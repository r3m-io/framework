<?php
/**
 * @author          Remco van der Velde
 * @since           2023-02-03
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_data_index(Parse $parse, Data $data, $attribute=null){
    if(
        is_string($attribute) &&
        substr($attribute, 0, 1) === '$'
    ){
        $attribute = substr($attribute, 1);
    }
    $data->index($attribute);
}
