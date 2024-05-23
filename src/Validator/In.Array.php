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

function validate_in_array(App $object, $in='', $field='', $array='', $function=false): bool
{
    //dus
    if(is_array($in)){
        foreach($in as $text){
            if(in_array(null, $array, true)){
                if($text === null){
                    return true;
                }
            }
            if(!in_array($text, $array, true)){
                return false;
            }
        }
        return true;
    } else {
        if(in_array(null, $array, true)){
            if($in === null){
                return true;
            }
            return in_array($in, $array, true);
        } else {
            if(empty($in)){
                return false;
            }
            return in_array($in, $array, true);
        }
    }
}
