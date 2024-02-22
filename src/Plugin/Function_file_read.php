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
        $require = $object->config('require');
        if(empty($require)){
            $require = [];
        }
        $require[] = (object) [
            'url' => $url,
            'mtime' => $mtime
        ];
        $object->config('require', $require);
    }
    return File::read($url);
}
