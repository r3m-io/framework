<?php
/**
 * @author          Remco van der Velde
 * @since           19-01-2023
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

class Server {

    /**
     * @throws Exception
     */
    public static function url(App $object, $name=''): ?string
    {
        $name = str_replace('.', '-', $name);
        return $object->config('server.url.' . $name . '.' . $object->config('framework.environment'));
    }

    /**
     * @throws Exception
     */
    public static function cors(App $object): void
    {
        $logger_security = $object->config(' project.log.security');
        $logger = false;
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            $logger = $object->config(' project.log.debug');
        }
        header("HTTP/1.1 200 OK");
        if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
            $origin = $_SERVER['HTTP_ORIGIN'];
            if($logger){
                $object->logger($logger)->debug('HTTP_ORIGIN: ', [ $origin]);
            }
            if(Server::cors_is_allowed($object, $origin)){
                $allow_credential = $object->config('server.cors.allow_credential');
                if($allow_credential === true){
                    header('Access-Control-Allow-Credentials: true');
                }
                header("Access-Control-Allow-Origin: {$origin}");
            }
        }
        if (
            array_key_exists('REQUEST_METHOD', $_SERVER) &&
            $_SERVER['REQUEST_METHOD'] === 'OPTIONS'
        ) {
            $allow_credential = $object->config('server.cors.allow_credential');
            if($allow_credential === true){
                header('Access-Control-Allow-Credentials: true');
            }
            $methods = $object->config('server.cors.method');
            if(
                $methods &&
                is_array($methods)
            ){
                header('Access-Control-Allow-Methods: ' . implode(', ', $methods));
            }
            $allow = $object->config('server.cors.header.allow');
            if(
                $allow &&
                is_array($allow)
            ){
                header('Access-Control-Allow-Headers: ' . implode(', ', $allow));
            } else {
                header('Access-Control-Allow-Headers: Origin, Cache-Control, Content-Type, Authorization, X-Requested-With');
            }
            $expose = $object->config('server.cors.header.expose');
            if(
                $expose &&
                is_array($expose)
            ){
                header('Access-Control-Expose-Headers: ' . implode(', ', $expose));
            } else {
                header('Access-Control-Expose-Headers: Cache-Control, Content-Language, Content-Length, Content-Type, Expires, Last-Modified, Pragma');
            }
            $max_age = $object->config('server.cors.max-age');
            if(
                $max_age &&
                is_int($max_age)
            ){
                header('Access-Control-Max-Age: ' . $max_age);
            } else {
                header('Access-Control-Max-Age: 86400');    // cache for 1 day
            }
            if($logger_security){
                $object->logger($logger_security)->info('Request: ', [ $_SERVER['REQUEST_METHOD'], Core::object($object->request(), Core::OBJECT_ARRAY) ]);
            }
            elseif($logger){
                $object->logger($logger)->info('Request: ', [ $_SERVER['REQUEST_METHOD'], Core::object($object->request(), Core::OBJECT_ARRAY) ]);
            }
            exit(0);
        }
        if(array_key_exists('REQUEST_METHOD', $_SERVER)){
            if($logger){
                $object->logger($logger)->debug('REQUEST_METHOD: ', [ $_SERVER['REQUEST_METHOD'] ]);
            }
        }
        if($logger){
            $object->logger($logger)->debug('REQUEST: ', [ Core::object($object->request(), Core::OBJECT_ARRAY) ]);
        }
    }

    /**
     * @throws Exception
     */
    public static function cors_is_allowed(App $object, $origin=''): bool
    {
        $logger_security = $object->config('project.log.security');
        $logger = false;
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            $logger = $object->config('project.log.debug');
        }
        $origin = rtrim($origin, '/');
        $origin = explode('://', $origin);
        if(array_key_exists(1, $origin)){
            $origin = $origin[1];
            $explode = explode('/', $origin);    //origin can be a css resource
            $origin = $explode[0];
        } else {
            return false;
        }
        d($origin);
        d($object->config('host'));
        d($object->config('domain'));
        $host_list = $object->config('server.cors.domain');
        if(is_array($host_list)){
            foreach($host_list as $host){
                $explode = explode('.', $host);
                $local = $explode;
                $count_explode = count($explode);
                if($count_explode === 3){
                    $local[2] = Core::LOCAL;
                    if($explode[0] === '*'){
                        $temp = explode('.', $origin);
                        if(count($temp) === 3){
                            $explode[0] = '';
                            $temp[0] = '';
                            $host = implode('.', $explode);
                            $match = implode('.', $temp);
                            if($host === $match){
                                return true;
                            }
                            $local[0] = '';
                            $host = implode('.', $local);
                            if($host === $match){
                                return true;
                            }
                        }
                    } else {
                        if($host === $origin){
                            return true;
                        }
                        $host = implode('.', $local);
                        if($host === $origin){
                            return true;
                        }
                    }
                }
                elseif($count_explode === 2){
                    $local[1] = Core::LOCAL;
                    if($host === $origin){
                        return true;
                    }
                    $host = implode('.', $local);
                    if($host === $origin){
                        return true;
                    }
                }
                elseif($count_explode === 1){
                    if($host === '*'){
                        return true;
                    }
                }
            }
        }
        if($logger_security){
            $object->logger($logger_security)->notice('Cors rejected for origin: ' . $origin);
        }
        elseif($logger){
            $object->logger($logger)->debug('Cors rejected for origin: ' . $origin);
        }
        return false;
    }

    public static function token(): ?string
    {
        if(array_key_exists('HTTP_AUTHORIZATION', $_SERVER)){
            $explode = explode('Bearer ', $_SERVER['HTTP_AUTHORIZATION'], 2);
            if(array_key_exists(1, $explode)){
                return $explode[1];
            }
        }
    }
}