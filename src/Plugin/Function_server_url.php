<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Config;

/**
 * @throws Exception
 */
function function_server_url(Parse $parse, Data $data, $name=''){
    $object = $parse->object();
    $name = str_replace('.', '-', $name);
    d($name);
    ddd($object->config('server'));
    $url = $object->config('server.url.' . $name . '.' . $object->config('framework.environment'));
    if(
        $url &&
        substr($url, 0, -1) !== '/'
    ){
        $url .= '/';
    }
    return $url;
}
