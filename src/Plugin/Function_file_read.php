<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

function function_file_read(Parse $parse, Data $data, $url=''){
    if(File::exist($url)){
        $mtime = File::mtime($url);
        $object = $parse->object();
        $require_url = $object->config('require.url');
        $require_mtime = $object->config('require.mtime');
        if(empty($require_url)){
            $require_url = [];
            $require_mtime = [];
        }
        if(
            !in_array(
                $url,
                $require_url,
                true
            ) &&
            !str_contains(
                $url,
                $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'Parse' .
                $object->config('ds')
            )
        ){
            $require_url[] = $url;
            $require_mtime[] = $mtime;
            $object->config('require.url', $require_url);
            $object->config('require.mtime', $require_mtime);
        }

    }
    return File::read($url);
}
