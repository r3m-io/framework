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
namespace R3m\Io\Module;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\System\Node;

use Exception;

use R3m\Io\Exception\DirectoryCreateException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

class Host {
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';

    /**
     * @throws \Exception
     */
    public static function configure(App $object): bool
    {
        if(defined('IS_CLI')){
            return false;
        }
        $key = 'host.url';
        $value = Host::url();
        $object->config($key, $value);
        $key = 'host.scheme';
        $value = Host::scheme();
        $object->config($key, $value);
        $key = 'host.extension';
        $value = Host::extension();
        $object->config($key, $value);
        $key = 'host.domain';
        $value = Host::domain();
        $object->config($key, $value);
        $key = 'host.subdomain';
        $subdomain = Host::subdomain();
        $object->config($key, $subdomain);
        $key = 'host.port';
        $port = Host::port();
        $object->config($key, $port);

//        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        $key = 'host.dir.root';
        if(empty($subdomain)){
            $sentence = Core::ucfirst_sentence(
                $object->config('host.domain') .
                $object->config('ds') .
                $object->config('host.extension') .
                $object->config('ds'),
                $object->config('ds')
            );
            $sentence = ltrim($sentence, $object->config('ds'));
            $value = $object->config('project.dir.host') .
                $sentence;
        } else {
            $sentence = Core::ucfirst_sentence(
                $object->config('host.subdomain') .
                $object->config('ds') .
                $object->config('host.domain') .
                $object->config('ds') .
                $object->config('host.extension') .
                $object->config('ds'),
                $object->config('ds')
            );
            $sentence = ltrim($sentence, $object->config('ds'));
            $value = $object->config('project.dir.host') .
                $sentence;
        }
        $object->config($key, $value);
        $key = 'host.dir.data';
        $value =
            $object->config('host.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::DATA) .
            $object->config('ds');
        $value_with_port = $value;
        if(!in_array($port, $object->config('server.default.port'))){
            $value_with_port .= $port . $object->config('ds');
        }
        if(File::exist($value_with_port)){
            $value = $value_with_port;
        }
        $object->config($key, $value);
        $key = 'host.dir.cache';
        $value =
            $object->config('framework.dir.temp') .
            $object->config(Config::DICTIONARY . '.' . Config::HOST) .
            $object->config('ds');
        $object->config($key, $value);
        $key = 'host.dir.public';
        $value =
            $object->config('host.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::PUBLIC) .
            $object->config('ds');
        $value_with_port = $value;
        if(!in_array($port, $object->config('server.default.port'))){
            $value_with_port .= $port . $object->config('ds');
        }
        if(File::exist($value_with_port)){
            $value = $value_with_port;
        }
        $object->config($key, $value);
        $key = 'host.dir.source';
        $value =
            $object->config('host.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::SOURCE) .
            $object->config('ds');
        $value_with_port = $value;
        if(!in_array($port, $object->config('server.default.port'))){
            $value_with_port .= $port . $object->config('ds');
        }
        if(File::exist($value_with_port)){
            $value = $value_with_port;
        }
        $object->config($key, $value);
        $key = 'host.dir.view';
        $value =
            $object->config('host.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::VIEW) .
            $object->config('ds');
        $value_with_port = $value;
        if(!in_array($port, $object->config('server.default.port'))){
            $value_with_port .= $port . $object->config('ds');
        }
        if(File::exist($value_with_port)){
            $value = $value_with_port;
        }
        $object->config($key, $value);
        return true;
    }

    public static function url($include_scheme = true): string
    {
        if(isset($_SERVER['HTTP_HOST'])){
            $domain = $_SERVER['HTTP_HOST'];
        }
        elseif(isset($_SERVER['SERVER_NAME'])){
            $domain = $_SERVER['SERVER_NAME'];
        } else {
            $domain = '';
        }
        if($include_scheme) {
            $scheme = Host::scheme();
            $host = '';
            if(isset($scheme) && isset($domain)){
                $host = $scheme . '://' . $domain . '/';
            }
        } else {
            $host = $domain;
        }
        return $host;
    }

    public static function domain($host=''): bool|string|null
    {
        if(empty($host)){
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }
        }
        if(empty($host)){
            return false;
        }
        $explode = explode('.', $host);
        if(count($explode) >= 2){
            array_pop($explode);
            return strtolower(array_pop($explode));
        }
        return false;
    }

    public static function subdomain($host=''): bool|string
    {
        if(empty($host)){
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }
        }
        if(empty($host)){
            return false;
        }
        $explode = explode('.', $host);
        if(count($explode) > 2){
            array_pop($explode);
            array_pop($explode);
            return strtolower(implode('.', $explode));
        }
        return false;
    }

    public static function port($host=''): bool|int
    {
        if(empty($host)){
            if(isset($_SERVER['SERVER_PORT'])) {
                return (int) $_SERVER['SERVER_PORT'];
            }
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }
        }
        if(empty($host)){
            return false;
        }
        $explode = explode(':', $host);
        if(count($explode) >= 2){
            $string = array_pop($explode);
            $test = explode('?', $string);
            return (int) $test[0];
        }
        return false;
    }

    public static function extension($host=''): bool|string
    {
        if(empty($host)){
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }
        }
        if(empty($host)){
            return false;
        }
        $host = explode(':', $host, 2);
        if(array_key_exists(1, $host)){
            array_pop($host);
        }
        $host = implode(':', $host);
        $explode = explode('.', $host);
        if(count($explode) > 1){
            return strtolower(array_pop($explode));
        }
        return false;
    }

