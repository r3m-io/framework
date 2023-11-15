<?php

namespace Event\R3m\Io\Framework;


use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

use Exception;

class LetsEncrypt {

    public static function backup(App $object, $event, $options=[]){
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $command = 'wat zip archive /etc/letsencrypt/ ' . $object->config('project.dir.data') . 'Letsencrypt/Letsencrypt.zip';
        exec($command);
    }

    public static function renew(App $object, $event, $options=[]){
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $command = 'certbot renew';
        exec($command, $output);
        echo implode(PHP_EOL, $output) . PHP_EOL;
    }


    /**
     * @throws Exception
     */
    public static function restore(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        exec('apt-get install certbot python3-certbot-apache -y');
        $source = $object->config('project.dir.data') . 'Letsencrypt/Letsencrypt.zip';
        if(File::exist($source)){
            $command = Core::binary() . ' zip extract ' . $source . ' /';
            exec($command, $output);
            echo implode(PHP_EOL, $output) . PHP_EOL;
        }
        $dir = new Dir();
        $read = $dir->read('/etc/letsencrypt/live', true);
        foreach($read as $nr => $file){
            if($file->type === File::TYPE){
                $extension = File::extension($file->url);
                if($extension === 'pem'){
                    $source = str_replace([
                        '/live/',
                        '.pem'
                    ],
                        [
                            '/archive/',
                            '1.pem'
                        ],
                        $file->url
                    );
                    $dir = Dir::name($file->url);
                    Dir::change($dir);
                    File::delete($file->url);
                    File::link($source, $file->name);
                }
            }
        }
    }
}