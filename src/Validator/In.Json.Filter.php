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

use R3m\Io\Module\Data;
use R3m\Io\Module\Filter;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

/**
 * @throws ObjectException
 * @throws FileWriteException
 * @throws Exception
 */
function validate_in_json_filter(App $object, $request=null, $field='', $argument='', $function=false): bool
{
    $url = $argument->url ?? false;
    $list = $argument->list ?? false;
    $attribute = $argument->attribute ?? 'name';
    $ignore_case = $argument->ignore_case ?? false;
    $filter = $argument->filter ?? false;
    $key = $argument->key ?? false;
    $inverse = $argument->inverse ?? false;
    $type = $argument->type ?? 'record';
    if($url === false) {
        return false;
    }
    $data = $object->parse_read($url, sha1($url));
    $data_key = null;
    if($data){
        if($filter){
            if($key) {
                $data_key = $data->data($key);
                if (!is_scalar($data_key)) {
                    d($inverse);
                    ddd($type);
                    $data_filter = Filter::list($data_key)->where($filter);
                    if(
                        is_array($data_filter) &&
                        empty($data_filter)
                    ){
                        return false;
                    }
                    if($data_filter === false){
                        $data_filter = Filter::record($data_key)->where($filter);
                    }
                    d($data_filter);
                    if($data_filter){
                        return true;
                    }
                }
            } else {
                $data_key = $data->data();
                if(!is_scalar($data_key)){
                    d($inverse);
                    ddd($type);
                    $data_filter = Filter::list($data_key)->where($filter);
                    if(
                        is_array($data_filter) &&
                        empty($data_filter)
                    ){
                        return false;
                    }
                    if($data_filter === false){
                        $data_filter = Filter::record($data_key)->where($filter);
                    }
                    d($data_filter);
                    if($data_filter){
                        return true;
                    }
                }
            }
        }
    }
    return false;
}