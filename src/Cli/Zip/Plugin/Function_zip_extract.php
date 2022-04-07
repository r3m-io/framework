<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;
use R3m\Io\App;

function function_zip_extract(Parse $parse, Data $data){
    $object = $parse->object();
    $object->logger()->error('test2: zip');
    $source = App::parameter($object, 'extract', 1);
    $target = App::parameter($object, 'extract', 2);
    d($source);

    $limit = $parse->limit();
    $parse->limit([
        'function' => [
            'date'
        ]
    ]);
    try {
        $target = $parse->compile($target, [], $data);
        $parse->limit($limit);
    } catch (Exception $e) {
    }

    dd($target);




    /*
    if(File::exist($target)){
        return;
    }
    Core::execute('ln -s ' . $source . ' ' . $target);
    */
}
