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

use R3m\Io\Config;

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;

function function_package_dir(Parse $parse, Data $data, $prefix='', $package=''){
    $object = $parse->object();
    if(empty($prefix)){
        throw new Exception('Prefix is empty');
    }
    $explode = explode('_', $package);
    foreach($explode as $nr => $value){
        $explode[$nr] = ucfirst($value);
    }
    $package = implode('/', $explode);
    $package = Dir::ucfirst($package);

    $explode = explode('/', $package);
    if(substr($prefix, 0, -1) !== '/'){
        $prefix .= '/';
    }
    $result = $prefix . $package;
    if(Dir::is($result)){
        return $result;
    }
    $dir = $prefix;
    if($object->config(Config::POSIX_ID) === 0){
        if(!Dir::is($dir)){
            Dir::create($dir, Dir::CHMOD);
        }
        $command = 'chown www-data:www-data ' . $dir;
        exec($command);
    }
    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
        $command = 'chmod 777 ' . $dir;
        exec($command);
    }
    foreach($explode as $nr => $value){
        $dir .= $value . '/';
        Dir::create($dir, Dir::CHMOD);
        if($object->config(Config::POSIX_ID) === 0){
            $command = 'chown www-data:www-data ' . $dir;
            exec($command);
        }
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            $command = 'chmod 777 ' . $dir;
            exec($command);
        }
    }
    return $result;
}
