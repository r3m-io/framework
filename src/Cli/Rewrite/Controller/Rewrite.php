<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Cli\Rewrite\Controller;

use R3m\Io\App;

use R3m\Io\Module\Controller;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Exception\ObjectException;



class Rewrite extends Controller {
    const NAME = 'Rewrite';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_DIRECTORY = 'directory';
    const COMMAND_BATCH = 'batch';

    const COMMAND = [
        Rewrite::COMMAND_INFO,
        Rewrite::COMMAND_DIRECTORY,
        Rewrite::COMMAND_BATCH
    ];
    const DEFAULT_COMMAND = Rewrite::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{{$command}}';
    const EXCEPTION_COMMAND = 'invalid command (' . Rewrite::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    const INFO = '{{binary()}} license                        | r3m_io/framework license';

    /**
     * @throws Exception
     */
    public static function run(App $object){
        $command = $object->parameter($object, Rewrite::NAME, 1);

        if($command === null){
            $command = Rewrite::DEFAULT_COMMAND;
        }
        if(!in_array($command, Rewrite::COMMAND, true)){
            $exception = str_replace(
                Rewrite::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Rewrite::EXCEPTION_COMMAND
            );
            $exception = new Exception($exception);
            Event::trigger($object, 'cli.' . strtolower(Rewrite::NAME) . '.' . __FUNCTION__, [
                'command' => $command,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $response = Rewrite::{$command}($object);
        Event::trigger($object, 'cli.' . strtolower(Rewrite::NAME) . '.' . __FUNCTION__, [
            'command' => $command
        ]);
        return $response;
    }

    /**
     * @throws ObjectException
     */
    private static function info(App $object)
    {
        $name = false;
        $url = false;
        try {
            $name = Rewrite::name(__FUNCTION__, Rewrite::NAME);
            $url = Rewrite::locate($object, $name);
            $result = Rewrite::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Rewrite::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url
            ]);
            return $result;
        } catch (Exception | LocateException | UrlEmptyException | UrlNotExistException $exception) {
            Event::trigger($object, 'cli.' . strtolower(Rewrite::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }

    /**
     * @throws Exception
     */
    private static function directory(App $object)
    {
        $options = App::options($object);

        if(property_exists($options, 'directory') === false){
            throw new Exception('Directory not found');
        }
        if(property_exists($options, 'from') === false){
            throw new Exception('From not found');
        }
        if(property_exists($options, 'to') === false){
            throw new Exception('To not found');
        }
        if(!is_array($options->from)){
            $options->from = [$options->from];
        }
        $dir = new Dir();
        $list = $dir->read($options->directory, true);
        foreach($list as $nr => $file){
            if($file->type === Dir::TYPE){
                continue;
            }
            $file->extension = File::extension($file->url);
            if(property_exists($options, 'extension')){
                if(!is_array($options->extension)){
                    $options->extension = [$options->extension];
                }
                foreach($options->extension as $extension){
                    if($file->extension === $extension){
                        $read = File::read($file->url);
                        foreach($options->from as $from){
                            $read = str_replace($from, $options->to, $read);
                        }
                        File::write($file->url, $read);
                    }
                }
            } else {
                $read = File::read($file->url);
                foreach($options->from as $from){
                    $read = str_replace($from, $options->to, $read);
                }
                File::write($file->url, $read);
            }
        }
    }




    /**
     * @throws Exception
     */
    private static function batch(App $object)
    {
        $options = App::options($object);
        if (property_exists($options, 'directory') === false) {
            throw new Exception('Directory not found');
        }
        $dir = new Dir();
        $list = $dir->read($options->directory, true);
        foreach($list as $file){
            if($file->type === File::TYPE){
                $extension = File::extension($file->url);
                ddd($extension);
            }
        }




        /*
        if (property_exists($options, 'package') === false) {
            throw new Exception('Package not found');
        }
        $package = $options->package;
        $data = '
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=php -from[]=R3m\\Io -to=Difference\\Fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=php -from[]=R3m -to=Difference
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=php -from[]=\'Io\' -to=\'Fun\'
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=php -from[]=r3m.io -to=difference.fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=php -from[]=r3m_io -to=difference_fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=json -from[]=r3m_io -to=difference_fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=json -from[]=R3m\\\\Io -to=Difference\\\\fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=json -from[]=R3m\\Io -to=Difference\\fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=json -from[]=r3m_io -to=difference_fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=json -from[]=r3m-io -to=difference-fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=json -from[]=r3m.io -to=difference.fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=json -from[]=R3m:Io -to=Difference:Fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=json -from[]=R3m.Io -to=Difference.Fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=tpl -from[]=R3m:Io -to=Difference:Fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=tpl -from[]=R3m.Io -to=Difference.Fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=tpl -from[]=r3m.io -to=difference.fun
        app rewrite directory -directory=/home/remco/difference-fun/' . $package . '/ -extension=tpl -from[]=r3m_io -to=difference_fun';
        $data = explode(PHP_EOL, $data);
        foreach($data as $nr => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            exec($line);
        }
        */
    }
}
