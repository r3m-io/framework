<?php

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Exception\FileMoveException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

/**
 * @throws Exception
 */
function function_environment_set(Parse $parse, Data $data, $environment=''){
    $id = posix_geteuid();
    if(
        !in_array(
            $id,
            [
                0,
                33
            ]
        )
    ){
        throw new Exception('Only root & www-data can configure domain add...');
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
    }
    $read->data('framework.environment', $environment);
    try {
        File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
        $id = posix_geteuid();
        if($id === 0){
            File::chmod($url, 0666);
            $project_dir_data = $object->config('project.dir.data');
            Core::execute('chown www-data:www-data -R ' . $project_dir_data);
            if(File::exist($project_dir_data . 'Cache/0/')){
                Core::execute('chown root:root -R ' . $project_dir_data . 'Cache/0/');
            }
            if(File::exist($project_dir_data . 'Compile/0/')){
                Core::execute('chown root:root -R ' . $project_dir_data . 'Compile/0/');
            }
            if(File::exist($project_dir_data . 'Cache/1000/')){
                Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Cache/1000/');
            }
            if(File::exist($project_dir_data . 'Compile/1000/')){
                Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Compile/1000/');
            }
        }
    } catch (Exception | FileWriteException | ObjectException $exception){
        return $exception;
    }
    return ucfirst($environment) . ' mode enabled.' . PHP_EOL;
}

