<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

use Exception;

use R3m\Io\Exception\ObjectException;

class Doctrine {

    /**
     * @throws Exception
     */
    public static function configure(App $object, $event, $action='', $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $source = $object->config('project.dir.data') . 'Doctrine/ArrayCache.php';
        $destination = $object->config('project.dir.vendor') . 'doctrine/cache/lib/Doctrine/Common/Cache/ArrayCache.php';
        if(!File::exist($destination)) {
            File::copy($source, $destination);
        }
        $source = $object->config('project.dir.data') . 'Doctrine/doctrine';
        $destination = '/usr/bin/doctrine';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }

        exec('chmod 750 ' . $destination);
        $source = $object->config('project.dir.data') . 'Doctrine/Doctrine.php';
        $dir = $object->config('project.dir.root') . 'Bin' . $object->config('ds');
        $destination = $dir . 'Doctrine.php';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        exec('chmod 640 ' . $destination);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 777 ' . $dir);
            exec('chmod 666 ' . $destination);
        }
    }

    /**
     * @throws Exception
     */
    public static function restore(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $source = $object->config('project.dir.data') . 'Doctrine/ArrayCache.php';
        $destination = $object->config('project.dir.vendor') . 'doctrine/cache/lib/Doctrine/Common/Cache/ArrayCache.php';
        if(File::exist($destination)) {
            File::delete($destination);
        }
        File::copy($source, $destination);
        $source = $object->config('project.dir.data') . 'Doctrine/doctrine';
        $destination = '/usr/bin/doctrine';
        if(File::exist($destination)){
            File::delete($destination);
        }
        File::copy($source, $destination);
        exec('chmod 750 ' . $destination);
        $source = $object->config('project.dir.data') . 'Doctrine/Doctrine.php';
        $dir = $object->config('project.dir.root') . 'Bin' . $object->config('ds');
        $destination = $dir . 'Doctrine.php';
        if(File::exist($destination)){
            File::delete($destination);
        }
        File::copy($source, $destination);
        exec('chmod 640 ' . $destination);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 777 ' . $dir);
            exec('chmod 666 ' . $destination);
        }
    }

    /**
     * @throws ObjectException
     */
    public static function orm_generate_proxies(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $command = '{{binary()}} doctrine orm:generate-proxies';
        $command = str_replace('{{binary()}}', Core::binary(), $command);
        Core::execute($object, $command);
    }

}