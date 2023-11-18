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

use R3m\Io\Exception\LocateException;


class FileRequest {
    const REQUEST = 'Request';

    private static function location(App $object, $dir): array
    {
        $location = [];
        $explode = explode('/', $dir);
        $controller = array_shift($explode);
        $view = $explode;
        array_unshift($explode, 'Public');
        if (!empty($controller)) {
            array_unshift($explode, $controller);
        }
        array_unshift($view, 'Public');
        $view_2 = $view;
        array_unshift($view, 'View');
        if (!empty($controller)) {
            array_unshift($view, $controller);
            array_unshift($view_2, $controller);
        }
        array_unshift($view_2, 'View');
        $location[] = $object->config('host.dir.root') .
            rtrim(implode($object->config('ds'), $view), '/') .
            $object->config('ds')
        ;
        $location[] = $object->config('host.dir.root') .
            rtrim(implode($object->config('ds'), $view_2), '/') .
            $object->config('ds')
        ;
        $location[] = $object->config('host.dir.root') .
            rtrim(implode($object->config('ds'), $explode), '/') .
            $object->config('ds')
        ;
        $location[] = $object->config('host.dir.root') .
            $dir .
            'Public' .
            $object->config('ds')
        ;
        $explode = explode('/', $dir);
        array_pop($explode);
        $type = array_pop($explode);
        array_push($explode, '');
        $dir_type = implode('/', $explode);
        array_pop($explode);
        $dir_swap = array_pop($explode);
        $dir_type_swap = false;
        if(!empty($explode)){
            array_push($explode, '');
            $dir_type_swap = implode('/', $explode);
        }
        $type_swap = $dir_swap . '/' . $type;
        if ($type) {
            $location[] = $object->config('host.dir.root') .
                $dir_type .
                'Public' .
                $object->config('ds') .
                $type .
                $object->config('ds')
            ;
        }
        $location[] = $object->config('host.dir.root') .
            'View' .
            $object->config('ds') .
            $dir .
            'Public' .
            $object->config('ds')
        ;
        if ($type) {
            $location[] = $object->config('host.dir.root') .
                'View' .
                $object->config('ds') .
                $dir_type .
                'Public' .
                $object->config('ds') .
                $type .
                $object->config('ds')
            ;
        }
        if(
            $dir_type_swap &&
            $type_swap
        ){
            $location[] = $object->config('host.dir.root') .
                $dir_type_swap .
                'Public' .
                $object->config('ds') .
                $type_swap .
                $object->config('ds')
            ;
            $location[] = $object->config('host.dir.root') .
                'View' .
                $object->config('ds') .
                $dir_type_swap .
                'Public' .
                $object->config('ds') .
                $type_swap .
                $object->config('ds')
            ;
        }
        $location[] = $object->config('host.dir.public') .
            $dir;
        $location[] = $object->config('project.dir.public') .
            $dir;
        return $location;
    }

    /*
    public static function local(App $object){
        $fileRequest = $object->config('server.fileRequest');
        if(empty($fileRequest)){
            return false;
        }
        if(!is_object($fileRequest)){
            return false;
        }
        foreach($fileRequest as $name => $node){
            $explode = explode('-', $name, 3);
            $count = count($explode);
            if($count > 1){
                $explode[$count - 1] = 'local';
                $name = implode('-', $explode);
                $fileRequest->{$name} = $node;
            }
        }
    }
    */

