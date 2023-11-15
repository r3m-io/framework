<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

use Exception;

use R3m\Io\Exception\DirectoryCreateException;

class Apache2 {

    /**
     * @throws Exception
     */
    public static function restore(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $url = $object->config('project.dir.data') . 'Apache2' . $object->config('ds');
        $dir = new Dir();
        $read = $dir->read($url);
        foreach($read as $file){
            if($file->type === File::TYPE){
                $source = $file->url;
                $destination = '/etc/apache2/sites-available/' . $file->name;
                if(File::exist($destination)) {
                    File::delete($destination);
                }
                File::copy($source, $destination);
                exec('chmod 640 ' . $destination);
                $disabled = $object->config('server.site.disabled');
                if(in_array($file->name, $disabled, true)){
                    exec('a2dissite ' . $file->name);
                } else {
                    exec('a2ensite ' . $file->name);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function backup(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $destination_dir = $object->config('project.dir.data') . 'Apache2' . $object->config('ds');
        $url = '/etc/apache2/sites-available/';
        $dir = new Dir();
        $read = $dir->read($url);
        foreach($read as $file){
            if($file->type === File::TYPE){
                $source = $file->url;
                $destination = $destination_dir . $file->name;
                if(File::exist($destination)) {
                    File::delete($destination);
                }
                File::copy($source, $destination);
                exec('chmod 640 ' . $destination);
                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                    exec('chmod 666 ' . $destination);
                }
                exec('chown www-data:www-data ' . $destination);
            }
        }
    }

    /**
     * @throws DirectoryCreateException
     * @throws Exception
     */
    public static function configure(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        Dir::create('/run/php');
        $command = 'a2enmod proxy_fcgi setenvif';
        exec($command);
        $command = 'a2enconf php8.2-fpm';
        exec($command);
        $command = 'a2dismod php7.4';
        exec($command);
        $command = 'a2dismod mpm_prefork';
        exec($command);
        $command = 'a2enmod mpm_event';
        exec($command);
        $command = 'a2enmod http2';
        exec($command);
        $command = 'a2enmod rewrite';
        exec($command);
        $command = 'a2enmod ssl';
        exec($command);
        $command = '. /etc/apache2/envvars';
        exec($command);
    }

    /**
     * @throws Exception
     */
    public static function restart(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $command = 'service apache2 restart';
        exec($command);
    }
}