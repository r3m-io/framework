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
namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Parse\Token;

use R3m\Io\Node\Model\Node;

use Exception;

use R3m\Io\Exception\ObjectException;

class Config extends Data {
    const DIR = __DIR__ . '/';
    const NAME = 'Config';
    const OBJECT = 'System.Config';

    const MODE_INIT = 'init';
    const MODE_DEVELOPMENT = 'development';
    const MODE_PRODUCTION = 'production';
    const MODE_STAGING = 'staging';
    const MODE_TEST = 'test';
    const MODE_REPLICA = 'replica';
    const DATA = 'data';
    const VALUE_DATA = 'Data';

    const MIDDLEWARE = 'middleware';
    const VALUE_MIDDLEWARE = 'Middleware';
    const EVENT = 'event';
    const VALUE_EVENT = 'Event';
    const OUTPUT_FILTER = 'output.filter';
    const VALUE_OUTPUT_FILTER = 'Output/Filter';
    const PUBLIC = 'public';
    const VALUE_PUBLIC = 'Public';
    const DOMAIN = 'domain';
    const VALUE_DOMAIN = 'Domain';
    const HOST = 'host';
    const VALUE_HOST = 'Host';
    const TEMP = 'temp';
    const VALUE_TEMP = '/tmp/r3m/io/';
    const CACHE = 'cache';
    const VALUE_CACHE = 'Cache';
    const SOURCE = 'Source';
    const VALUE_SOURCE = 'src';
    const MOUNT = 'Mount';
    const VALUE_MOUNT = 'Mount';
    const ASSET = 'Asset';
    const VALUE_ASSET = 'Asset';
    const BACKUP = 'Backup';
    const VALUE_BACKUP = 'Backup';
    const BINARY = 'Binary';
    const VALUE_BINARY = 'Bin';
    const CLI = 'cli';
    const VALUE_CLI = 'Cli';
    const MODULE = 'module';
    const VALUE_MODULE = 'Module';
    const FRAMEWORK = 'framework';
    const VALUE_FRAMEWORK = 'r3m_io/framework';
    const ENVIRONMENT = 'environment';
    const VALUE_ENVIRONMENT = Config::MODE_INIT;
    const FUNCTION = 'function';
    const VALUE_FUNCTION = 'Function';
    const PLUGIN = 'plugin';
    const VALUE_PLUGIN = 'Plugin';
    const CONTROLLER = 'controller';
    const VALUE_CONTROLLER = 'Controller';
    const VALIDATE = 'validate';
    const VALUE_VALIDATE = 'Validate';
    const DS = 'ds';
    const VALUE_DS = DIRECTORY_SEPARATOR;
    const VIEW = 'view';
    const VALUE_VIEW = 'View';
    const MODEL = 'model';
    const VALUE_MODEL = 'Model';
    const COMPONENT = 'component';
    const VALUE_COMPONENT = 'Component';
    const PACKAGE = 'package';
    const VALUE_PACKAGE = 'Package';
    const INSTALLATION = 'installation';
    const VALUE_INSTALLATION = 'Installation';
    const ENTITY = 'entity';
    const VALUE_ENTITY = 'Entity';
    const REPOSITORY = 'repository';
    const VALUE_REPOSITORY = 'Repository';
    const SERVICE = 'service';
    const VALUE_SERVICE = 'Service';
    const NODE = 'node';
    const VALUE_NODE = 'Node';
    const TRANSLATION = 'translation';
    const VALUE_TRANSLATION = 'Translation';
    const LOCALHOST_EXTENSION = 'localhost.extension';
    const VALUE_LOCALHOST_EXTENSION = 'local';
    const LOG = 'log';
    const VALUE_LOG = 'Log';
    const EXECUTE = 'execute';
    const VALUE_EXECUTE = 'Execute';
    const VALIDATOR = 'validator';
    const VALUE_VALIDATOR = 'Validator';
    const ROUTE = 'Route.json';
    const CONFIG = 'Config.json';
    const DICTIONARY = 'dictionary';
    const DATA_PDO = 'pdo';
    const DATA_DIR_VENDOR = 'dir.vendor';
    const DATA_FRAMEWORK_VERSION = 'framework.version';
    const DATA_FRAMEWORK_BUILT = 'framework.built';
    const DATA_FRAMEWORK_DIR = 'framework.dir';
    const DATA_FRAMEWORK_DIR_ROOT = Config::DATA_FRAMEWORK_DIR . '.' . 'root';
    const DATA_FRAMEWORK_DIR_VENDOR = Config::DATA_FRAMEWORK_DIR . '.' . 'vendor';
    const DATA_FRAMEWORK_DIR_SOURCE = Config::DATA_FRAMEWORK_DIR . '.' . 'source';
    const DATA_FRAMEWORK_DIR_DATA = Config::DATA_FRAMEWORK_DIR . '.' . 'data';
    const DATA_FRAMEWORK_DIR_CLI = Config::DATA_FRAMEWORK_DIR . '.' . 'cli';
    const DATA_FRAMEWORK_DIR_CACHE = Config::DATA_FRAMEWORK_DIR . '.' . 'cache';
    const DATA_FRAMEWORK_DIR_TEMP = Config::DATA_FRAMEWORK_DIR . '.' . 'temp';
    const DATA_FRAMEWORK_DIR_MODULE = Config::DATA_FRAMEWORK_DIR . '.' . 'module';
    const DATA_FRAMEWORK_DIR_PLUGIN =  Config::DATA_FRAMEWORK_DIR . '.' . 'plugin';
    const DATA_FRAMEWORK_DIR_FUNCTION =  Config::DATA_FRAMEWORK_DIR . '.' . 'function';
    const DATA_FRAMEWORK_DIR_VALIDATE =  Config::DATA_FRAMEWORK_DIR . '.' . 'validate';
    const DATA_FRAMEWORK_DIR_VALIDATOR =  Config::DATA_FRAMEWORK_DIR . '.' . 'validator';
    const DATA_FRAMEWORK_DIR_VIEW =  Config::DATA_FRAMEWORK_DIR . '.' . 'view';
    const DATA_FRAMEWORK_ENVIRONMENT = 'framework.environment';
    const DATA_HOST_DIR = 'host.dir';
    const DATA_HOST_DIR_ROOT = Config::DATA_HOST_DIR . '.' . 'root';
    const DATA_HOST_DIR_CACHE = Config::DATA_HOST_DIR . '.' . 'cache';
    const DATA_HOST_DIR_DATA = Config::DATA_HOST_DIR . '.' . 'data';
    const DATA_HOST_DIR_PUBLIC = Config::DATA_HOST_DIR . '.' . 'public';
    const DATA_HOST_DIR_PLUGIN = Config::DATA_HOST_DIR . '.' . 'plugin';
    const DATA_HOST_DIR_PLUGIN_2 = Config::DATA_HOST_DIR . '.' . 'plugin-2';
    const DATA_PARSE_DIR = 'parse.dir';
    const DATA_PARSE_DIR_TEMPLATE = Config::DATA_PARSE_DIR . '.' . 'template';
    const DATA_PARSE_DIR_COMPILE = Config::DATA_PARSE_DIR . '.' . 'compile';
    const DATA_PARSE_DIR_CACHE = Config::DATA_PARSE_DIR . '.' . 'cache';
    const DATA_PARSE_DIR_PLUGIN = Config::DATA_PARSE_DIR . '.' . 'plugin';
    const DATA_PROJECT_ROUTE_FILENAME = 'project.route.filename';
    const DATA_PROJECT_ROUTE_URL = 'project.route.url';
    const DATA_PROJECT_DIR = 'project.dir';
    const DATA_PROJECT_DIR_ROOT =  Config::DATA_PROJECT_DIR . '.' . 'root';
    const DATA_PROJECT_DIR_BINARY =  Config::DATA_PROJECT_DIR . '.' . 'binary';
    const DATA_PROJECT_DIR_VENDOR =  Config::DATA_PROJECT_DIR . '.' . 'vendor';
    const DATA_PROJECT_DIR_SOURCE =  Config::DATA_PROJECT_DIR . '.' . 'source';
    const DATA_PROJECT_DIR_MOUNT =  Config::DATA_PROJECT_DIR . '.' . 'mount';
    const DATA_PROJECT_DIR_ASSET =  Config::DATA_PROJECT_DIR . '.' . 'asset';
    const DATA_PROJECT_DIR_BACKUP =  Config::DATA_PROJECT_DIR . '.' . 'backup';
    const DATA_PROJECT_DIR_DATA =  Config::DATA_PROJECT_DIR . '.' . 'data';
    const DATA_PROJECT_DIR_NODE =  Config::DATA_PROJECT_DIR . '.' . 'node';
    const DATA_PROJECT_DIR_EVENT =  Config::DATA_PROJECT_DIR . '.' . 'event';
    const DATA_PROJECT_DIR_MIDDLEWARE =  Config::DATA_PROJECT_DIR . '.' . 'middleware';
    const DATA_PROJECT_DIR_OUTPUT_FILTER =  Config::DATA_PROJECT_DIR . '.' . 'output.filter';
    const DATA_PROJECT_DIR_PACKAGE =  Config::DATA_PROJECT_DIR . '.' . 'package';
    const DATA_PROJECT_DIR_CLI =  Config::DATA_PROJECT_DIR . '.' . 'cli';
    const DATA_PROJECT_DIR_PUBLIC =  Config::DATA_PROJECT_DIR . '.' . 'public';
    const DATA_PROJECT_DIR_HOST =  Config::DATA_PROJECT_DIR . '.' . 'host';
    const DATA_PROJECT_DIR_DOMAIN =  Config::DATA_PROJECT_DIR . '.' . 'domain';
    const DATA_PROJECT_DIR_PLUGIN =  Config::DATA_PROJECT_DIR . '.' . 'plugin';
    const DATA_PROJECT_DIR_FUNCTION =  Config::DATA_PROJECT_DIR . '.' . 'function';
    const DATA_PROJECT_DIR_LOG =  Config::DATA_PROJECT_DIR . '.' . 'log';
    const DATA_PROJECT_DIR_EXECUTE =  Config::DATA_PROJECT_DIR . '.' . 'execute';
    const DATA_PROJECT_DIR_COMPONENT =  Config::DATA_PROJECT_DIR . '.' . 'component';
    const DATA_PROJECT_DIR_VALIDATOR =  Config::DATA_PROJECT_DIR . '.' . 'validator';
    const DATA_PROJECT_VOLUME = 'project.volume';
    const DATA_CONTROLLER = 'controller';
    const DATA_CONTROLLER_CLASS = 'controller.class';
    const DATA_CONTROLLER_NAME = 'controller.name';
    const DATA_CONTROLLER_TITLE = 'controller.title';
    const DATA_CONTROLLER_DIR = 'controller.dir';
    const DATA_CONTROLLER_DIR_ROOT = Config::DATA_CONTROLLER_DIR . '.' .'root';
    const DATA_CONTROLLER_DIR_SOURCE = Config::DATA_CONTROLLER_DIR . '.' .'source';
    const DATA_CONTROLLER_DIR_DATA = Config::DATA_CONTROLLER_DIR . '.' .'data';
    const DATA_CONTROLLER_DIR_PLUGIN = Config::DATA_CONTROLLER_DIR . '.' .'plugin';
    const DATA_CONTROLLER_DIR_FUNCTION = Config::DATA_CONTROLLER_DIR . '.' .'function';
    const DATA_CONTROLLER_DIR_MODEL = Config::DATA_CONTROLLER_DIR . '.' .'model';
    const DATA_CONTROLLER_DIR_ENTITY = Config::DATA_CONTROLLER_DIR . '.' .'entity';
    const DATA_CONTROLLER_DIR_REPOSITORY = Config::DATA_CONTROLLER_DIR . '.' .'repository';
    const DATA_CONTROLLER_DIR_VALIDATOR = Config::DATA_CONTROLLER_DIR . '.' .'validator';
    const DATA_CONTROLLER_DIR_SERVICE = Config::DATA_CONTROLLER_DIR . '.' .'service';
    const DATA_CONTROLLER_DIR_NODE = Config::DATA_CONTROLLER_DIR . '.' .'node';
    const DATA_CONTROLLER_DIR_VIEW = Config::DATA_CONTROLLER_DIR . '.' .'view';
    const DATA_CONTROLLER_DIR_COMPONENT = Config::DATA_CONTROLLER_DIR . '.' .'component';
    const DATA_CONTROLLER_DIR_PUBLIC = Config::DATA_CONTROLLER_DIR . '.' .'public';
    const DATA_ROUTE = 'route';
    const DATA_ROUTE_PREFIX = Config::DATA_ROUTE . '.' . 'prefix';
    const POSIX_ID = 'posix.id';

