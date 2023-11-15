<?php

namespace Event\R3m\Io\Framework;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

class Ssh {

    /**
     * @throws Exception
     */
    public static function restore(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $source = $object->config('project.dir.data') . 'Ssh/id_ed25519';
        $destination = '/root/.ssh/id_ed25519';
        Dir::create('/root/.ssh');
        if(!File::exist($destination)){
            File::copy($source, $destination);
            exec('chmod 600 ' . $destination);
        }
        $source = $object->config('project.dir.data') . 'Ssh/id_ed25519.pub';
        $destination = '/root/.ssh/id_ed25519.pub';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            exec('chmod 644 ' . $destination);
        }
    }
}