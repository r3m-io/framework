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
use stdClass;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Host;


function function_host_url(Parse $parse, Data $data){
    $object = $parse->object();
    ddd($object>config('host'));
    $url =  $object->config('host.url.' . $object->config('framework.environment'));
    if(substr($url,-1, 1) != '/'){
        $url .= '/';
    }
    return $url;
}
