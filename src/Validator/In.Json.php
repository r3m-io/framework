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

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

/**
 * @throws ObjectException
 * @throws FileWriteException
 */
function validate_in_json(App $object, $request=null, $field='', $argument='', $function=false): bool
{
    d($request);
    d($field);
    d($argument);
    d($function);
    if(is_array($request)){
        $url = $argument->url ?? false;
        $list = $argument->list ?? false;
        $attribute = $argument->attribute ?? 'name';
        $ignore_case = $argument->ignore_case ?? false;
        if($url){
            $data = $object->parse_read($url, sha1($url));
            if($data){
                $result = [];
                foreach($data->data($list) as $nr => $record) {
                    if(is_object($record) && property_exists($record, $attribute)) {
                        if($ignore_case){
                            $result[] = strtolower($record->{$attribute});
                        } else {
                            $result[] = $record->{$attribute};
                        }
                    } else {
                        if($ignore_case){
                            $result[] = strtolower($record);
                        } else {
                            $result[] = $record;
                        }
                    }
                }
                foreach($request as $post){
                    if($ignore_case){
                        $post = strtolower($post);
                    }
                    if(!in_array($post, $result, true)) {
                        return false;
                    }
                }
                return true;
            }
            return false;
        }
    }
    elseif(is_scalar($request)) {
        $url = $argument->url ?? false;
        $list = $argument->list ?? false;
        $attribute = $argument->attribute ?? 'name';
        $ignore_case = $argument->ignore_case ?? false;

        if($url){
            $data = $object->parse_read($url, sha1($url));
            if($data){
                $result = [];
                foreach($data->data($list) as $nr => $record) {
                    if (
                        is_object($record) &&
                        property_exists($record, $attribute)) {
                        if ($ignore_case) {
                            $result[] = strtolower($record->{$attribute});
                        } else {
                            $result[] = $record->{$attribute};
                        }
                    } else {
                        if ($ignore_case) {
                            $result[] = strtolower($record);
                        } else {
                            $result[] = $record;
                        }
                    }
                }
                if($ignore_case){
                    $string = strtolower($request);
                } else {
                    $string = $request;
                }
                if(!in_array($string, $result, true)) {
                    return false;
                }
                return true;
            }
            return false;
        }
    }
    return false;
}