    /**
     * @throws LocateException
     * @throws Exception
     */
    public static function get(App $object)
    {
        if (
            array_key_exists('REQUEST_METHOD', $_SERVER) &&
            $_SERVER['REQUEST_METHOD'] == 'OPTIONS'
        ) {
            Server::cors($object);
        }
        if (
            $object->config('server.http.upgrade_insecure') === true &&
            array_key_exists('REQUEST_SCHEME', $_SERVER) &&
            array_key_exists('REQUEST_URI', $_SERVER) &&
            $_SERVER['REQUEST_SCHEME'] === Host::SCHEME_HTTP &&
            $object->config('framework.environment') !== Config::MODE_DEVELOPMENT &&
            Host::isIp4Address() === false
        ) {
            $subdomain = Host::subdomain();
            $domain = Host::domain();
            $extension = Host::extension();
            if ($subdomain) {
                $url = Host::SCHEME_HTTPS . '://' . $subdomain . '.' . $domain . '.' . $extension . $_SERVER['REQUEST_URI'];
            } else {
                $url = Host::SCHEME_HTTPS . '://' . $domain . '.' . $extension . $_SERVER['REQUEST_URI'];
            }
            Core::redirect($url);
        }
        $logger = $object->config('project.log.fileRequest');
        if(empty($logger)){
            $logger = $object->config('project.log.name');
        }
        $request = $object->data(App::REQUEST);
        $input = $request->data('request');
        $dir = str_replace(['../', '..'], '', Dir::name($input));
        $file = str_replace($dir, '', $input);
        if (
            (
                substr($file, 0, 3) === '%7B' &&
                substr($file, -3, 3) === '%7D'
            ) ||
            (
                substr($file, 0, 1) === '[' &&
                substr($file, -1, 1) === ']'
            )
        ) {
            return false;
        }
        $file_extension = File::extension($file);
        if (empty($file_extension)) {
            return false;
        }
        $subdomain = Host::subdomain();
        $domain = Host::domain();
        $extension = Host::extension();
        $node = new Node($object);
        $host = false;
        $start = microtime(true);

        if($subdomain){
            $source = $subdomain . '.' . $domain . '.' . $extension;
        } else {
            $source = $domain . '.' . $extension;
        }
        $cache_key = Cache::key($object, [
            'name' => Cache::name($object, [
                'type' => Cache::FILE,
                'extension' => $object->config('extension.json'),
                'name' => 'Host.Mapper.' . $source,
            ]),
            'ttl' => Cache::ONE_MINUTE,
        ]);
        $map = Cache::read(
            $object,
            [
                'key' => $cache_key,
                'ttl' => Cache::INF,
            ]
        );
        $map = Core::object($map, Core::OBJECT_OBJECT);
        d($map);
        if(!$map){
            $map = $node->record(
                'System.Host.Mapper',
                $node->role_system(),
                [
                    'sort' => [
                        'source' => 'ASC',
                        'destination' => 'ASC'
                    ],
                    'filter' => [
                        'source' => $source
                    ],
                    'ttl' => Cache::TEN_MINUTES,
                    'ramdisk' => true
                ]
            );
            Cache::write(
                $object,
                [
                    'key' => $cache_key,
                    'data' => Core::object($map, Core::OBJECT_JSON)
                ]
            );
        }
        ddd($map);
        if(
            array_key_exists('node', $map) &&
            property_exists($map['node'], 'destination')
        ){
            $name = $map['node']->destination;
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
            $duration = microtime(true) - $start;
            d($duration * 1000);
            ddd($host);
        }






//        FileRequest::local($object);
        if($subdomain){
            $fileRequest = $object->config('server.fileRequest.' .
                $subdomain .
                '-' .
                $domain .
                '-' .
                $extension
            );
        } else {
            $fileRequest = $object->config('server.fileRequest.' .
                $domain .
                '-' .
                $extension
            );
        }
        ddd($fileRequest);
        if(empty($fileRequest)){
            $location = FileRequest::location($object, $dir);
            ddd($location);
        } else {

            if($subdomain){
                $attribute = $subdomain . '-' . $domain . '-' . $extension . '.location';
            } else {
                $attribute = $domain . '-' . $extension. '.location';
            }
            /*
            $location = $data->get($attribute);
            if(empty($location)){
                $location = $data->get('location');
            }
            if(empty($location)){
                $location = FileRequest::location($object, $dir);
            }
            */
        }
        $ram_dir = false;
        $ram_url = false;
        $ram_maxsize = false;
        $file_mtime = false;
        $file_mtime_url = false;
        $file_mtime_dir = false;
        if(
            $object->config('ramdisk.url') &&
            empty($object->config('ramdisk.is.disabled'))
        ){
            $file_mtime_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'File' .
                $object->config('ds')
            ;
            $file_mtime_url = $file_mtime_dir .
                'File.Mtime' .
                $object->config('extension.json')
            ;
            $file_mtime = $object->data_read($file_mtime_url, sha1($file_mtime_url));
            if(empty($file_mtime)){
                $file_mtime = new Data();
            }
            $ram_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'File' .
                $object->config('ds')
            ;
            $ram_url = $ram_dir;
            if($subdomain){
                $ram_url .= $subdomain . '_';
            }
            if(
                $object->config('cache.fileRequest.url.directory_length') &&
                $object->config('cache.fileRequest.url.directory_separator') &&
                $object->config('cache.fileRequest.url.directory_pop_or_shift') &&
                $object->config('cache.fileRequest.url.name_length') &&
                $object->config('cache.fileRequest.url.name_separator') &&
                $object->config('cache.fileRequest.url.name_pop_or_shift')
            ){
                $ram_url .= $domain .
                    '_' .
                    $extension .
                    '_' .
                    Autoload::name_reducer(
                        $object,
                        str_replace('/', '_', $dir),
                        $object->config('cache.fileRequest.url.directory_length'),
                        $object->config('cache.fileRequest.url.directory_separator'),
                        $object->config('cache.fileRequest.url.directory_pop_or_shift')
                    ) .
                    '_' .
                    Autoload::name_reducer(
                        $object,
                        $file,
                        $object->config('cache.fileRequest.url.name_length'),
                        $object->config('cache.fileRequest.url.name_separator'),
                        $object->config('cache.fileRequest.url.name_pop_or_shift')
                    )
                ;
            }
        }
        $is_ram_url = false;
        ddd($location);
        foreach($location as $url){
            if(substr($url, -1, 1) !== $object->config('ds')){
                $url .= $object->config('ds');
            }
            $url .= $file;
            if(
                $is_ram_url === false &&
                $ram_url !== false &&
                File::exist($ram_url) &&
                File::mtime($file_mtime->get(sha1($ram_url))) === File::mtime($ram_url)
            ){
                $is_ram_url = $ram_url;
                $url = $ram_url;
            }
            if(
                $is_ram_url ||
                File::exist($url)
            ){
                $etag = sha1($url);
                $mtime = File::mtime($url);
                $contentType = $object->config('contentType.' . $file_extension);
                if(empty($contentType)){
                    if($logger){
                        $object->logger($logger)->info('HTTP/1.0 415 Unsupported Media Type', [ $file, $file_extension]);
                    }
                    Handler::header('HTTP/1.0 415 Unsupported Media Type', 415);
                    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                        $json = [];
                        $json['message'] = 'HTTP/1.0 415 Unsupported Media Type';
                        $json['file'] = $file;
                        $json['extension'] = $file_extension;
                        $json['available'] = $object->config('contentType');
                        echo Core::object($json, Core::OBJECT_JSON);
                    }
                    exit();
                }
                if(!headers_sent()){
                    Handler::header("HTTP/1.1 200 OK");
                    $gm = gmdate('D, d M Y H:i:s T', $mtime);
                    Handler::header('Last-Modified: '. $gm);
                    Handler::header('Content-Type: ' . $contentType);
                    Handler::header('ETag: ' . $etag . '-' . $gm);
                    Handler::header('Cache-Control: public');
                    if(array_key_exists('HTTP_ORIGIN', $_SERVER)){
                        $origin = $_SERVER['HTTP_ORIGIN'];
                        if(Server::cors_is_allowed($object, $origin)){
                            header("Access-Control-Allow-Origin: {$origin}");
                        } elseif($logger){
                            $object->logger($logger)->debug('Cors is not allowed for: ', [ $origin ]);
                        }
                    }
                    elseif(array_key_exists('HTTP_REFERER', $_SERVER)){
                        $origin = $_SERVER['HTTP_REFERER'];
                        $origin = explode('://', $origin, 2);
                        if(array_key_exists(1, $origin)){
                            $explode = explode('/', $origin[1], 2);    //bugfix samsung browser ?
                            $origin = $origin[0] . '://' . $explode[0];
                        } else {
                            if($logger){
                                $object->logger($logger)->error('Wrong HTTP_REFERER', [ $origin ]);
                            }
                            exit();
                        }
                        if(Server::cors_is_allowed($object, $origin)){
                            header("Access-Control-Allow-Origin: {$origin}");
                        }
                        elseif($logger){
                            $object->logger($logger)->info('Cors is not allowed for: ', [ $origin ]);
                        }
                    }
                    elseif($logger){
                        $object->logger($logger)->info('No HTTP_REFERER & HTTP_ORIGIN');
                    }
                }
                elseif($logger) {
                    $object->logger($logger)->info('Headers sent');
                }
                if($logger){
                    $object->logger($logger)->info('Url:', [ $url ]);
                }
                $to_ramdisk = false;
                $read = File::read($url);
                if($is_ram_url){
                    return $read;
                }
                $size = File::size($url);
                $ram_maxsize = $object->config('ramdisk.file.size.max');
                if(
                    !empty($ram_maxsize) &&
                    $size > $ram_maxsize
                ){
                    return $read;
                }
                $file_extension_allow = $object->config('ramdisk.file.extension.allow');
                $file_extension_deny = $object->config('ramdisk.file.extension.deny');
                if(
                    empty($file_extension_allow) &&
                    empty($file_extension_deny)
                ){
                    $to_ramdisk = true;
                }
                elseif(
                    empty($file_extension_allow) &&
                    !empty($file_extension_deny) &&
                    is_array($file_extension_deny)
                ){
                    if(in_array('*', $file_extension_deny, true)){
                        return $read;
                    }
                    elseif(in_array($file_extension, $file_extension_deny, true)){
                        return $read;
                    } else {
                        $to_ramdisk = true;
                    }
                }
                elseif(
                    !empty($file_extension_allow) &&
                    empty($file_extension_deny) &&
                    is_array($file_extension_allow)
                ){
                    if(in_array('*', $file_extension_allow, true)){
                        $to_ramdisk = true;
                    }
                    elseif(in_array($file_extension, $file_extension_allow, true)){
                        $to_ramdisk = true;
                    } else {
                        return $read;
                    }
                }
                elseif(
                    !empty($file_extension_allow) &&
                    !empty($file_extension_deny) &&
                    is_array($file_extension_allow) &&
                    is_array($file_extension_deny)
                ){
                    if(in_array('*', $file_extension_deny, true)){
                        return $read;
                    }
                    elseif(in_array($file_extension, $file_extension_deny, true)){
                        return $read;
                    } else {
                        if(in_array('*', $file_extension_allow, true)){
                            $to_ramdisk = true;
                        }
                        elseif(in_array($file_extension, $file_extension_allow, true)){
                            $to_ramdisk = true;
                        } else {
                            return $read;
                        }
                    }
                }
                if(
                    $to_ramdisk &&
                    $is_ram_url === false &&
                    $ram_dir &&
                    $ram_url
                ){
                    //copy to ramdisk
                    Dir::create($ram_dir);
                    if(File::exist($ram_url)){
                        File::remove($ram_url);
                    }
                    File::copy($url, $ram_url);
                    File::touch($ram_url, filemtime($url));
                    if($file_mtime && $file_mtime_url){
                        $file_mtime->set(sha1($ram_url), $url);
                        $file_mtime->write($file_mtime_url);
                    }
                    $command = 'chmod 640 ' . $ram_url;
                    exec($command);
                    $command = 'chmod 640 ' . $file_mtime_url;
                    exec($command);
                }
                return $read;
            }
        }
        if($logger){
            $object->logger($logger)->error('File doesn\'t exists', [ $url ]);
        }
        Handler::header('HTTP/1.0 404 Not Found', 404);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            if(is_array($location)){
                foreach ($location as $key => $value){
                    $location[$key] .= $file;
                }
            }
            throw new LocateException('Cannot find location for file...', $location);
        } else {
            if(
                in_array(
                    $extension,
                    $object->config('error.extension.tpl')
                )
            ){
                if($object->config('server.http.error.404')){
                    //let's parse this tpl
                    $data = new Data();
                    $data->set('file', $file);
                    $data->set('extension', $extension);
                    $data->set('location', $location);
                    $contentType = $object->config('contentType.' . $extension);
                    $data->set('contentType', $contentType);
                    $parse = new Parse($object, $data);
                    $compile = $parse->compile(File::read($parse->compile($object->config('server.http.error.404'), $data->get())), $data->get());
                    echo $compile;
                }
            }
            elseif(
                in_array(
                    $extension,
                    $object->config('error.extension.text')
                )
            ){
                if($object->config('server.http.error.404')){
                    echo "HTTP/1.0 404 Not Found: " . $file . PHP_EOL;
                }
            }
            elseif(
                in_array(
                    $extension,
                    $object->config('error.extension.js')
                )
            ){
                if($object->config('server.http.error.404')){
                    echo 'console.error("HTTP/1.0 404 Not Found",  "' . $file . '");';
                }
            }
            elseif(
                in_array(
                    $extension,
                    $object->config('error.extension.json')
                )
            ){
                $contentType = 'application/json';
                Handler::header('Content-Type: ' . $contentType, null, true);
                echo '{
    "file" : "' . $file . '",
    "extension" : "' . $extension . '",
    "contentType" : "' . $contentType . '",
    "message" : "Error: cannot find file."
}';
            }
        }
        if($logger){
            $object->logger($logger)->error('HTTP/1.0 404 Not Found', $location);
        }
        exit();
    }
}