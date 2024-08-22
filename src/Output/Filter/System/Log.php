<?php

namespace R3m\Io\Output\Filter\System;

use R3m\Io\App;

use R3m\Io\Module\Controller;

class Log extends Controller {
    const DIR = __DIR__ . '/';

    public static function output_filter(App $object, $response=null): object
    {
        $result = [];
        ddd($response);
        if(
            !empty($response) &&
            (
                is_object($response) ||
                is_array($response)
            )
        ){
            foreach($response as $nr => $record){
                if(
                    is_array($record) &&
                    array_key_exists('name', $record)
                ){
                    $result[$record['name']] = $record;
                }
                elseif(
                    is_object($record) &&
                    property_exists($record, 'name')
                ){
                    $result[$record->name] = $record;
                }
            }
        }
        d($result);
        return (object) $result;
    }
}