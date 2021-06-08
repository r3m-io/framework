<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-31
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function___(Parse $parse, Data $data, $attribute=null){
    $object = $parse->object();
    $language = $object->session('language');
    if($language === null){
        $language = $object->session('language', 'en');
    }
    $test = $object->data('translation');
    if(empty($test)){
        $debug = debug_backtrace(true);
        dd($debug);

        return '{import.translation()} missing...' . PHP_EOL;
    }
    return $object->data('translation.' . $attribute . '.' . $language);
}