    public static function remove_port($url=''): string
    {
        $explode = explode(':', $url, 3);
        if(isset($explode[2])){
            array_pop($explode);
            return implode(':', $explode);
        }
        return '';
    }

    public static function remove_scheme($url=''): string
    {
        $explode = explode('://', $url, 2);
        if(isset($explode[1])){
            if(substr($explode[1], -1, 1) == '/'){
                return substr($explode[1], 0, -1);
            }
            return $explode[1];
        }
        return '';
    }

    public static function scheme(): string
    {
        $scheme = Host::SCHEME_HTTP;
        if(!empty($_SERVER['REQUEST_SCHEME'])){
            $scheme = $_SERVER['REQUEST_SCHEME'];
        } else {
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
                $scheme = Host::SCHEME_HTTPS;
            }
        }
        return $scheme;
    }

    public static function isIp4Address(): bool
    {
        $subdomain = Host::subdomain();
        $domain = Host::domain();
        $extension = Host::extension();
        $explode = explode('.', $subdomain);
        foreach($explode as $possibility){
            if(!intval($possibility) > 0){
                return false;
            }
        }
        if(!intval($domain) > 0){
            return false;
        }
        if(!intval($extension) > 0){
            return false;
        }
        return true;
    }

    /**
     * @throws ObjectException
     * @throws DirectoryCreateException
     * @throws FileWriteException
     * @throws Exception
     */
    public static function map(App $object, Node $node, $name){
        $cache_key = Cache::key($object, [
            'name' => Cache::name($object, [
                'type' => Cache::FILE,
                'extension' => $object->config('extension.json'),
                'name' => 'Host.Mapper.' . $name,
            ]),
            'ttl' => Cache::ONE_MINUTE,
        ]);
        $map = Cache::read(
            $object,
            [
                'key' => $cache_key,
                'ttl' => Cache::ONE_MINUTE,
            ]
        );
        if($map === 'false'){
            return [];
        }
        if($map){
            $map = (array) Core::object($map, Core::OBJECT_OBJECT);
        } else {
            $map = $node->record(
                'System.Host.Mapper',
                $node->role_system(),
                [
                    'sort' => [
                        'source' => 'ASC',
                        'destination' => 'ASC'
                    ],
                    'filter' => [
                        'source' => $name
                    ],
                    'ttl' => Cache::TEN_MINUTES,
                    'ramdisk' => true
                ]
            );
            if(empty($map)){
                Cache::write(
                    $object,
                    [
                        'key' => $cache_key,
                        'data' => 'false'
                    ]
                );
            } else {
                Cache::write(
                    $object,
                    [
                        'key' => $cache_key,
                        'data' => Core::object($map, Core::OBJECT_JSON)
                    ]
                );
            }

        }
        return $map;
    }

    /**
     * @throws ObjectException
     * @throws DirectoryCreateException
     * @throws FileWriteException
     * @throws Exception
     */
    public static function get(App $object, Node $node, $name, $map=[]){
        $host = false;
        if(empty($name)){
            return false;
        }
        if(
            empty($map) ||
            (
                array_key_exists('node', $map) &&
                empty($map['node'])
            )
        ) {
            $cache_key = Cache::key($object, [
                'name' => Cache::name($object, [
                    'type' => Cache::FILE,
                    'extension' => $object->config('extension.json'),
                    'name' => 'Host.' . $name,
                ]),
                'ttl' => Cache::ONE_MINUTE,
            ]);
            $host = Cache::read(
                $object,
                [
                    'key' => $cache_key,
                    'ttl' => Cache::ONE_MINUTE,
                ]
            );
            if ($host) {
                $host = (array) Core::object($host, Core::OBJECT_OBJECT);
            } else {
                $host = $node->record(
                    'System.Host',
                    $node->role_system(),
                    [
                        'sort' => [
                            'name' => 'ASC',
                        ],
                        'filter' => [
                            'name' => $name
                        ],
                        'ttl' => Cache::TEN_MINUTES,
                        'ramdisk' => true
                    ]
                );
                Cache::write(
                    $object,
                    [
                        'key' => $cache_key,
                        'data' => Core::object($host, Core::OBJECT_JSON)
                    ]
                );
            }
        }
        elseif(
            array_key_exists('node', $map) &&
            !empty($map['node']) &&
            property_exists($map['node'], 'destination') &&
            !empty($map['node']->destination)
        ) {
            $name = $map['node']->destination;
            $cache_key = Cache::key($object, [
                'name' => Cache::name($object, [
                    'type' => Cache::FILE,
                    'extension' => $object->config('extension.json'),
                    'name' => 'Host.' . $name,
                ]),
                'ttl' => Cache::ONE_MINUTE,
            ]);
            $host = Cache::read(
                $object,
                [
                    'key' => $cache_key,
                    'ttl' => Cache::ONE_MINUTE,
                ]
            );
            if ($host) {
                $host = (array) Core::object($host, Core::OBJECT_OBJECT);
            } else {
                $host = $node->record(
                    'System.Host',
                    $node->role_system(),
                    [
                        'sort' => [
                            'name' => 'ASC',
                        ],
                        'filter' => [
                            'name' => $name
                        ],
                        'ttl' => Cache::TEN_MINUTES,
                        'ramdisk' => true
                    ]
                );
                Cache::write(
                    $object,
                    [
                        'key' => $cache_key,
                        'data' => Core::object($host, Core::OBJECT_JSON)
                    ]
                );
            }
        }
        return $host;
    }
}