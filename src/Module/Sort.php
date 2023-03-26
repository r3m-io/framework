<?php
/**
 * @author          Remco van der Velde
 * @since           18-12-2020
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use stdClass;
use Exception;
use R3m\Io\App;
use R3m\Io\Config;

class Sort extends Data{

    public static function list($list): Sort
    {
        return new Sort($list);
    }

    public function with($sort=[], $key_reset=false, $key=false){
        $list = $this->data();
        if(
            is_array($list) || 
            is_object($list)
        ){
            $result = [];  
            $no_attribute = [];
            $count = count($sort);
            if($count == 1){
                if(
                    is_object($list) &&
                    Core::object_is_empty($list)){
                    return [];
                }
                $attribute = false;
                $sortable_1 = 'ASC';
                foreach($list as $uuid => $node){
                    foreach($sort as $attribute => $record){
                        $value = $this->data($uuid . '.' . $attribute);
                        d($uuid);
                        d($attribute);
                        d($this->data());
                        ddd($value);
                    }





                    if($key){
                        if(is_array($node)){
                            if(array_key_exists($key, $node)){
                                $select = $node[$key];
                            }
                        } else {
                            if(property_exists($node, $key)){
                                $select = $node->$key;
                            }
                        }
                    } else {
                        $select = $node;
                    }
                    if(is_array($select)){
                        foreach($sort as $attribute => $record){
                            if(array_key_exists($attribute, $select)){
                                if(is_scalar($select[$attribute])){
                                    $result[$select[$attribute]][] = $node;
                                } else if (is_array($node[$attribute])){
                                    $attr = '';
                                    foreach($select[$attribute] as $node_attribute){
                                        if(is_scalar($node_attribute)){
                                            $attr .= '.' . $node_attribute;
                                        }
                                    }
                                    $attr = substr($attr, 1);
                                    $result[$attr][] = $node;
                                }
                            } else {
                                $result[''][] = $node;
                            }
                            $sortable_1 = $record;
                            break;
                        }
                    } else {
                        foreach($sort as $attribute => $record){
                            if(property_exists($select, $attribute)){
                                if(is_scalar($select->$attribute)){
                                    $result[$select->$attribute][] = $node;
                                } else if (is_array($select->$attribute)){
                                    $attr = '';
                                    foreach($select->$attribute as $node_attribute){
                                        if(is_scalar($node_attribute)){
                                            $attr .= '.' . $node_attribute;
                                        }
                                    }
                                    $attr = substr($attr, 1);
                                    $result[$attr][] = $node;
                                }
                            } else {
                                $result[''][] = $node;
                            }
                            $sortable_1 = $record;
                            break;
                        }
                    }
                }
                unset($sort[$attribute]);                
                if(strtolower($sortable_1) == 'asc'){
                    ksort($result, SORT_NATURAL);
                } else {
                    krsort($result, SORT_NATURAL);
                }
                $list = [];                
                foreach($result as $attribute => $subList){
                    foreach($subList as $nr => $record){
                        if(is_array($record)){
                            if(array_key_exists('uuid', $record)){
                                $list[$record['uuid']] = $record;
                            } else {
                                while(true){
                                    $uuid = Core::uuid();
                                    if(!array_key_exists($uuid, $list)){
                                        $record['uuid'] = $uuid;
                                        break;
                                    }
                                }
                                $list[$uuid] = $record;
                            }
                        } else {
                            if(property_exists($record, 'uuid')){
                                $list[$record->uuid] = $record;
                            } else {
                                while(true){
                                    $uuid = Core::uuid();
                                    if(!array_key_exists($uuid, $list)){
                                        $record->uuid = $uuid;
                                        break;
                                    }
                                }
                                $list[$uuid] = $record;
                            }
                        }
                    }
                }                                
            }
            elseif($count == 2){
                if(
                    is_object($list) &&
                    Core::object_is_empty($list)){
                    return [];
                }
                $attribute = false;
                $sortable_1 = 'ASC';
                $sortable_2 = 'ASC';
                foreach($list as $uuid => $node){
                    if($key){
                        if(is_array($node)){
                            if(array_key_exists($key, $node)){
                                $select = $node[$key];
                            }
                        } else {
                            if(property_exists($node, $key)){
                                $select = $node->$key;
                            }
                        }
                    } else {
                        $select = $node;
                    }
                    foreach($sort as $attribute => $record){
                        if(is_array($select)){
                            if(array_key_exists($attribute, $select)){
                                if(is_scalar($select[$attribute])){
                                    $result[$select[$attribute]][] = $node;
                                } else if (is_array($select[$attribute])){
                                    $attr = '';
                                    foreach($select[$attribute] as $node_attribute){
                                        if(is_scalar($node_attribute)){
                                            $attr .= '.' . $node_attribute;
                                        }
                                    }
                                    $attr = substr($attr, 1);
                                    $result[$attr][] = $node;
                                }
                            } else {
                                $result[''][] = $node;
                            }
                        } else {
                            if(property_exists($select, $attribute)){
                                if(is_scalar($select->$attribute)){
                                    $result[$select->$attribute][] = $node;
                                } else if (is_array($select->$attribute)){
                                    $attr = '';
                                    foreach($select->$attribute as $node_attribute){
                                        if(is_scalar($node_attribute)){
                                            $attr .= '.' . $node_attribute;
                                        }
                                    }
                                    $attr = substr($attr, 1);
                                    $result[$attr][] = $node;
                                }
                            } else {
                                $result[''][] = $node;
                            }
                        }
                        $sortable_1 = $record;
                        break;
                    }                
                }                    
                unset($sort[$attribute]);                                
                if(!empty($sort) && is_array($result)){                
                    $list = [];                
                    foreach($result as $result_key => $subList){
                        foreach($subList as $nr => $node){
                            if($key){
                                if(is_array($node)){
                                    if(array_key_exists($key, $node)){
                                        $select = $node[$key];
                                    }
                                } else {
                                    if(property_exists($node, $key)){
                                        $select = $node->$key;
                                    }
                                }
                            } else {
                                $select = $node;
                            }
                            if(is_array($select)){
                                foreach($sort as $attribute => $record){
                                    if(array_key_exists($attribute, $select)){
                                        $list[$result_key][$select[$attribute]][] = $node;
                                    } else {
                                        $list[$result_key][''][] = $node;
                                    }
                                    $sortable_2 = $record;
                                    break;
                                }
                            } else {
                                foreach($sort as $attribute => $record){
                                    if(
                                        property_exists($select, $attribute) &&
                                        is_scalar($select->$attribute)
                                    ){
                                        $list[$result_key][$select->$attribute][] = $node;
                                    } else {
                                        $list[$result_key][''][] = $node;
                                    }
                                    $sortable_2 = $record;
                                    break;
                                }
                            }
                        }
                    }
                    unset($sort[$attribute]);
                    $result = $list;                
                    if(strtolower($sortable_1) == 'asc'){
                        ksort($result, SORT_NATURAL);
                    } else {
                        krsort($result, SORT_NATURAL);
                    }                
                    foreach($result as $key => $list){
                        if(strtolower($sortable_2) == 'asc'){
                            ksort($list, SORT_NATURAL);                            
                        } else {
                            krsort($list, SORT_NATURAL);
                        }
                        $result[$key] = $list;                                                
                    }                                        
                    $list = [];          
                    $has_uuid = false;      
                    foreach($result as $result_key => $subList){
                        foreach($subList as $attribute => $subSubList){
                            foreach($subSubList as $nr => $node){
                                if(is_array($node)){
                                    if(array_key_exists('uuid', $node)){
                                        $has_uuid = true;
                                        $list[$node['uuid']] = $node;
                                    } else {
                                        while(true){
                                            $uuid = Core::uuid();
                                            if(!array_key_exists($uuid, $list)){
                                                $node['uuid'] = $uuid;
                                                break;
                                            }
                                        }
                                        $list[$uuid] = $node;
                                    }
                                } else {
                                    if(property_exists($node, 'uuid')){
                                        $has_uuid = true;
                                        $list[$node->uuid] = $node;
                                    } else {
                                        while(true){
                                            $uuid = Core::uuid();
                                            if(!array_key_exists($uuid, $list)){
                                                $node->uuid = $uuid;
                                                break;
                                            }
                                        }
                                        $list[$uuid] = $node;
                                    }
                                }

                            }
                        }
                    }                                      
                }  
            }                   
        }
        if($key_reset){
            $result = [];
            foreach($list as $record){
                $result[] = $record;
            }
            return $result;
        }
        return $list;
    }
}
