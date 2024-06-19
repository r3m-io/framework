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

use Exception;

class Limit extends Data{
    const LIMIT = 20;
    const MAX = 1000;

    /**
     * @throws Exception
     */
    public static function list($list): Limit
    {
        return new Limit($list);
    }

    /**
     * @throws Exception
     */
    public function with($limit=[], $options=[], &$count=0): array
    {
        $preserve_keys = false;
        if(array_key_exists('preserve_keys', $options)){
            $preserve_keys = $options['preserve_keys'];
        }
        $list = $this->data();
        $start = 0;
        if(array_key_exists('start', $limit)){
            $start = (int) $limit['start'];
        }
        if(array_key_exists('limit', $limit)){
            $the_limit = (int) $limit['limit'];
        } else {
            $the_limit = Limit::LIMIT;
        }
        if(array_key_exists('page', $limit)){
            $start = ((int) $limit['page'] * $the_limit) - $the_limit;
        }
        $nr = 0;
        $end = $start + $the_limit;
        $result = [];
        if(
            is_array($list) || 
            is_object($list)
        ){
            $is_collect = false;
            foreach($list as $record){
                if($nr === $start){
                    $is_collect = true;
                }
                if($nr < $end && $is_collect){
                    if($preserve_keys){
                        if(
                            is_object($record) &&
                            property_exists($record, 'uuid')
                        ){
                            $result[$record->uuid] = $record;
                            $count++;
                        }
                        elseif(is_object($record) && method_exists($record, 'get')){
                            $result[$record->get('uuid')] = $record;
                            $count++;
                        }
                        elseif(
                            is_array($record) &&
                            array_key_exists('uuid', $record)
                        ){
                            $result[$record['uuid']] = $record;
                            $count++;
                        } else {
                            $result[] = $record;
                            $count++;
                        }
                    } else {
                        $result[] = $record;
                        $count++;
                    }
                }
                elseif($is_collect) {
                    break;
                }
                $nr++;
            }
        }
        return $result;
    }
}
