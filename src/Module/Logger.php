<?php
/**
 * @author          Remco van der Velde
 * @since           13-03-2022
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use R3m\Io\App;
use R3m\Io\Config;

use Exception;

class Logger {

    /**
     * @throws Exception
     */
    public static function configure(App $object): void
    {
        $interface = $object->config('log');
        $is = null;
        if($interface){
            foreach($interface as $name => $record){
                $name = ucfirst($name);
                if(
                    property_exists($record, 'default') &&
                    !empty($record->default)
                ){
                    //disabled strange setting, use app as default
//                    $object->config('project.log.name', $name);
                }
                if(
                    property_exists($record, 'is') &&
                    !empty($record->is)
                ){
                    $is = $record->is;
                }
                if(
                    property_exists($record, 'options') &&
                    is_object($record->options) &&
                    property_exists($record->options, 'class') &&
                    !empty($record->options->class) &&
                    is_string($record->options->class)
                ){
                    if(
                        property_exists($record->options, 'parameters') &&
                        !empty($record->options->parameters) &&
                        is_array($record->options->parameters)
                    ){
                        //use constants in config & replace them here
                        $parameters = $record->options->parameters;
                        $parameters = Config::parameters($object, $parameters);
                    } else {
                        $parameters = [];
                        $parameters[] = $name;
                    }
                    $logger = new $record->options->class(...$parameters);
                    d($record);
                    if(
                        property_exists($record, 'handler') &&
                        !empty($record->handler) &&
                        is_array($record->handler)
                    ){
                        foreach($record->handler as $handler){
                            if(
                                property_exists($handler, 'options') &&
                                is_object($handler->options) &&
                                property_exists($handler->options, 'class') &&
                                !empty($handler->options->class) &&
                                is_string($handler->options->class)
                            ){
                                if(
                                    property_exists($handler->options, 'parameters') &&
                                    !empty($handler->options->parameters) &&
                                    is_array($handler->options->parameters)
                                ){
                                    //use constants in config & replace them here
                                    $parameters = $handler->options->parameters;
                                    $parameters = Config::parameters($object, $parameters);
                                } else {
                                    $parameters = [];
                                }
                                if(array_key_exists(0, $parameters)){
                                    $url = $parameters[0];
                                    if(!File::exist($url)){
                                        $dir = Dir::name($url);
                                        Dir::create($dir, Dir::CHMOD);
                                        File::touch($url);
                                        File::permission($object, [
                                            'dir' => $dir,
                                            'url' => $url,
                                        ]);
                                    }
                                }
                                $push = new $handler->options->class(...$parameters);
                                if(
                                    property_exists($handler, 'formatter') &&
                                    !empty($handler->formatter) &&
                                    is_object($handler->formatter)
                                ){
                                    if(
                                        property_exists($handler->formatter, 'options') &&
                                        is_object($handler->formatter->options) &&
                                        property_exists($handler->formatter->options, 'class') &&
                                        !empty($handler->formatter->options->class) &&
                                        is_string($handler->formatter->options->class)
                                    ){
                                        if(
                                            property_exists($handler->formatter->options, 'parameters') &&
                                            !empty($handler->formatter->options->parameters) &&
                                            is_array($handler->formatter->options->parameters)
                                        ){
                                            //use constants in config & replace them here
                                            $parameters = $handler->formatter->options->parameters;
                                            $parameters = Config::parameters($object, $parameters);
                                        } else {
                                            $parameters = [];
                                        }
                                        if(method_exists($push, 'setFormatter')){
                                            $formatter = new $handler->formatter->options->class(...$parameters);
                                            $push->setFormatter($formatter);
                                        }
                                    }
                                }
                                elseif(
                                    !property_exists($handler, 'formatter') &&
                                    method_exists($push, 'setFormatter')
                                ){
                                    $formatter =new \Monolog\Formatter\LineFormatter();
                                    $push->setFormatter($formatter);
                                }
                                if(method_exists($logger, 'pushHandler')){
                                    $logger->pushHandler($push);
                                }
                            }
                        }
                    }
                    if(
                        property_exists($record, 'processor') &&
                        !empty($record->processor) &&
                        is_array($record->processor)
                    ){
                        foreach($record->processor as $processor){
                            if(
                                property_exists($processor, 'options') &&
                                is_object($processor->options) &&
                                property_exists($processor->options, 'class') &&
                                !empty($processor->options->class) &&
                                is_string($processor->options->class)
                            ){
                                if(
                                    property_exists($processor->options, 'parameters') &&
                                    !empty($processor->options->parameters) &&
                                    is_array($processor->options->parameters)
                                ){
                                    //use constants in config & replace them here
                                    $parameters = $processor->options->parameters;
                                    $parameters = Config::parameters($object, $parameters);
                                } else {
                                    $parameters = [];
                                }
                                $push = new $processor->options->class(...$parameters);
                                if(method_exists($logger, 'pushProcessor')){
                                    $logger->pushProcessor($push);
                                }
                            }
                        }
                    }
                    $logName = lcfirst($logger->getName());
                    $object->logger($logger->getName(), $logger);
                    $object->config('project.log.' . $logName, $logger->getName());
                    d($logger);
                    if(
                        property_exists($record, 'channel') &&
                        !empty($record->channel) &&
                        is_array($record->channel)
                    ){
                        foreach($record->channel as $withName){
                            $withName = ucfirst($withName);
                            $channel = $logger->withName($withName);
                            $logName = lcfirst($withName);
                            if($logName !== 'name'){
                                $object->config('project.log.' . $logName, $withName);
                            }
                            $object->logger($channel->getName(), $channel);
                            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                $object->logger($channel->getName())->info('Channel initialised.', [$withName]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function alert($message=null, $context=[], $channel=''): void
    {
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.app');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->alert($message, $context);
        }

    }

    /**
     * @throws Exception
     */
    public static function critical($message=null, $context=[], $channel=''): void
    {
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.error');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->critical($message, $context);
        }

    }

    /**
     * @throws Exception
     */
    public static function debug($message=null, $context=[], $channel=''): void
    {
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.debug');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->debug($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function emergency($message=null, $context=[], $channel=''): void
    {
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.error');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->emergency($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function error($message=null, $context=[], $channel=''): void
    {
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.error');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->error($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function info($message=null, $context=[], $channel=''): void
    {
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.app');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->info($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function notice($message=null, $context=[], $channel=''): void
    {
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.app');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->notice($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function warning($message=null, $context=[], $channel=''): void
    {
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.app');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->warning($message, $context);
        }
    }
}