<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Database;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Parse\Token;
use R3m\Io\Module\Stream\Notification;

use Exception;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

class Environment
{

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function set(App $object, $event, $options=[]): void
    {
        $id = $object->config(Config::POSIX_ID);
        if (!empty($id)) {
            return; //only root can execute this.
        }
        if (!array_key_exists('environment', $options)) {
            return;
        }
        $app_flags = App::flags($object);
        $app_options = App::options($object);

        Core::output_mode(Core::MODE_INTERACTIVE);
        $environment = $options['environment'];
        $url = $object->config('app.config.dir') . 'Environment' . $object->config('extension.json');
        $config = $object->data_read($url);
        switch($environment){
            case Config::MODE_DEVELOPMENT:
                $directories = $config->data($environment . '.directory');
                $files = $config->data($environment . '.file');
                if(is_array($directories)){
                    foreach($directories as $directory){
                        if(
                            property_exists($directory, 'chmod') &&
                            property_exists($directory->chmod, 'file') &&
                            property_exists($directory->chmod, 'directory')
                        ) {
                            if (
                                property_exists($directory, 'recursive') &&
                                $directory->recursive === true
                            ) {
                                $command = 'chmod ' .
                                    $directory->chmod->file . ' ' .
                                    $object->config('project.dir.root') .
                                    $directory->name .
                                    ' ' .
                                    '-R';
                                exec($command);
                                echo $command . PHP_EOL;
                                $command = 'cd ' .
                                    $object->config('project.dir.root') .
                                    $directory->name .
                                    $object->config('ds') .
                                    ' && ' .
                                    'chmod ' .
                                    $directory->chmod->directory .
                                    ' ' .
                                    '.'
                                ;;
                                exec($command);
                                echo $command . PHP_EOL;
                                $dir = new Dir();
                                $read = $dir->read(
                                    $object->config('project.dir.root') .
                                    $directory->name,
                                    true
                                );
                                if($read){
                                    foreach($read as $file){
                                        if($file->type === Dir::TYPE){
                                            $command = 'chmod ' .
                                                $directory->chmod->directory . ' ' .
                                                $file->url
                                            ;
                                            exec($command);
                                        }
                                    }
                                    echo $command . PHP_EOL;
                                }
                                $command = 'chown ' .
                                    $directory->owner .
                                    ':' .
                                    $directory->group .
                                    ' ' .
                                    $object->config('project.dir.root') .
                                    $directory->name .
                                    ' ' .
                                    '-R';
                                exec($command);
                                echo $command . PHP_EOL;
                            } else {
                                $command = 'chmod ' .
                                    $directory->chmod->directory . ' ' .
                                    $object->config('project.dir.root') .
                                    $directory->name;
                                exec($command);
                                echo $command . PHP_EOL;
                                $command = 'chown ' .
                                    $directory->owner .
                                    ':' .
                                    $directory->group .
                                    ' ' .
                                    $object->config('project.dir.root') .
                                    $directory->name;
                                exec($command);
                                echo $command . PHP_EOL;
                            }
                        }
                    }
                }
                //chmod every file in application to 666 and adjust the dirs to 777
            break;
            case Config::MODE_STAGING:
                //chmod every file in application to File::CHMOD and adjust the dirs to Dir::CHMOD
            break;
            case Config::MODE_PRODUCTION:
                $directories = $config->data($environment . '.directory');
                $files = $config->data($environment . '.file');
                if(is_array($directories)){
                    foreach($directories as $directory){
                        if(
                            property_exists($directory, 'chmod') &&
                            property_exists($directory->chmod, 'file') &&
                            property_exists($directory->chmod, 'directory')
                        ){
                            if(
                                property_exists($directory, 'recursive') &&
                                $directory->recursive === true
                            ){
                                $command = 'chmod ' .
                                    $directory->chmod->file . ' ' .
                                    $object->config('project.dir.root') .
                                    $directory->name .
                                    ' ' .
                                    '-R';
                                exec($command);
                                echo $command . PHP_EOL;
                                $command = 'cd ' .
                                    $object->config('project.dir.root') .
                                    $directory->name .
                                    $object->config('ds') .
                                    ' && ' .
                                    'chmod ' .
                                    $directory->chmod->directory .
                                    ' ' .
                                    '.'
                                ;;
                                exec($command);
                                echo $command . PHP_EOL;
                                $dir = new Dir();
                                $read = $dir->read(
                                    $object->config('project.dir.root') .
                                    $directory->name,
                                    true
                                );
                                if($read){
                                    foreach($read as $file){
                                        if($file->type === Dir::TYPE){
                                            $command = 'chmod ' .
                                                $directory->chmod->directory . ' ' .
                                                $file->url
                                            ;
                                            exec($command);
                                        }
                                    }
                                    echo $command . PHP_EOL;
                                }
                                $command = 'chown ' .
                                    $directory->owner .
                                    ':' .
                                    $directory->group .
                                    ' ' .
                                    $object->config('project.dir.root') .
                                    $directory->name .
                                    ' ' .
                                    '-R';
                                exec($command);
                                echo $command . PHP_EOL;
                            } else {
                                $command = 'chmod ' .
                                    $directory->chmod->directory . ' ' .
                                    $object->config('project.dir.root') .
                                    $directory->name
                                ;
                                exec($command);
                                echo $command . PHP_EOL;
                                $command = 'chown ' .
                                    $directory->owner .
                                    ':' .
                                    $directory->group .
                                    ' ' .
                                    $object->config('project.dir.root') .
                                    $directory->name
                                ;
                                exec($command);
                                echo $command . PHP_EOL;
                            }
                        }

                    }
                }

                //chmod every file in application to File::CHMOD and adjust the dirs to Dir::CHMOD
            break;
        }

    }


