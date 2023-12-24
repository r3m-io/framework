<?php
/**
 * @author          Remco van der Velde
 * @since           18-12-2020
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use stdClass;

use R3m\Io\App;

use R3m\Io\Module\Data as Storage;
use R3m\Io\Module\Response;
use R3m\Io\Module\Template\Main;

use R3m\Io\Node\Model\Node;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

class Middleware extends Main {

    const NAME = 'Middleware';
    const OBJECT = 'System.Middleware';

    public function __construct(App $object){
        $this->object($object);
    }


    /**
     * @throws Exception
     */
    public static function on(App $object, $data, $options=[]): void
    {
        $list = $object->get(App::MIDDLEWARE)->get(Middleware::OBJECT);
        if(empty($list)){
            $list = [];
        }
        if(is_array($data)){
            foreach($data as $node){
                $list[] = $node;
            }
        } else {
            $list[] = $data;
        }
        $object->get(App::MIDDLEWARE)->set(Middleware::OBJECT, $list);
    }

    public static function off(App $object, $record, $options=[]): void
    {
        //need rewrite
//        $action = $record->get('action');
//        $options = $record->get('options');
        /*
        $list = $object->get(App::MIDDLEWARE)->get(Middleware::NAME);
        if(empty($list)){
            return;
        }
        //remove them on the sorted list backwards so sorted on input order
        krsort($list);
        foreach($list as $key => $node){
            if(empty($options)){
                if($node['action'] === $action){
                    unset($list[$key]);
                    break;
                }
            } else {
                if($node['action'] === $action){
                    foreach($options as $options_key => $value){
                        if(
                            $value === true &&
                            is_array($node['options']) &&
                            array_key_exists($options_key, $node['options'])
                        ){
                            unset($list[$key]);
                            break;
                        }
                        if(
                            $value === true &&
                            is_object($node['options']) &&
                            property_exists($node['options'], $options_key)
                        ){
                            unset($list[$key]);
                            break;
                        }
                        elseif(
                            is_array($node['options']) &&
                            array_key_exists($options_key, $node['options']) &&
                            $node['options'][$options_key] === $value
                        ){
                            unset($list[$key]);
                            break;
                        }
                        elseif(
                            is_object($node['options']) &&
                            property_exists($node['options'], $options_key) &&
                            $node['options']->{$options_key} === $value
                        ){
                            unset($list[$key]);
                            break;
                        }
                    }
                }
            }
        }
        */
//        $object->get(App::MIDDLEWARE)->set(Middleware::NAME, $list);
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function trigger(App $object, $options=[]){
        $middlewares = $object->get(App::MIDDLEWARE)->data(Middleware::OBJECT);
        $response = null;
        if(empty($middlewares)){
            if(
                array_key_exists('route', $options)
            ){
                return $options['route'];
            }
            return null;
        }
        if(is_array($middlewares) || is_object($middlewares)){
            foreach($middlewares as $middleware){
                if(is_object($middleware)) {
                    if(
                        property_exists($middleware, 'options') &&
                        property_exists($middleware->options, 'controller') &&
                        is_array($middleware->options->controller)
                    ){
                        //middleware need route match
                        if(
                            (
                                array_key_exists('route', $options) &&
                                is_object($options['route']) &&
                                property_exists($options['route'], 'uuid') &&
                                property_exists($middleware, 'route') &&
                                $options['route']->uuid === $middleware->route
                            ) ||
                            (
                                property_exists($middleware, 'route') &&
                                $middleware->route === '*'
                            )
                        ){
                            foreach($middleware->options->controller as $controller){
                                $route = new stdClass();
                                $route->controller = $controller;
                                $route = Route::controller($route);
                                if(
                                    property_exists($route, 'controller') &&
                                    property_exists($route, 'function')
                                ){
                                    $middleware = new Storage($middleware);
                                    try {
                                        $response = $route->controller::{$route->function}($object, $middleware, $options);
                                        if($middleware->get('stopPropagation')){
                                            break 2;
                                        }
                                    }
                                    catch (LocateException $exception){
                                        if($object->config('project.log.error')){
                                            $object->logger($object->config('project.log.error'))->error('LocateException', [ $route, (string) $exception ]);
                                        }
                                        elseif($object->config('project.log.name')){
                                            $object->logger($object->config('project.log.name'))->error('LocateException', [ $route, (string) $exception ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        d($response);
        ddd($options);
        if($response){
            return new Response($response);
        }
        if(array_key_exists('route', $options)){
            return $options['route'];
        }
        return null;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public static function configure(App $object): void
    {
        $node = new Node($object);
        $role_system = $node->role_system();
        if(!$role_system){
            return;
        }
        if(!$node->role_has_permission($role_system, 'System:Middleware:list')){
            return;
        }
        $response = $node->list(
            Middleware::OBJECT,
            $role_system,
            [
                'sort' => [
                    'route' => 'ASC',
                    'options.priority' => 'ASC'
                ],
                'limit' => '*',
                'ramdisk' => true
            ]
        );
        if(
            $response &&
            array_key_exists('list', $response)
        ){
            Middleware::on($object, $response['list']);
        }
    }
}