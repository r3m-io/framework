<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\File;

class Git {

    public static function configure(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $commands = $object->config('git.command');
        if(is_array($commands)){
            foreach($commands as $command){
                exec($command);
            }
        }
    }

    public static function restore(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $dir = $object->config('project.dir.root') .
            '.git' .
            $object->config('ds')
        ;
        $command = 'chown 1000:1000 -R ' . $dir;
        exec($command);
        $source = $object->config('project.dir.data') .
            'Git' .
            $object->config('ds') .
            '.gitconfig'
        ;
        $target = '/root/.gitconfig';
        if(File::exist($source)){
            $command = 'cp ' . $source . ' ' . $target;
            exec($command);
        }
    }
}