    /**
     * @throws ObjectException
     * @throws Exception
     */
    /*
    public static function set(App $object, $event, $options = []): void
    {
        $id = $object->config(Config::POSIX_ID);
        if (!empty($id)) {
            return; //only root can execute this.
        }
        if (!array_key_exists('environment', $options)) {
            return;
        }
        $flags = App::flags($object);
        if (
            (
                property_exists($flags, 'enable-file-permission') &&
                $flags->{"enable-file-permission"} === true
            ) ||
            (
                property_exists($flags, 'disable-file-permission') &&
                $flags->{"disable-file-permission"} !== true &&
                $options['environment'] === Config::MODE_PRODUCTION
            ) ||
            (
                !property_exists($flags, 'disable-file-permission') &&
                $options['environment'] === Config::MODE_PRODUCTION
            )
        ) {
            Core::output_mode(Core::MODE_INTERACTIVE);
            switch ($options['environment']) {
                case Config::MODE_STAGING:
                case Config::MODE_PRODUCTION:
                case Config::MODE_DEVELOPMENT:
                    $url = $object->config('app.config.dir') . 'Environment' . $object->config('extension.json');
                    $config = $object->data_read($url);
                    $dir = new Dir();
                    if ($config && $config->has($options['environment'])) {
                        if (
                            $config->has($options['environment'] . '.directory') &&
                            is_array($config->get($options['environment'] . '.directory'))
                        ) {
                            foreach ($config->get($options['environment'] . '.directory') as $directory) {
                                $recursive = false;
                                $dir_counter = 0;
                                $file_counter = 0;
                                if (property_exists($directory, 'recursive')) {
                                    $recursive = $directory->recursive;
                                }
                                if (
                                    property_exists($directory, 'name') &&
                                    property_exists($directory, 'owner') &&
                                    property_exists($directory, 'group')
                                ) {
                                    if ($recursive) {
                                        $command = 'chown ' .
                                            $directory->owner .
                                            ':' .
                                            $directory->group .
                                            ' ' .
                                            $object->config('project.dir.root') .
                                            $directory->name .
                                            ' ' .
                                            '-R';
                                        $read = $dir->read($object->config('project.dir.root') . $directory->name, true);
                                    } else {
                                        $command = 'chown ' .
                                            $directory->owner .
                                            ':' .
                                            $directory->group .
                                            ' ' .
                                            $object->config('project.dir.root') .
                                            $directory->name;
                                        $read = $dir->read($object->config('project.dir.root') . $directory->name, false);
                                    }
                                    Core::execute($object, $command);
                                    echo $command . PHP_EOL; //done
                                    if (
                                        $read &&
                                        property_exists($directory, 'chmod') &&
                                        property_exists($directory->chmod, 'directory') &&
                                        property_exists($directory->chmod, 'file')
                                    ) {
                                        $command = 'chmod ' . $directory->chmod->directory . ' ' . $object->config('project.dir.root') . $directory->name;
                                        Core::execute($object, $command);
                                        $dir_counter++;
                                        foreach ($read as $file) {
                                            if ($file->type === Dir::TYPE) {
                                                $command = 'chmod ' . $directory->chmod->directory . ' ' . $file->url;
                                                Core::execute($object, $command);
                                                $dir_counter++;
                                            } elseif ($file->type === File::TYPE) {
                                                $command = 'chmod ' . $directory->chmod->file . ' ' . $file->url;
                                                Core::execute($object, $command);
                                                $file_counter++;
                                            }
                                        }
                                        echo 'Directories modified: ' . $dir_counter . PHP_EOL;
                                        echo 'Files modified: ' . $file_counter . PHP_EOL;
                                    }
                                }
                            }
                        }
                        if (
                            $config->has($options['environment'] . '.file') &&
                            is_array($config->get($options['environment'] . '.file'))
                        ) {
                            foreach ($config->get($options['environment'] . '.file') as $file) {
                                if (
                                    property_exists($file, 'name') &&
                                    property_exists($file, 'owner') &&
                                    property_exists($file, 'group') &&
                                    property_exists($file, 'chmod')
                                ) {
                                    $command = 'chown ' .
                                        $file->owner .
                                        ':' .
                                        $file->group .
                                        ' ' .
                                        $object->config('project.dir.root') .
                                        $file->name;
                                    Core::execute($object, $command);
                                    echo $command . PHP_EOL; //done
                                    $command = 'chmod ' .
                                        $file->chmod .
                                        ' ' .
                                        $object->config('project.dir.root') .
                                        $file->name;
                                    Core::execute($object, $command);
                                }
                            }
                        }
                    } else {
                        if($object->has('project.log.name')){
                            $object->logger($object->get('project.log.name'))->error('Environment configuration not found.');
                        }
                    }
                break;
            }
        }
    }
    */
}