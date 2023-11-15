<?php

namespace Event\R3m\Io\Framework\Parse;

use Event\R3m\Io\Framework\Email;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;

use Exception;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\ParseException;

class Build {
    const NAME = 'Build';

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function write(App $object, $event, $options = []): void
    {
        $id = $object->config(Config::POSIX_ID);
        if (empty($options['url'])) {
            return;
        }
        if(empty($options['string'])){
            return;
        }
        if(empty($options['storage'])){
            return;
        }
        if(!File::exist($options['url'])){
            return;
        }
        //make event which checks php-l and move accordingly
        $command = 'php -l ' . escapeshellcmd($options['url']);
        $default = $object->config('core.execute.stream.is.default');
        $object->config('core.execute.mode', 'stream');
        $object->config('core.execute.stream.is.default', false);
        Core::execute($object, $command, $output, $error);
        $object->config('core.execute.stream.is.default', $default);
        if ($error) {
            $url_write_error = $object->config('framework.dir.temp') . 'Parse/Error/' . File::basename($options['url']);
            if ($object->config('project.log.error')) {
                $object->logger($object->config('project.log.error'))->error($error, [$url_write_error]);
            } elseif ($object->config('project.log.name')) {
                $object->logger($object->config('project.log.name'))->error($error, [$url_write_error]);
            }
            $dir = Dir::name($url_write_error);
            Dir::create($dir, Dir::CHMOD);
            File::move($options['url'], $url_write_error, true);
            if (empty($id)) {
                exec('chown www-data:www-data ' . $dir);
            }
            exec('chmod 640 ' . $url_write_error);
            $error .= PHP_EOL .
                PHP_EOL .
                'Source: ' . $options['storage']->get('source') .
                PHP_EOL
            ;
            $exception = new ParseException(
                $error, [
                'url' => $url_write_error,
                'source' => $options['storage']->get('source'),
                ],
                $object
            );
            Event::trigger($object, 'parse.build.write.error', [
                'url' => $url_write_error,
                'string' => $options['string'],
                'source' => $options['storage']->get('source'),
                'exception' => $exception,
            ]);
            $object->config('is.exception', true);
            throw $exception;
        } else {
            Event::trigger($object, 'parse.build.write.success', [
                'url' => $options['url'],
                'string' => $options['string'],
                'source' => $options['storage']->get('source')
            ]);
        }
    }

    /**
     * @throws FileWriteException
     * @throws OptimisticLockException
     * @throws ObjectException
     * @throws ORMException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\DBAL\Exception
     */
    public static function exception(App $object, $event, $options = []): void
    {
        $action = $event->get('action');
        Email::queue(
            $object,
            $action,
            $options
        );
    }
}