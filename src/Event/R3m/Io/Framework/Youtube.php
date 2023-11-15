<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\File;

class Youtube
{

    /**
     * @throws \Exception
     */
    public static function restore(App $object, $event, $options = []): void
    {
        if ($object->config(Config::POSIX_ID) !== 0) {
            return;
        }
        $url_source = $object->config('project.dir.data') .
            'Youtube' .
            $object->config('ds') .
            'yt-dlp'
        ;
        $url_destination = '/usr/bin/' . 'yt-dlp';
        File::copy($url_source, $url_destination);
        $command = 'chmod +x ' . $url_destination;
        exec($command);
    }
}