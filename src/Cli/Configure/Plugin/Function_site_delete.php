<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use Exception;

/**
 * @throws Exception
 */
function function_site_delete(Parse $parse, Data $data, $server=null){
    $id = posix_geteuid();
    if(
        !in_array(
            $id,
            [
                0
            ]
        )
    ){
        throw new Exception('Only root can configure site delete...');
    }
    if(!empty($server) && is_object($server)){
        $url = '/etc/apache2/sites-available/';
        $dir = new Dir();
        $read = $dir->read($url);
        foreach($read as $file){
            if($file->type != File::TYPE){
                continue;
            }
            if(stristr($file->name, str_replace('.', '-', $server->name)) !== false){
                File::delete($file->url);
            }
        }
    } else {
        throw new Exception('Server variable needs to be an object');
    }









}

