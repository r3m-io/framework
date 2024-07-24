<?php

namespace R3m\Io\Output\Filter\System;

use R3m\Io\App;

use R3m\Io\Module\Controller;

class Server extends Controller {
    const DIR = __DIR__ . '/';

    public static function url(App $object, $response=null): object
    {
        $result = [];
        if(
            !empty($response) &&
            is_array($response)
        ){
            foreach($response as $nr => $record){
                if(
                    is_array($record) &&
                    array_key_exists('name', $record) &&
                    array_key_exists('options', $record)
                ){
                    $name = str_replace('.', '-', strtolower($record['name']));
                    $result[$name] = $record['options'];
                }
                elseif(
                    is_object($record) &&
                    property_exists($record, 'name') &&
                    property_exists($record, 'options')
                ){
                    $name = str_replace('.', '-', strtolower($record->name));
                    $result[strtolower($name)] = $record->options;
                }
            }
        }
        return (object) $result;
    }
}