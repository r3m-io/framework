<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;

use Exception;

class Configure {

    /**
     * @throws Exception
     */
    public static function public_create(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        if(!Dir::is($object->config('project.dir.public'))){
            $command = '{{binary()}} configure public create ' . $object->config('dictionary.public');
            $command = str_replace('{{binary()}}', Core::binary(), $command);
            Core::execute($object, $command);
        }
    }

}