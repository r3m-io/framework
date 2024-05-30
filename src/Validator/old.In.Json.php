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

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

/**
 * @throws ObjectException
 * @throws FileWriteException
 * @throws Exception
 */
function validate_in_json(App $object, $request=null, $field='', $argument='', $function=false): bool
{
    $url = $argument->url ?? false;
    $list = $argument->list ?? false;
    $attribute = $argument->attribute ?? 'name';
    $ignore_case = $argument->ignore_case ?? false;
    if($url === false) {
        return false;
    }
    if(is_array($request)){
        $data = $object->parse_read($url, sha1($url));
        if($data){
            $result = [];
            if($list === false) {
                $result[] = $data->get($attribute);
            }
            foreach($data->data($list) as $nr => $record) {
                if (is_object($record)){
                    $node = new Data($record);
                    if(is_array($attribute)){
                        foreach($attribute as $attr){
                            if ($ignore_case) {
                                $result[] = strtolower($node->get($attr));
                            } else {
                                $result[] = $node->get($attr);
                            }
                        }
                    } else {
                        if ($ignore_case) {
                            $result[] = strtolower($node->get($attribute));
                        } else {
                            $result[] = $node->get($attribute);
                        }
                    }
                } elseif(is_scalar($record)) {
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
    }
    elseif(is_scalar($request)) {
        $data = $object->parse_read($url, sha1($url));
        if($data){
            $result = [];
            if($list === false) {
                if(is_array($attribute)){
                    $value = [];
                    foreach ($attribute as $attr){
                        $value[] = $data->get($attr);
                    }
                    $result[] = implode('', $value);
                } elseif(is_scalar($attribute)){
                    $result[] = $data->get($attribute);
                }

            } else {
                foreach($data->data($list) as $nr => $record) {
                    if (is_object($record)){
                        $node = new Data($record);
                        if(is_array($attribute)){
                            $value = [];
                             foreach($attribute as $attr){
                                if ($ignore_case) {
                                    $value[] = strtolower($node->get($attr));
                                } else {
                                    $value[] = $node->get($attr);
                                }
                             }
                             $result[] = implode('', $value);
                        } else {
                            if ($ignore_case) {
                                $result[] = strtolower($node->get($attribute));
                            } else {
                                $result[] = $node->get($attribute);
                            }
                        }
                    }
                    elseif(is_scalar($record)) {
                        if ($ignore_case) {
                            $result[] = strtolower($record);
                        } else {
                            $result[] = $record;
                        }
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
    }
    return false;
}