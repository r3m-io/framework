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
    $filter = $argument->filter ?? false;
    $key = $argument->key ?? false;
    $inverse = $argument->inverse ?? false;
    $type = $argument->type ?? 'auto';
    $data = $argument->data ?? null;

    if($data === null){
        if($url === false) {
            return false;
        }
        if(!File::exist($url)){
            return false;
        }
        $data = $object->parse_read($url, sha1($url));
    }
    if($data){
        if($filter){
            if($key) {
                $data_key = $data->data($key);
                if (
                    $data_key !==null &&
                    !is_scalar($data_key)
                ) {
                    if($type === Filter::TYPE_AUTO){
                        $type = Filter::is_type($data_key);
                        ddd($type);
                    }
                    switch($type){
                        case 'list':
                            $data_filter = Filter::list($data_key)->where($filter);
                            break;
                        case 'record':
                            $data_filter = Filter::record($data_key)->where($filter);
                            break;
                        default:
                            throw new Exception('Type (' . $type . ') not supported in ' . __FUNCTION__ . ', supported types: list, record');
                    }
                    if(!empty($data_filter)){
                        return !$inverse;
                    }
                } else {
                    throw new Exception('Key (' . $key . ') is scalar in ' . __FUNCTION__ . ', expected array, object');
                }
            } else {
                $data_key = $data->data();
                if(
                    $data_key !==null &&
                    !is_scalar($data_key)
                ){
                    switch($type){
                        case 'list':
                            $data_filter = Filter::list($data_key)->where($filter);
                            break;
                        case 'record':
                            $data_filter = Filter::record($data_key)->where($filter);
                            break;
                        default:
                            throw new Exception('Type (' . $type . ') not supported in ' . __FUNCTION__ . ', supported types: list, record');
                    }
                    if(!empty($data_filter)){
                        return !$inverse;
                    }
                } else {
                    throw new Exception('Key (' . $key . ') is scalar in ' . __FUNCTION__ . ', expected array, object');
                }
            }
        }
    }
    return $inverse;
}