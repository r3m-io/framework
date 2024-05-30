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
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

/**
 * @throws \R3m\Io\Exception\ObjectException
 * @throws \R3m\Io\Exception\FileWriteException
 */
function function_json_select(Parse $parse, Data $data, $url, $select=null, $compile=false, $cache=true){
    $object = $parse->object();
    $object->logger($object->config('project.log.debug'))->notice('Deprecated function json.select triggered. replace with data.select or parse.select');
    //@2024-10-01 can be removed after the documentation of data.select & parse.select are updated based on json.select.
    if($compile){
        if($cache){
            $json = $object->parse_read($url, sha1($url));
        } else {
            $json = $object->parse_read($url);
        }
    } else {
        if($cache){
            $json = $object->data_read($url, sha1($url));
        } else {
            $json = $object->data_read($url);
        }
    }
    if($json){
        return $json->data($select);
    }
    return null;
}
