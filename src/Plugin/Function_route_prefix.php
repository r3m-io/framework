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

function function_route_prefix(Parse $parse, Data $data, $prefix=null){
    $object = $parse->object();
    if($prefix !== null){
        $object->config('route.prefix', $prefix);
    }
    return $object->config('route.prefix');
}
