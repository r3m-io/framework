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
namespace R3m\Io\Cli\Install\Controller;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use R3m\Io\Node\Model\Node;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Exception\RouteExistException;

class Install extends Controller {
    const DIR = __DIR__;
    const NAME = 'Install';
    const INFO = '{{binary()}} install                        | Install packages';

    /**
     * @throws Exception
     */
    public static function run(App $object){
        $id = $object->config(Config::POSIX_ID);
        $options = App::options($object);
        $key = App::parameter($object, 'install', 1);
        if(
            !in_array(
                $id,
                [
                    0,
                    33
                ],
                true
            )
        ){
            $exception = new Exception('Only root & www-data can install packages...');
            Event::trigger($object, 'cli.install', [
                'key' => $key,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $url = $object->config('framework.dir.data') .
            $object->config('dictionary.package') .
            $object->config('extension.json')
        ;
        $object->set(Controller::PROPERTY_VIEW_URL, $url);
        $package = $object->parse_select(
            $url,
            'package.' . $key
        );
        if(empty($package)){
            $exception = new Exception('Package: ' . $key . PHP_EOL);
            Event::trigger($object, 'cli.install', [
                'key' => $key,
                'exception' => $exception
            ]);
            throw $exception;
        }
        if($package->has('composer')){
            Dir::change($object->config('project.dir.root'));
            Core::execute($object, $package->get('composer'), $output, $notification);
            if($output){
                echo $output;
            }
            if($notification){
                echo $notification;
            }
        }
        $node = new Node($object);
        $role_system = $node->role_system();
        if(empty($role_system)){
            //install role system...
            $node->role_system_create('r3m_io/boot');
            $node->role_system_create('r3m_io/node');
            $node->role_system_create('r3m_io/route');
            //move below to setups
            $node->role_system_create('r3m_io/config');
//            $node->role_system_create('r3m_io/event');
            $node->role_system_create('r3m_io/autoload');
        }
        if(
            $package->has('copy') &&
            is_array($package->get('copy'))
        ){
            foreach($package->get('copy') as $copy){
                if(
                    property_exists($copy, 'from') &&
                    property_exists($copy, 'to') &&
                    property_exists($copy, 'recursive') &&
                    $copy->recursive === true &&
                    !empty($copy->from) &&
                    !empty($copy->to)
                ){
//                    $parse = new Parse($object, $object->data());
//                    $copy->to = $parse->compile($copy->to, $object->data());
                    if(File::exist($copy->from)){
                        if(Dir::is($copy->from)){
                            Dir::create($copy->to, Dir::CHMOD);
                            if($object->config(Config::POSIX_ID) === 0){
                                $command = 'chown www-data:www-data ' . $copy->to;
                                exec($command);
                                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                    $command = 'chmod 777 ' . $copy->to;
                                    exec($command);
                                }
                            }
                            $dir = new Dir();
                            $read = $dir->read($copy->from, true);
                            if(is_array($read)){
                                foreach($read as $file){
                                    if($file->type === Dir::TYPE){
                                        $create = str_replace($copy->from, $copy->to, $file->url);
                                        Dir::create($create, Dir::CHMOD);
                                        if($object->config(Config::POSIX_ID) === 0){
                                            $command = 'chown www-data:www-data ' . $create;
                                            exec($command);
                                        }
                                        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                            $command = 'chmod 777 ' . $create;
                                            exec($command);
                                        }
                                    }
                                }
                                foreach($read as $file){
                                    if($file->type === File::TYPE){
                                        $to = str_replace($copy->from, $copy->to, $file->url);
                                        if(
                                            !File::exist($to) ||
                                            property_exists($options, 'force')
                                        ){
                                            if(property_exists($options, 'force')){
                                                File::delete($to);
                                            }
                                            File::copy($file->url, $to);
                                            if($object->config(Config::POSIX_ID) === 0){
                                                $command = 'chown www-data:www-data ' . $to;
                                                exec($command);
                                            }
                                            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                                $command = 'chmod 666 ' . $to;
                                                exec($command);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                elseif(
                    property_exists($copy, 'from') &&
                    property_exists($copy, 'to')
                ){
//                    $parse = new Parse($object, $object->data());
//                    $copy->to = $parse->compile($copy->to, $object->data());
                    if(File::exist($copy->from)){
                        if(Dir::is($copy->from)){
                            Dir::create($copy->to, Dir::CHMOD);
                            if($object->config(Config::POSIX_ID) === 0){
                                $command = 'chown www-data:www-data ' . $copy->to;
                                exec($command);
                                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                    $command = 'chmod 777 ' . $copy->to;
                                    exec($command);
                                }
                            }
                            $dir = new Dir();
                            $read = $dir->read($copy->from, true);
                            foreach($read as $file){
                                if($file->type === Dir::TYPE){
                                    Dir::create($file->url, Dir::CHMOD);
                                    if($object->config(Config::POSIX_ID) === 0){
                                        $command = 'chown www-data:www-data ' . $create;
                                        exec($command);
                                    }
                                    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                        $command = 'chmod 777 ' . $create;
                                        exec($command);
                                    }
                                }
                            }
                            foreach($read as $file){
                                if($file->type === File::TYPE){
                                    $to = str_replace($copy->from, $copy->to, $file->url);
                                    if(
                                        !File::exist($to) ||
                                        (
                                            property_exists($options, 'force') ||
                                            property_exists($options, 'patch')
                                        )

                                    ){
                                        if(
                                            property_exists($options, 'force') ||
                                            property_exists($options, 'patch')
                                        ){
                                            File::delete($to);
                                        }
                                        File::copy($file->url, $to);
                                        if($object->config(Config::POSIX_ID) === 0){
                                            $command = 'chown www-data:www-data ' . $to;
                                            exec($command);
                                        }
                                        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                            $command = 'chmod 666 ' . $to;
                                            exec($command);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if(
            $package->has('route') &&
            is_array($package->get('route'))
        ){
            foreach($package->get('route') as $url_route){
                if(File::exist($url_route)){
                    $class = Controller::name(File::basename($url_route, $object->config('extension.json')));
                    $read = $object->data_read($url_route);
                    if($read){
                        foreach($read->data($class) as $import){
                            if(!property_exists($import, 'name')){
                                continue;
                            }
                            if(property_exists($import, 'host')){
                                $record = $node->record(
                                    $class,
                                    $node->role_system(),
                                    [
                                        'filter' => [
                                            'name' => [
                                                'operator' => '===',
                                                'value' => $import->name
                                            ],
                                            'host' => [
                                                'operator' => '===',
                                                'value' => $import->host
                                            ]
                                        ]
                                    ]
                                );
                            } else {
                                $record = $node->record(
                                    $class,
                                    $node->role_system(),
                                    [
                                        'filter' => [
                                            'name' => [
                                                'operator' => '===',
                                                'value' => $import->name
                                            ]
                                        ]
                                    ]
                                );
                            }
                            if(!$record){
                                $create = $node->create(
                                    $class,
                                    $node->role_system(),
                                    $import,
                                    []
                                );
                            }
                            elseif(
                                property_exists($options, 'force') &&
                                is_array($record) &&
                                array_key_exists('node', $record) &&
                                property_exists($record['node'], 'uuid')
                            ){
                                $import->uuid = $record['node']->uuid;
                                $put = $node->put(
                                    $class,
                                    $node->role_system(),
                                    $import,
                                    []
                                );
                            }
                            elseif(
                                property_exists($options, 'patch') &&
                                is_array($record) &&
                                array_key_exists('node', $record) &&
                                property_exists($record['node'], 'uuid')
                            ){
                                $import->uuid = $record['node']->uuid;
                                $put = $node->patch(
                                    $class,
                                    $node->role_system(),
                                    $import,
                                    []
                                );
                            }
                        }
                    }
                }
            }
        }
        elseif(
            $package->has('route') &&
            is_string($package->get('route'))
        ){
            if(File::exist($package->get('route'))){
                $node = new Node($object);
                $class = Controller::name(File::basename($package->get('route'), $object->config('extension.json')));
                $read = $object->data_read($package->get('route'));
                if($read){
                    foreach($read->data($class) as $import){
                        if(!property_exists($import, 'name')){
                            continue;
                        }
                        if(property_exists($import, 'host')){
                            $record = $node->record(
                                $class,
                                $node->role_system(),
                                [
                                    'filter' => [
                                        'name' => [
                                            'operator' => '===',
                                            'value' => $import->name
                                        ],
                                        'host' => [
                                            'operator' => '===',
                                            'value' => $import->host
                                        ]
                                    ]
                                ]
                            );
                        } else {
                            $record = $node->record(
                                $class,
                                $node->role_system(),
                                [
                                    'filter' => [
                                        'name' => [
                                            'operator' => '===',
                                            'value' => $import->name
                                        ]
                                    ]
                                ]
                            );
                        }
                        if(!$record){
                            unset($import->uuid);
                            $node->create(
                                $class,
                                $node->role_system(),
                                $import,
                                []
                            );
                        }
                        elseif(
                            property_exists($options, 'force') &&
                            is_array($record) &&
                            array_key_exists('node', $record) &&
                            property_exists($record['node'], 'uuid')
                        ){
                            $import->uuid = $record['node']->uuid;
                            $node->put(
                                $class,
                                $node->role_system(),
                                $import,
                                []
                            );
                        }
                        elseif(
                            property_exists($options, 'patch') &&
                            is_array($record) &&
                            array_key_exists('node', $record) &&
                            property_exists($record['node'], 'uuid')
                        ){
                            $import->uuid = $record['node']->uuid;
                            $node->patch(
                                $class,
                                $node->role_system(),
                                $import,
                                []
                            );
                        }
                    }
                }
            }
        }
        /*
        if(
            $package->has('inst') &&
            is_array($package->get('copy'))
        ){
        */
        $command = '{{binary()}} cache:clear';
        $parse = new Parse($object, $object->data());
        $command = $parse->compile($command, $object->data());
        Core::execute($object, $command, $output);
        if($output){
            echo $output;
        }
        // add to installation, but cannot do it here, node isn't yet installed
//        $command = '{{binary()}} r3m_io/node create -class=System.Installation -name=' . $key . ' -ctime=' . time() . ' -mtime=' . time();
        echo 'Press ctrl-c to stop the installation...' . PHP_EOL;
        $command_options = [];
        foreach($options as $option => $value){
            if($value === false){
                $value = 'false';
            }
            elseif($value === true){
                $value = 'true';
            }
            elseif($value === null){
                $value = 'null';
            }
            if(
                in_array(
                    $value,
                    [
                        'false',
                        'true',
                        'null'
                    ],
                    true
                ) ||
                is_numeric($value)
            ){
                $command_options[] = '-' . $option . '=' . $value;
            } else {
                $command_options[] = '-' . $option . '=\'' . $value . '\'';
            }
        }
        if(
            $package->has('command') &&
            is_array($package->get('command'))
        ){
            foreach($package->get('command') as $command){
                if(!empty($command_options)){
                    $command .= ' ' . implode(' ', $command_options);
                }
                echo $command . PHP_EOL;
                Core::execute($object, $command, $output, $notification);
                if($output){
                    echo $output;
                }
                if($notification){
                    echo $notification;
                }
            }
        }
        elseif(
            $package->has('command') &&
            is_string($package->get('command'))
        ){
            $command = $package->get('command');
            if(!empty($command_options)){
                $command .= ' ' . implode(' ', $command_options);
            }
            echo $command . PHP_EOL;
            Core::execute($object, $command, $output, $notification);
            if($output){
                echo $output;
            }
            if($notification){
                echo $notification;
            }
        }
        Event::trigger($object, 'cli.install', [
            'key' => $key,
        ]);
    }
}