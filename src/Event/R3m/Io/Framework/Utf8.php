<?php

namespace Event\R3m\Io\Framework;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;

class Utf8
{

    /**
     * @throws Exception
     */
    public static function export(App $object, $event, $options = []): void
    {
        if ($object->config(Config::POSIX_ID) !== 0) {
            return;
        }
        $start = 0x0000;
        $end = 0x10FFFF;
        $table = ['App' => ['Character' => ['UTF-8' => []]]];
        $index = 0;
        for ($char = $start; $char <= $end; $char++) {
            $entity = '&#' . $char . ';';
            $record = [];
            $record['char'] = mb_convert_encoding($entity, 'UTF-8', 'HTML-ENTITIES');
            /*
            $encoding = mb_detect_encoding($record['char'], mb_detect_order(), true);
            if ($encoding !== 'UTF-8') {
                $record['char'] = iconv($encoding, 'UTF-8//IGNORE', $record['char']);
            }
            */
            $record['entity'] = $entity;
            $record['#key'] = $index;
            $record['#class'] = 'App.Character.UTF-8';
            $record['uuid'] = Core::uuid();
            $table['App']['Character']['UTF-8'][$index] = $record;
            $index++;
        }
        $url = $object->config('project.dir.data') .
            'App' .
            $object->config('ds') .
            'Character.UTF-8' .
            $object->config('extension.json');
        $json = json_encode($table, JSON_INVALID_UTF8_IGNORE | JSON_PRETTY_PRINT);
        File::write($url, $json);
    }
}