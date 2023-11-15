<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;

class TempDirectory
{

    public static function configure(App $object, $event, $options = []): void
    {
        if ($object->config(Config::POSIX_ID) !== 0) {
            return;
        }
        $command = 'chown www-data:www-data ' . $object->config('framework.dir.temp');
        exec($command);
        echo $command . PHP_EOL;
    }
}