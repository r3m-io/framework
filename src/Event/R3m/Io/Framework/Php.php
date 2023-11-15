<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

use Exception;

class Php {

    /**
     * @throws Exception
     */
    public static function restart(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $command = 'service php8.2-fpm restart';
        exec($command);
    }

    public static function backup(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        //move to mnt/Data
        $dir_php = $object->config('project.dir.data') . 'Php/';
        $dir_version = $dir_php . '8.2/';
        $dir_fpm = $dir_version . 'Fpm/';
        $dir_cli = $dir_version . 'Cli/';
        $dir_fpm_pool_d = $dir_fpm . 'Pool.d/';
        Dir::create($dir_fpm, Dir::CHMOD);
        Dir::create($dir_fpm_pool_d, Dir::CHMOD);
        Dir::create($dir_cli, Dir::CHMOD);
        $command = 'cp /etc/php/8.2/fpm/php.ini ' . $dir_fpm . 'php.ini';
        exec($command);
        $command = 'cp /etc/php/8.2/fpm/php-fpm.conf' . $dir_fpm . 'php-fpm.conf';
        exec($command);
        $command = 'cp /etc/php/8.2/fpm/pool.d/www.conf' . $dir_fpm_pool_d . 'www.conf';
        exec($command);
        $command = 'cp /etc/php/8.2/cli/php.ini ' . $dir_cli . 'php.ini';
        exec($command);
        $command = 'chown www-data:www-data ' . $object->config('project.dir.data') . ' -R';
        exec($command);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            $command = 'chmod 777 ' . $dir_php;
            exec($command);
            $command = 'chmod 777 ' . $dir_version;
            exec($command);
            $command = 'chmod 777 ' . $dir_fpm;
            exec($command);
            $command = 'chmod 777 ' . $dir_fpm_pool_d;
            exec($command);
            $command = 'chmod 777 ' . $dir_cli;
            exec($command);
            $command = 'chmod 666 ' . $dir_fpm . 'php.ini';
            exec($command);
            $command = 'chmod 666 ' . $dir_fpm . 'php-fpm.conf';
            exec($command);
            $command = 'chmod 666 ' . $dir_fpm_pool_d . 'www.conf';
            exec($command);
            $command = 'chmod 666 ' . $dir_cli . 'php.ini';
            exec($command);
        } else {
            $command = 'chmod 640 ' . $dir_fpm . 'php.ini';
            exec($command);
            $command = 'chmod 640 ' . $dir_fpm . 'php-fpm.conf';
            exec($command);
            $command = 'chmod 640 ' . $dir_fpm_pool_d . 'www.conf';
            exec($command);
            $command = 'chmod 640 ' . $dir_cli . 'php.ini';
            exec($command);
        }
    }

    public static function restore(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }

        if(File::exist($object->config('project.dir.data') . 'Php/8.2/Fpm/php.ini')){
            $command = 'cp ' . $object->config('project.dir.data') . 'Php/8.2/Fpm/php.ini /etc/php/8.2/fpm/php.ini';
            exec($command);
        }
        if(File::exist($object->config('project.dir.data') . 'Php/8.2/Fpm/php-fpm.conf')){
            $command = 'cp ' . $object->config('project.dir.data') . 'Php/8.2/Fpm/php-fpm.conf /etc/php/8.2/fpm/php-fpm.conf';
            exec($command);
        }
        if(File::exist($object->config('project.dir.data') . 'Php/8.2/Fpm/Pool.d/www.conf')){
            $command = 'cp ' . $object->config('project.dir.data') . 'Php/8.2/Fpm/Pool.d/www.conf /etc/php/8.2/fpm/pool.d/www.conf';
            exec($command);
        }
        if(File::exist($object->config('project.dir.data') . 'Php/8.2/Cli/php.ini')){
            $command = 'cp ' . $object->config('project.dir.data') . 'Php/8.2/Cli/php.ini /etc/php/8.2/cli/php.ini';
            exec($command);
        }
    }
}