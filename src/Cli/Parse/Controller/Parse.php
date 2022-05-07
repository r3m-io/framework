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
namespace R3m\Io\Cli\Parse\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\View;
use R3m\Io\Module\Parse as Parser;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Parse extends View{
    const NAME = 'Parse';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_RESTART = 'restart';
    const COMMAND_COMPILE = 'compile';
    const COMMAND = [
        Parse::COMMAND_INFO,
        Parse::COMMAND_RESTART,
        Parse::COMMAND_COMPILE
    ];

    const DEFAULT_COMMAND = Parse::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{{$command}}';
    const EXCEPTION_COMMAND = 'invalid command (' . Parse::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    /**
     * @throws Exception
     */
    public static function run($object){
        $command = $object->parameter($object, Parse::NAME, 1);

        if($command === null){
            $command = Parse::DEFAULT_COMMAND;
        }
        if(!in_array($command, Parse::COMMAND)){
            $exception = str_replace(
                Parse::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Parse::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        return Parse::{$command}($object);
    }

    private static function info($object){
        try {
            $name = Parse::name(__FUNCTION__, Parse::NAME);
            $url = Parse::locate($object, $name);
            return Parse::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;
        }
    }

    private static function restart($object){
        try {
            $name = Parse::name(__FUNCTION__, Parse::NAME);
            $url = Parse::locate($object, $name);
            return Parse::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;
        }
    }

    /**
     * @throws Exception
     */
    private static function compile($object)
    {
        try {
            $template_url = $object->parameter($object, __FUNCTION__, 1);
            $data_url = $object->parameter($object, __FUNCTION__, 2);
            $is_json = false;
            if (File::exist($template_url)) {
                $extension = File::extension($template_url);
                if($object->config('extension.json') === '.' . $extension) {
                    $read = $object->data_read($template_url);
                    if($read){
                        $read = $read->data();
                        $is_json = true;
                    }
                } else {
                    $read = File::read($template_url);
                }
                if ($read) {
                    $mtime = File::mtime($template_url);
                    $parse = new Parser($object);
                    $parse->storage()->data('r3m.io.parse.view.url', $template_url);
                    $parse->storage()->data('r3m.io.parse.view.mtime', $mtime);
                    $object->data('ldelim', '{');
                    $object->data('rdelim', '}');
                    $data = $object->data_read($data_url);
                    $data = Core::object_merge($object->data(), $data->data());
                    unset($data->{App::NAMESPACE});
                    $read = $parse->compile($read, $data, $parse->storage());
                    if($is_json){
                        $read = Core::object($read, Core::OBJECT_JSON);
                    }
                    return $read;
                }
            }
        } catch (Exception $exception){
            return $exception->getMessage() . PHP_EOL;
        }
    }
}