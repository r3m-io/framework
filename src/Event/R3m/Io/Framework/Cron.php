<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

use Exception;

class Cron {

    /**
     * @throws Exception
     */
    public static function install(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $environment = $object->config('framework.environment');
        switch($environment){
            case 'development':
            case 'staging':
            case 'production':
                $source = $object->config('project.dir.data') . 'Cron' . $object->config('ds') . 'Cron.' . $environment;
            break;
            default:
                $source = $object->config('project.dir.data') . 'Cron' . $object->config('ds') . 'Cron.development';
        }

        $dir = '/etc/cron.d/';
        if(!Dir::is($dir)){
            Dir::create($dir, Dir::CHMOD);
        }
        $destination = $dir . 'vps-cron';
        if(File::exist($destination)){
            File::delete($destination);
        }
        File::copy($source, $destination);
        $command = 'chmod 644 ' . $destination;
        exec($command);
        $command = 'crontab ' . $destination;
        exec($command);
        $command = 'touch /var/log/cron.log';
        exec($command);
    }

    public static function start(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $command = $object->config('service.cron.start');
        exec($command);
    }

    public static function restart(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $command = $object->config('service.cron.restart');
        exec($command);
    }
}