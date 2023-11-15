<?php

namespace Event\R3m\Io\System;

use Event\R3m\Io\Framework\Email;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Controller;
use R3m\Io\Module\Core;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\ObjectException;

class Restart {

    /**
     * @throws ObjectException
     */
    public static function execute(App $object, $event, $options=[]): void
    {
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $command = 'ps -aux';
        $object->config('core.execute.mode', Core::STREAM);
        Core::execute($object, $command, $output);
        $output = explode(PHP_EOL, $output);
        foreach($output as $line){
            if(strpos($line, '/usr/bin/start') !== false){
                $line = trim($line);
                $line = preg_replace('/\s+/', ' ', $line);
                $line = explode(' ', $line);
                $pid = $line[1];
                Event::Trigger(
                    $object,
                    'cli.system.restart.notification',
                    $options
                );
                $command = 'kill -9 ' . $pid;
                Core::execute($object, $command);
                break;
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function notification(App $object, $event, $options=[]): void
    {
        $action = $event->get('action');
        Email::queue(
            $object,
            $action,
            $options
        );
    }
}