    CONST WWW_DATA_DIR = 33;

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public function __construct($config=[]){
        if(
            is_array($config) &&
            array_key_exists(Config::DATA_DIR_VENDOR, $config)
        ){
            $this->data(Config::DATA_PROJECT_DIR_VENDOR, $config[Config::DATA_DIR_VENDOR]);
            $this->data(Config::DATA_PROJECT_DIR_ROOT, dirname($this->data(Config::DATA_PROJECT_DIR_VENDOR)) . '/');
            unset($config[Config::DATA_DIR_VENDOR]);
        }
        if(is_object($config)){
            $this->data($config);
        } else {
            $this->default();
            $url = $this->data(Config::DATA_FRAMEWORK_DIR_DATA) . Config::CONFIG;
            if(File::exist($url)){
                $read = Core::object(File::read($url));
                $this->data(Core::object_merge($this->data(), $read));
            }
            foreach($config as $attribute => $value){
                $this->data($attribute, $value);
            }
        }
        $id = posix_geteuid();
        $this->data(Config::POSIX_ID, $id);
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function volume(App $object): void
    {
        $config = $object->data(App::CONFIG);
        $volume_url_production = $config->data(Config::DATA_PROJECT_DIR_ROOT) . 'Volume.Production.' . $config->data('extension.json');
        $volume_url_staging = $config->data(Config::DATA_PROJECT_DIR_ROOT) . 'Volume.Staging.' . $config->data('extension.json');
        $volume_url_development = $config->data(Config::DATA_PROJECT_DIR_ROOT) . 'Volume.Development.' . $config->data('extension.json');
        $volume_url = $config->data(Config::DATA_PROJECT_DIR_ROOT) . 'Volume' . $config->data('extension.json');
        if(File::exist($volume_url_production)){
            $volume_url = $volume_url_production;
        }
        elseif(File::exist($volume_url_staging)){
            $volume_url = $volume_url_staging;
        }
        elseif(File::exist($volume_url_development)){
            $volume_url = $volume_url_development;
        }
        $volume = $object->data_read($volume_url);
        if($volume){
            $config->data(Config::DATA_PROJECT_VOLUME, $volume->data('volume'));
            $key = Config::DATA_PROJECT_DIR_ASSET;
            $value = $volume->data('volume.dir.asset');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_BACKUP;
            $value = $volume->data('volume.dir.backup');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_COMPONENT;
            $value = $volume->data('volume.dir.component');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_CLI;
            $value = $volume->data('volume.dir.cli');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_DATA;
            $value = $volume->data('volume.dir.data');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_DOMAIN;
            $value = $volume->data('volume.dir.domain');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_EVENT;
            $value = $volume->data('volume.dir.event');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_HOST;
            $value = $volume->data('volume.dir.host');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_LOG;
            $value = $volume->data('volume.dir.log');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_MIDDLEWARE;
            $value = $volume->data('volume.dir.middleware');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_MOUNT;
            $value = $volume->data('volume.dir.mount');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_NODE;
            $value = $volume->data('volume.dir.node');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_OUTPUT_FILTER;
            $value = $volume->data('volume.dir.output.filter');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_PACKAGE;
            $value = $volume->data('volume.dir.package');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_SOURCE;
            $value = $volume->data('volume.dir.source');
            if($value){
                $config->data($key, $value);
            }
            $key = Config::DATA_PROJECT_DIR_VALIDATOR;
            $value = $volume->data('volume.dir.validator');
            if($value){
                $config->data($key, $value);
            }
        }
    }

    /**
     * @throws ObjectException
     */
    public static function prepare(App $object): void
    {
//        Config::volume($object);
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function configure(App $object): void
    {
        Config::volume($object);
        $node = new Node($object);
        $class = Config::OBJECT;
        $dir_cache = false;
        if($object->config(Config::POSIX_ID) === 0){
            $dir_temp = $object->config('framework.dir.temp');
            $dir =
                $dir_temp .
                $object->config('posix.id') .
                $object->config('ds')
            ;
            if(!Dir::is($dir)) {
                Dir::Create($dir);
            }
            $dir_www = $dir_temp .
                Config::WWW_DATA_DIR .
                $object->config('ds')
            ;
            $dir_cache =
                $dir .
                'Cache' .
                $object->config('ds')
            ;
            if(!Dir::is($dir_www)){
                Dir::create($dir_www, Dir::CHMOD);
                File::permission($object, [
                    'dir_www' => $dir_www
                ]);
            }
            if(!Dir::is($dir_cache)){
                Dir::create($dir_cache, Dir::CHMOD);
            }
        }
        elseif($object->config('posix.id') === Config::WWW_DATA_DIR){
            $dir_temp = $object->config('framework.dir.temp');
            $dir =
                $dir_temp .
                $object->config('posix.id') .
                $object->config('ds')
            ;
            $dir_cache =
                $dir .
                'Cache' .
                $object->config('ds')
            ;
            if(!Dir::is($dir_cache)){
                Dir::create($dir_cache, Dir::CHMOD);
            }
        } else {
            throw new Exception('Posix id not allowed: ' . $object->config('posix.id') . ' for ' . $object->config('framework.dir.temp'));
        }
        $options = [
            'relation' => true,
            'ramdisk' => true,
            'ramdisk_dir' => $dir_cache,
        ];
        $role_system = $node->role_system();
        if(!$role_system){
            return;
        }
        if(!$node->role_has_permission($role_system, 'System:Config:record')){
            return;
        }
        $response = $node->record($class, $role_system, $options);
        if(
            $response &&
            array_key_exists('node', $response)
        ){
            $object->config(Core::object_merge($object->config(), $response['node']));
        }
//        $dir = $object->config('project.volume.dir.data') . 'App' . $object->config('ds');
//        $object->config('app.config.dir', $dir);
//        $object->config('app.route.url', $object->config('app.config.dir') . 'Route' . $object->config('extension.json'));
//        $object->config('app.secret.url', $object->config('app.config.dir') . 'Secret' . $object->config('extension.json'));
    }

    /**
     * @throws Exception
     */
    public static function parameters(App $object, $parameters=[]): array
    {
        if(empty($parameters)){
            return $parameters;
        }
        if(!is_array($parameters)){
            return [];
        }
        $uuid = Core::uuid();
        foreach($parameters as $nr => $parameter){
            $parameter = str_replace(
                [
                    '{',
                    '}',
                ],
                [
                    '[$ldelim-' . $uuid . ']',
                    '[$rdelim-' . $uuid . ']',
                ],
                $parameter
            );
            $parameter = str_replace(
                [
                    '[$ldelim-' . $uuid . ']',
                    '[$rdelim-' . $uuid . ']',
                ],
                [
                    '{$ldelim}',
                    '{$rdelim}',
                ],
                $parameter
            );
            $parameter = str_replace(
                [
                    '{$ldelim}{$ldelim}',
                    '{$rdelim}{$rdelim}',
                ],
                [
                    '{',
                    '}',
                ],
                $parameter
            );
            $parameters[$nr] = $parameter;
        }
        foreach($parameters as $key => $parameter){
            $tree = Parse\Token::tree($parameter);
            if(
                !empty($tree) &&
                is_array($tree)
            ){
                $parameters[$key]  = '';
                foreach($tree as $tree_nr => $record){
                    if(
                        $record['type'] === Token::TYPE_METHOD &&
                        array_key_exists('method', $record) &&
                        array_key_exists('attribute', $record['method'])
                    ){
                        foreach($record['method']['attribute'] as $attribute_nr => $attribute_list){
                            foreach($attribute_list as $attribute){
                                $value = $object->config($attribute['execute']);
                                if(
                                    is_object($value) ||
                                    is_array($value)
                                ){
                                    if(empty($parameters[$key])){
                                        $parameters[$key] = [];
                                    }
                                    $parameters[$key][] = $object->config($attribute['execute']);
                                } else {
                                    $parameters[$key] .= $object->config($attribute['execute']);
                                }

                            }
                        }
                    }
                    elseif($record['type'] === Token::TYPE_STRING){
                        $parameters[$key] .= $record['value'];
                    }
                }
            }
        }
        foreach($parameters as $key => $sublist){
            if(is_array($sublist)){
                $count = count($sublist);
                if($count === 1){
                    $parameters[$key] = reset($sublist);
                } else {
                    ddd($parameters);
                }
            }
        }
        return $parameters;
    }

    /**
     * @throws Exception
     */
    public function default(): void
    {
        $key = Config::DICTIONARY . '.' . Config::DATA;
        $value = Config::VALUE_DATA;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::SOURCE;
        $value = Config::VALUE_SOURCE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::MOUNT;
        $value = Config::VALUE_MOUNT;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::ASSET;
        $value = Config::VALUE_ASSET;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::BACKUP;
        $value = Config::VALUE_BACKUP;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::BINARY;
        $value = Config::VALUE_BINARY;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::TEMP;
        $value = Config::VALUE_TEMP;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::CACHE;
        $value = Config::VALUE_CACHE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::PUBLIC;
        $value = Config::VALUE_PUBLIC;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::MODULE;
        $value = Config::VALUE_MODULE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::CLI;
        $value = Config::VALUE_CLI;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::HOST;
        $value = Config::VALUE_HOST;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::DOMAIN;
        $value = Config::VALUE_DOMAIN;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::LOG;
        $value = Config::VALUE_LOG;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::EXECUTE;
        $value = Config::VALUE_EXECUTE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::COMPONENT;
        $value = Config::VALUE_COMPONENT;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::PACKAGE;
        $value = Config::VALUE_PACKAGE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::INSTALLATION;
        $value = Config::VALUE_INSTALLATION;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::SERVICE;
        $value = Config::VALUE_SERVICE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::ENTITY;
        $value = Config::VALUE_ENTITY;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::REPOSITORY;
        $value = Config::VALUE_REPOSITORY;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::VIEW;
        $value = Config::VALUE_VIEW;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::MODEL;
        $value = Config::VALUE_MODEL;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::CONTROLLER;
        $value = Config::VALUE_CONTROLLER;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::FRAMEWORK;
        $value = Config::VALUE_FRAMEWORK;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::ENVIRONMENT;
        $value = Config::VALUE_ENVIRONMENT;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::FUNCTION;
        $value = Config::VALUE_FUNCTION;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::PLUGIN;
        $value = Config::VALUE_PLUGIN;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::VALIDATE;
        $value = Config::VALUE_VALIDATE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::VALIDATOR;
        $value = Config::VALUE_VALIDATOR;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::DS;
        $value = Config::VALUE_DS;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::TRANSLATION;
        $value = Config::VALUE_TRANSLATION;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::NODE;
        $value = Config::VALUE_NODE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::EVENT;
        $value = Config::VALUE_EVENT;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::MIDDLEWARE;
        $value = Config::VALUE_MIDDLEWARE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::OUTPUT_FILTER;
        $value = Config::VALUE_OUTPUT_FILTER;
        $this->data($key, $value);

        $value = Config::VALUE_DS;
        $key = Config::DS;
        $this->data($key, $value);
        $this->data(Config::LOCALHOST_EXTENSION, Config::VALUE_LOCALHOST_EXTENSION);

        $key = Config::DATA_PROJECT_DIR_SOURCE;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::SOURCE) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_MOUNT;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::MOUNT) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_ASSET;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::ASSET) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_BACKUP;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_MOUNT) .
            $this->data(Config::DICTIONARY . '.' . Config::BACKUP) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_BINARY;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::BINARY) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_DATA;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::DATA) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_NODE;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::NODE) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);
        $key = Config::DATA_PROJECT_DIR_EVENT;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::EVENT) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);
        $key = Config::DATA_PROJECT_DIR_MIDDLEWARE;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::MIDDLEWARE) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);
        $key = Config::DATA_PROJECT_DIR_OUTPUT_FILTER;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::OUTPUT_FILTER) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);
        $key = Config::DATA_PROJECT_DIR_PACKAGE;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::PACKAGE) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_CLI;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::CLI) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_PUBLIC;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::PUBLIC) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_HOST;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::HOST) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_DOMAIN;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::DOMAIN) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_LOG;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::LOG) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);
        $key = Config::DATA_PROJECT_DIR_COMPONENT;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::COMPONENT) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);
        $key = Config::DATA_PROJECT_DIR_VALIDATOR;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::VALIDATOR) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_ROUTE_FILENAME;
        $value = Config::ROUTE;
        $this->data($key, $value);

        //project.route.url can be configured in index / cli

        $dir = Dir::name(Config::DIR);
        $key = Config::DATA_FRAMEWORK_DIR_ROOT;
        $value = $dir;
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_SOURCE;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::SOURCE) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_DATA;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::DATA) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_TEMP;
        $value =
            $this->data(Config::DICTIONARY . '.' . Config::TEMP)
        ;
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_CACHE;
        $value =
            $this->data(Config::DICTIONARY . '.' . Config::TEMP) .
            $this->data(Config::DICTIONARY . '.' . Config::CACHE) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_MODULE;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::MODULE) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_CLI;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::CLI) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_VALIDATE;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::VALIDATE) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);
        $key = Config::DATA_FRAMEWORK_DIR_VALIDATOR;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::VALIDATOR) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_VIEW;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::VIEW) .
            $this->data(Config::DS)
        ;
        $this->data($key, $value);
        $key = Config::DATA_FRAMEWORK_ENVIRONMENT;
        $value = $this->data(Config::DICTIONARY . '.' . Config::ENVIRONMENT);
        $this->data($key, $value);
    }
}