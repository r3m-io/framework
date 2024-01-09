<?php

namespace R3m\Io\Output\Filter\System;

use R3m\Io\App;

use R3m\Io\Module\Controller;

class Route extends Controller {
    const DIR = __DIR__ . '/';

    public static function list(App $object, $response=null): object
    {
        $result = [];
        if(
            !empty($response) &&
            is_array($response)
        ){
            foreach($response as $nr => $record){
                if(
                    is_array($record) &&
                    array_key_exists('name', $record)
                ){
                    $name = str_replace('.', '-', strtolower($record['name']));
                    $result[$name] = $record;
                }
                elseif(
                    is_object($record) &&
                    property_exists($record, 'name')
                ){
                    $name = str_replace('.', '-', strtolower($record->name));
                    $result[strtolower($name)] = $record;
                }
            }
        }
        return (object) $result;
    }
}