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

use stdClass;
use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Node\Model\Node;

use Exception;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\UrlEmptyException;

class Route extends Data {
    const NAMESPACE = __NAMESPACE__;
    const NAME = 'Route';
    const OBJECT = 'System.Route';
    const SELECT = 'Route_select';
    const SELECT_DEFAULT = 'info';
    const SELECT_WILDCARD = '*';

    private $current;
    private $url;
    private $cache_url;

    public function url($url=null): ?string
    {
        if($url !== null){
            $this->url = $url;
        }
        return $this->url;
    }

    public function cache_url($url=null): ?string
    {
        if($url !== null){
            $this->cache_url = $url;
        }
        return $this->cache_url;
    }

    public function current(Destination $current=null): ?Destination
    {
        if($current !== null){
            $this->setCurrent($current);
        }
        return $this->getCurrent();
    }

    private function setCurrent(Destination $current=null): void
    {
        $this->current = $current;
    }

    private function getCurrent(): ?Destination
    {
        return $this->current;
    }

    public static function has_host($select='', $url=''): bool | object
    {
        $url = Host::remove_scheme($url);
        $allowed_host = [];
        $disallowed_host = [];
        if(property_exists($select, 'host')){
            foreach($select->host as $host){
                $host = strtolower($host);
                if(substr($host, 0, 1) == '!'){
                    $disallowed_host[] = substr($host, 1);
                    continue;
                }
                $allowed_host[] = $host;
            }
            if(in_array($url, $disallowed_host, true)){
                return false;
            }
            if(in_array($url, $allowed_host, true)){
                return $select;
            }
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function find(App $object, $name='', $option=[]): bool | string
    {
        if($name === null){
            return false;
        }
        $logger = false;
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            $logger = $object->logger($object->config('project.log.debug'));
        }
        $logger_error = $object->config('project.log.error');
        $route = $object->data(App::ROUTE);
        $get = $route->data($name);
        if(empty($get)){
            if($logger){
                $object->logger($logger)->debug('route:find.url:', [false]);
            }
            return false;
        }
        if(!property_exists($get, 'path')){
            if(property_exists($get, 'url')){
                if($logger){
                    $object->logger($logger)->debug('route:find.url:', [$get->url]);
                }
                return $get->url;
            } else {
                if($logger_error){
                    $object->logger($logger_error)->error('path & url are empty');
                }
                throw new Exception('path & url are empty');
            }
        }
//        $get = $route::add_localhost($object, $get);
        if(!empty($object->config('host.name'))  && property_exists($get, 'host')){
            $host = explode(':', $object->config('host.name'), 3);
            if(array_key_exists(2, $host)){
                array_pop($host);
            }
            $host = implode(':', $host);
            $get = $route::has_host($get, $host);
        }
        if(empty($get)){
            return false;
        }
        $get->path = str_replace([
            '{{',
            '}}',
        ], [
            '{',
            '}'
        ], $get->path);
        $path = $get->path;
        if(is_array($option)){
            if(
                empty($option) &&
                stristr($path, '{$') !== false
            ){
                if($logger_error){
                    $object->logger($logger_error)->error('path has variable & option is empty');
                }
                throw new Exception('path has variable & option is empty');
            }
            $old_path = $get->path;
            foreach($option as $key => $value){
                if(is_numeric($key)){
                    $explode = explode('}', $get->path, 2);
                    $temp = explode('{$', $explode[0], 2);
                    if(array_key_exists(1, $temp)){
                        $variable = $temp[1];
                        $path = str_replace('{$' . $variable . '}', $value, $path);
                        $get->path = str_replace('{$' . $variable . '}', '', $get->path);
                    }
                } else {
                    $path = str_replace('{$' . $key . '}', $value, $path);
                    $get->path = str_replace('{$' . $key . '}', '', $get->path);
                }
            }
            $get->path = $old_path;
        }
        if($path == '/'){
            $url = $object->config('host.name');
        } else {
            $url = $object->config('host.name') . $path;
        }
        if($logger){
            $object->logger($logger)->debug('route:find.url:', [$url]);
        }
        return $url;
    }

    private static function input_request(App $object, object $input, $glue='/'): object
    {
        $request = [];
        foreach($input as $key => $value){
            $request[] = $value;
        }
        $input->request = implode($glue, $request);
        if(substr($input->request, -1, 1) != $glue){
            $input->request .= $glue;
        }
        return $input;
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    private static function add_request(App $object, $request): void
    {
        if(empty($request)){
            return;
        }
        if(
            property_exists($request, 'request') &&
            get_class($request->request) === stdClass::class
        ){
            $object->request(
                Core::object_merge(
                    $object->request(),
                    $request->request
                )
            );
        } elseif(
            property_exists($request, 'request')
        ) {
            $object->request(
                Core::object_merge(
                    $object->request(),
                    $request->request->data()
                )
            );
        }
    }

    /**
     * @throws Exception
     */
    private static function route_select_info($object, $record): object
    {
        $select = (object) [] ;
        $select->parameter = (object) [];
        $select->attribute = [];
        $select->method = Handler::method();
        $select->host = [];
        $select->attribute[] = Route::SELECT_DEFAULT;
        $key = 0;
        $select->parameter->{$key} =  Route::SELECT_DEFAULT;
        foreach($record->parameter as $key => $value){
            $select->parameter->{$key + 1} = $value;
        }
        return $select;
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function wildcard(App $object): bool | Destination
    {
        if(defined('IS_CLI')){

        } else {
            $route =  $object->data(App::ROUTE);
            $request = $route->data(Route::SELECT_WILDCARD);
            if(property_exists($request, 'request')){
                $request->request = new Data($request->request);
            } else {
                $request->request = new Data();
            }
            Route::add_request($object, $request);
            return $route->current(new Destination($request));
        }
        return false;
    }

    private static function find_array($string=''): array
    {
        $split = str_split($string);
        $is_array = false;
        $is_quote_double = false;
        $previous_char = false;
        $collection = '';
        $array = [];
        foreach($split as $nr => $char){
            if(
                $previous_char === '/' &&
                $char === '[' &&
                $is_quote_double === false
            ){
                $is_array = true;
            }
            elseif(
                $char === ']' &&
                $is_quote_double === false
            ){
                if($is_array){
                    $array[] = $collection;
                    $collection = '';
                }
                $is_array = false;
            }
            elseif(
                $char === '"' &&
                $previous_char !== '\\'
            ){
                $is_quote_double = !$is_quote_double;
            }
            if($is_array){
                $collection .= $char;
            }
            $previous_char = $char;
        }
        return $array;
    }

    private static function request_explode($input=''): array
    {
        $split = str_split($input);
        $is_quote_double = false;
        $collection = '';
        $explode = [];
        $previous_char = false;
        foreach($split as $nr => $char){
            if(
                $previous_char === '/' &&
                $char === '{' &&
                $is_quote_double === false
            ){
                if(!empty($collection)){
                    $value = substr($collection, 0,-1);
                    if(!empty($value)){
                        $explode[] = $value;
                    }
                }
                $collection = $char;
                continue;
            }
            elseif(
                $previous_char === '/' &&
                $char == '[' &&
                $is_quote_double === false
            ){
                if(!empty($collection)){
                    $value = substr($collection, 0,-1);
                    if(!empty($value)){
                        $explode[] = $value;
                    }
                }
                $collection = $char;
                continue;
            }
            elseif(
                $char === '"' &&
                $previous_char !== '\\'
            ){
                $is_quote_double = !$is_quote_double;
            }
            $collection .= $char;
            $previous_char = $char;
        }
        if(!empty($collection)){
            if($previous_char === '/'){
                $value = substr($collection, 0,-1);
                if(!empty($value)){
                    $explode[] = $value;
                }
            } else {
                $explode[] = $collection;
            }
        }
        return $explode;
    }

    /**
     * @throws Exception
     */
    public static function navigate(App $object, $name='', $options=[]): bool | Destination
    {
        $route = $object->data(App::ROUTE);
        $get = $route->data($name);
        if(empty($get)){
            return false;
        }
        if(
            !property_exists($get, 'path') ||
            empty($get->path)
        ){
            if(
                property_exists($get, 'url') &&
                !empty($get->url)
            ){
                return $get->url;
            } else {
                throw new Exception('path & url are empty');
            }
        }
        if(property_exists($get, 'request')){
            $get->request = new Data($get->request);
        } else {
            $get->request = new Data();
        }
        if(
            !empty($object->config('host.name'))  &&
            property_exists($get, 'host') &&
            !empty($get->host)
        ){
            $host = explode(':', $object->config('host.name'), 3);
            if(array_key_exists(2, $host)){
                array_pop($host);
            }
            $host = implode(':', $host);
            $get = $route::has_host($get, $host);
        }
        if(empty($get)){
            return false;
        }
        $get->path = str_replace([
            '{{',
            '}}',
        ], [
            '{',
            '}'
        ], $get->path);
        $path = $get->path;
        if(is_array($options)){
            if(
                empty($options) &&
                stristr($path, '{$') !== false
            ){
                throw new Exception('path has variable & option is empty');
            }
            $old_path = $get->path;
            foreach($options as $key => $value){
                if(is_numeric($key)){
                    $explode = explode('}', $get->path, 2);
                    $temp = explode('{$', $explode[0], 2);
                    if(array_key_exists(1, $temp)){
                        $variable = $temp[1];
                        $path = str_replace('{$' . $variable . '}', $value, $path);
                        $get->request->set($variable, $value);
                        $get->path = str_replace('{$' . $variable . '}', '', $get->path);
                    }
                } else {
                    $path = str_replace('{$' . $key . '}', $value, $path);
                    $get->request->set($key, $value);
                    $get->path = str_replace('{$' . $key . '}', '', $get->path);
                }
            }
            $get->path = $old_path;
        }
        $get = Route::controller($get);
        Route::add_request($object, $get);
        return $route->current(new Destination($get));
    }

    /**
     * @throws UrlEmptyException
     * @throws ObjectException
     * @throws Exception
     */
    public static function request(App $object): bool | Destination
    {
        $logger_error = $object->config('project.log.error');
        if(defined('IS_CLI')){
            $input = Route::input($object);
            $select = new stdClass();
            $select->parameter = $input->data();
            $key = 0;
            if(property_exists($select->parameter, $key)){
                $select->attribute = explode($object->config('ds'), $select->parameter->{$key});
            } else {
                $select->attribute = [];
                $select->attribute[] = '';
            }
            $select->method = Handler::method();
            $select->host = [];
            $request = Route::route_select_cli($object, $select);
            if($request === false){
                $select = Route::route_select_info($object, $select);
                $request = Route::route_select_cli($object, $select);
            }
            if($request === false){
                if($logger_error){
                    $object->logger($logger_error)->error('Exception in request');
                }
                throw new Exception('Exception in request');
            }
            $request->request->data(Core::object_merge(clone $select->parameter, $request->request->data()));
            $route =  $object->data(App::ROUTE);
            Route::add_request($object, $request);
            return $route->current(new Destination($request));
        } else {
            Route::upgrade_insecure($object);
            $input = Route::input($object);
            if(substr($input->data('request'), -1) != '/'){
                $input->data('request', $input->data('request') . '/');
            }
            $select = new stdClass();
            $select->input = $input;
            $test = Route::request_explode(urldecode($input->data('request')));
            $test_count = count($test);
            if($test_count > 1){
                $select->attribute = explode('/', $test[0]);
                if(end($select->attribute) === ''){
                    array_pop($select->attribute);
                }
                $array = [];
                for($i=1; $i < $test_count; $i++){
                    $array[] = $test[$i];
                }
                $select->attribute = array_merge($select->attribute, $array);
                $select->deep = count($select->attribute);
            } else {
                $string_count = $input->data('request');
                $select->deep = substr_count($string_count, '/');
                $select->attribute = explode('/', $input->data('request'));
                if(end($select->attribute) === ''){
                    array_pop($select->attribute);
                }
            }
            while(end($select->attribute) === ''){
                array_pop($select->attribute);
            }
            $select->method = Handler::method();
            $select->host = strtolower($object->config('host.name'));
            $request = Route::route_select($object, $select);
            $route =  $object->data(App::ROUTE);
            Route::add_request($object, $request);
            if($request){
                return $route->current(new Destination($request));
            }
            return false;
        }
    }

    /**
     * @throws UrlEmptyException
     * @throws Exception
     */
    public static function upgrade_insecure(App $object): void
    {
        if(
            Host::scheme() === Host::SCHEME_HTTP &&
            $object->config('server.http.upgrade_insecure') === true &&
            $object->config('framework.environment') !== Config::MODE_DEVELOPMENT &&
            Host::isIp4Address() === false
        ){
            $url = false;
            $subdomain = Host::subdomain();
            if($subdomain){
                $url = Host::SCHEME_HTTPS . '://' . $subdomain . '.' . Host::domain() . '.' . Host::extension();
            } else {
                $domain = Host::domain();
                if ($domain) {
                    $url = Host::SCHEME_HTTPS . '://' . Host::domain() . '.' . Host::extension();
                }
            }
            if($url) {
                Core::redirect($url);
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function input(App $object): Data
    {
        return $object->data(App::REQUEST);
    }

    /**
     * @throws Exception
     */
    private static function route_select_cli(App $object, object $select): bool | object
    {
        $route =  $object->data(App::ROUTE);
        if(empty($route)){
            return false;
        }
        $match = false;
        $data = $route->data();
        if(Core::object_is_empty($data)){
            return false;
        }
        if(!is_object($data)){
            return false;
        }
        $current = false;
        foreach($data as $name => $record){
            if(property_exists($record, 'resource')){
                continue;
            }
            $match = Route::is_match_cli($object, $record, $select);
            if($match === true){
                $current = $record;
                $current->name = $name;
                break;
            }
        }
        if($current !== false){
            $current = Route::prepare($object, $current, $select);
            $current->parameter = $select->parameter;
            return $current;
        }
        return false;
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    private static function selectWildcard(App $object, object $select): bool | object
    {
        $route =  $object->data(App::ROUTE);
        $match = false;
        $data = $route->data();
        if(empty($data)){
            return $select;
        }
        if(!is_object($data)){
            return $select;
        }
        $current = false;
        foreach($data as $record){
            if(property_exists($record, 'resource')){
                continue;
            }
            if(!property_exists($record, 'deep')){
                continue;
            }
            $match = Route::is_match_by_wildcard($object, $record, $select);
            if($match === true){
                $current = $record;
                break;
            }
        }
        if($match === false){
            foreach($data as $record){
                if(property_exists($record, 'resource')){
                    continue;
                }
                if(!property_exists($record, 'deep')){
                    continue;
                }
                $match = Route::is_match_by_wildcard_has_slash_in_attribute($object, $record, $select);
                if($match === true){
                    $current = $record;
                    break;
                }
            }
        }
        if($current !== false){
            if(property_exists($current, 'controller')){
                $current = Route::controller($current);
            }
            Route::add_request($object, $current);
            return $current;
        }
        return false;
    }

    private static function route_select(App $object, object $select): bool | object
    {
        $route =  $object->data(App::ROUTE);
        $match = false;
        $data = $route->data();
        if(empty($data)){
            return $select;
        }
        if(!is_object($data)){
            return $select;
        }
        $current = false;
        d($data);
        foreach($data as $name => $record){
            if(!is_object($record)){
                continue;
            }
            if(property_exists($record, 'resource')){
                continue;
            }
            if(!property_exists($record, 'deep')){
                continue;
            }
            $match = Route::is_match($object, $record, $select);
            d($match);
            if($match === true){
                $current = $record;
                $current->name = $name;
                break;
            }
        }
        if($match === false){
            foreach($data as $name => $record){
                if(property_exists($record, 'resource')){
                    continue;
                }
                if(!property_exists($record, 'deep')){
                    continue;
                }
                $match = Route::is_match_has_slash_in_attribute($object, $record, $select);
                if($match === true){
                    $current = $record;
                    $current->name = $name;
                    break;
                }
            }
        }
        if($current !== false){
            return Route::prepare($object, $current, $select);
        }
        return false;
    }

    private static function is_variable($string): bool
    {
        $string = trim($string);
        $string = str_replace([
            '{{',
            '}}'
        ], [
            '{',
            '}'
        ], $string);
        if(
            substr($string, 0, 2) == '{$' &&
            substr($string, -1) == '}'
        ){
            return true;
        }
        return false;
    }

    private static function get_variable($string): ?string
    {
        $string = trim($string);
        $string = str_replace([
            '{{',
            '}}'
        ], [
            '{',
            '}'
        ], $string);
        if(
            substr($string, 0, 2) == '{$' &&
            substr($string, -1) == '}'
        ){
            return substr($string, 2, -1);
        }
        return null;
    }

    /**
     * @throws Exception
     */
    private static function prepare(App $object, object $route, object $select): object
    {
        $route->path = str_replace([
            '{{',
            '}}'
        ], [
            '{',
            '}'
        ], $route->path);
        $explode = explode('/', $route->path);
        array_pop($explode);
        $attribute = $select->attribute;
        $nr = 0;
        if(property_exists($route, 'request')){
            $route->request = new Data($route->request);
        } else {
            $route->request = new Data();
        }
        foreach($explode as $nr => $part){
            if(Route::is_variable($part)){
                $get_attribute = Route::get_variable($part);
                $temp = explode(':', $get_attribute, 2);
                if(array_key_exists(1, $temp)){
                    $variable = $temp[0];
                    if(property_exists($route->request, $variable)){
                        continue;
                    }
                    if(array_key_exists($nr, $attribute)){
                        $type = ucfirst($temp[1]);
                        $className = '\\R3m\\Io\\Module\\Route\\Type' . $type;
                        $exist = class_exists($className);
                        if(
                            $exist &&
                            in_array('cast', get_class_methods($className), true)
                        ){
                            $value = $className::cast($object, urldecode($attribute[$nr]));
                        } else {
                            $value = urldecode($attribute[$nr]);
                        }
                        $route->request->data($variable, $value);
                    }
                } else {
                    $variable = $temp[0];
                    if(property_exists($route->request, $variable)){
                        continue;
                    }
                    if(array_key_exists($nr, $attribute)){
                        $value = urldecode($attribute[$nr]);
                        $route->request->data($variable, $value);
                    }
                }
            }
        }
        if(
            !empty($variable) &&
            count($attribute) > count($explode)
        ){
            $request = '';
            for($i = $nr; $i < count($attribute); $i++){
                $request .= $attribute[$i] . '/';
            }
            $request = substr($request, 0, -1);
            $request = urldecode($request);
            $route->request->data($variable, $request);
        }
        foreach($object->data(App::REQUEST) as $key => $record){
            if($key == 'request'){
                continue;
            }
            $route->request->data($key, $record);
        }
        if(property_exists($route, 'controller')){
            $route = Route::controller($route);
        }
        return $route;
    }

    public static function controller(object $route): object
    {
        if(property_exists($route, 'controller')){
            $is_double_colon = str_contains($route->controller, ':');
            if($is_double_colon){
                $controller = explode(':', $route->controller);
                if(array_key_exists(1, $controller)) {
                    $function = array_pop($controller);
                    $route->controller = str_replace('.', '_', implode('\\', $controller));
                    $function = str_replace('.', '_', $function);
                    $route->function = $function;
                }
            } else {
                $controller = explode('.', $route->controller);
                if(array_key_exists(1, $controller)) {
                    $function = array_pop($controller);
                    $route->controller = implode('\\', $controller);
                    $route->function = $function;
                }
            }
        }
        return $route;
    }

    private static function is_match_by_attribute(App $object, object $route, object $select): bool
    {
        if(!property_exists($route, 'path')){
            return false;
        }
        $explode = explode('/', $route->path);
        array_pop($explode);
        $attribute = $select->attribute;
        if(empty($attribute)){
            return true;
        }
        $path_attribute = [];
        foreach($explode as $nr => $part){
            if(Route::is_variable($part)){
                $variable = Route::get_variable($part);
                if($variable){
                    $temp = explode(':', $variable, 2);
                    if(array_key_exists(1, $temp)){
                        $path_attribute[$nr] = $temp[0];
                    }
                }
                continue;
            }
            if(array_key_exists($nr, $attribute) === false){
                return false;
            }
            d($part);
            d($attribute[$nr]);
            if(strtolower($part) != strtolower($attribute[$nr])){
                return false;
            }
        }
        if(!empty($path_attribute)){
            foreach($explode as $nr => $part){
                if(Route::is_variable($part)){
                    $variable = Route::get_variable($part);
                    if($variable){
                        $temp = explode(':', $variable, 2);
                        if(count($temp) === 2){
                            $attribute = $temp[0];
                            $type = ucfirst($temp[1]);
                            $className = '\\R3m\\Io\\Module\\Route\\Type' . $type;
                            $exist = class_exists($className);
                            if($exist){
                                $value = null;
                                foreach($path_attribute as $path_nr => $path_value){
                                    if(
                                        $path_value == $attribute &&
                                        array_key_exists($path_nr, $select->attribute)
                                    ){
                                        $value = $select->attribute[$path_nr];
                                        break;
                                    }
                                }
                                if($value){
                                    $validate = $className::validate($object, $value);
                                    if(!$validate){
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    private static function is_match_by_condition(App $object, object $route, object $select): bool
    {
        if(!property_exists($route, 'path')){
            return false;
        }
        $explode = explode('/', $route->path);
        array_pop($explode);
        $attribute = $select->attribute;
        if(empty($attribute)){
            return true;
        }
        foreach($explode as $nr => $part){
            if(Route::is_variable($part)){
                if(
                    property_exists($route, 'condition') &&
                    is_array($route->condition)
                ){
                    foreach($route->condition as $condition_nr => $value){
                        if(substr($value, 0, 1) == '!'){
                            //invalid conditions
                            if(strtolower(substr($value, 1)) == strtolower($attribute[$nr])){
                                return false;
                            }
                        } else {
                            //valid conditions
                            if(strtolower($value) == strtolower($attribute[$nr])){
                                return true;
                            }
                        }
                    }
                }
                continue;
            }
            if(array_key_exists($nr, $attribute) === false){
                return false;
            }
            if(strtolower($part) != strtolower($attribute[$nr])){
                return false;
            }
        }
        return true;
    }


    private static function is_match_by_method(App $object, object $route, object $select): bool
    {
        if(!property_exists($route, 'method')){
            return true;
        }
        if(!is_array($route->method)){
            return false;
        }
        foreach($route->method as $method){
            if(strtoupper($method) == strtoupper($select->method)){
                return true;
            }
        }
        return false;
    }

    private static function is_match_by_host(App $object, object $route, object $select): bool
    {
        if(!property_exists($route, 'host')){
            return true;
        }
        if(!is_string($route->host)){
            return false;
        }
        if(!is_string($select->host)){
            return false;
        }
        if($select->host === $route->host){
            return true;
        }
        return false;
    }

    private static function is_match_by_deep(App $object, object $route, object $select): bool
    {
        if(!property_exists($route, 'deep')){
            return false;
        }
        if(!property_exists($select, 'deep')){
            return false;
        }
        if($route->deep != $select->deep){
            return false;
        }
        return true;
    }

    private static function is_match_cli(App $object, object $route, object $select): bool
    {
        $is_match = Route::is_match_by_attribute($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    private static function is_match(App $object, object $route, object $select): bool
    {
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
//        $route = Route::add_localhost($object, $route);
        $is_match = Route::is_match_by_host($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_deep($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        d($route);
        d($select);

        $is_match = Route::is_match_by_attribute($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_condition($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    private static function is_match_has_slash_in_attribute(App $object, object $route, object $select): bool
    {
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
//        $route = Route::add_localhost($object, $route);
        $is_match = Route::is_match_by_host($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_attribute($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_condition($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    private static function is_match_by_wildcard(App $object, object $route, object $select): bool
    {
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_host($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    private static function is_match_by_wildcard_has_slash_in_attribute(App $object, object $route, object $select): bool
    {
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_host($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function configure(App $object): void
    {
        $route = $object->data(App::ROUTE);
        if(!$route){
            $route = new Route();
            $object->data(App::ROUTE, $route);
        }
        $host = strtolower($object->config('host.name'));
        if(empty($host) && Core::is_cli()){
            Route::framework($object);
            $node = new Node($object);
            $role_system = $node->role_system();
            $response = false;
            if($role_system){
                $response = $node->list(
                    Route::OBJECT,
                    $node->role_system(),
                    [
                        'filter' => [
                            'method' => 'CLI',
                        ],
                        'sort' => [
                            'options.priority' => 'ASC',
                            'name' => 'ASC',
                        ],
                        'limit' => '*',
                        'ramdisk' => true,
                        'output' => [
                            'filter' => [
                                "R3m:Io:Output:Filter:System:Route:list"
                            ]
                        ]

                    ]
                );
            }
            if(
                is_array($response) &&
                array_key_exists('list', $response ) &&
                (
                    is_array($response['list']) ||
                    is_object($response['list'])
                )
            ){
                foreach($response['list'] as $name => $record){
                    $record = Route::item_path($object, $record);
                    $record = Route::item_deep($object, $record);
                    if(is_object($response['list'])){
                        $response['list']->{$name} = $record;
                    }
                    elseif(is_array($response['list'])){
                        $response['list'][$name] = $record;
                    }
                }
                $route->data(Core::object_merge($route->data(), $response['list']));
                //maybe re-sort on priority-name
            }
            $object->data(App::ROUTE, $route);
        }
        elseif(!empty($host)) {
            $node = new Node($object);
            $response = $node->list(
                Route::OBJECT,
                $node->role_system(),
                [
                    'filter' => [
                        'host' => [
                            'value' => $host,
                            'operator' => '==='
                        ]
                    ],
                    'sort' => [
                        'options.priority' => 'ASC',
                        'name' => 'ASC',
                    ],
                    'limit' => '*',
                    'ramdisk' => true,
                    'output' => [
                        'filter' => [
                            "R3m:Io:Output:Filter:System:Route:list"
                        ]
                    ]
                ]
            );
            if(
                is_array($response) &&
                array_key_exists('list', $response)
            ){
                if(is_array($response['list'])){
                    foreach($response['list'] as $name => $record){
                        $record = Route::item_path($object, $record);
                        $record = Route::item_deep($object, $record);
                        $response['list'][$name] = $record;
                    }
                }
                elseif(is_object($response['list'])){
                    foreach($response['list'] as $name => $record){
                        $record = Route::item_path($object, $record);
                        $record = Route::item_deep($object, $record);
                        $response['list']->{$name} = $record;
                    }
                }
                $route->data($response['list']);
            }
            $object->data(App::ROUTE, $route);
        }
    }

    private static function cache_mtime(App $object, object $cache): ?bool
    {
        $time = strtotime(date('Y-m-d H:i:00'));
        if(File::mtime($cache->cache_url()) != $time){
            return File::touch($cache->cache_url(), $time, $time);
        }
        return null;
    }

    public static function item_path(App $object, object $item): object
    {
        if(!property_exists($item, 'path')){
            return $item;
        }
        if(substr($item->path, 0, 1) == '/'){
            $item->path = substr($item->path, 1);
        }
        if(substr($item->path, -1) !== '/'){
            $item->path .= '/';
        }
        return $item;
    }

    public static function item_deep(App $object, object $item): object
    {
        if(!property_exists($item, 'path')){
            $item->deep = 0;
            return $item;
        }
        $item->deep = substr_count($item->path, '/');
        return $item;
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function load(App $object): void
    {
        $logger_error = $object->config('project.log.error');
        $reload = false;
        $route = $object->data(App::ROUTE);
        if(empty($route)){
            return;
        }
        $data = $route->data();
        if(empty($data)){
            return;
        }
        foreach($data as $item){
            if(!is_object($item)){
                continue;
            }
            if(!property_exists($item, 'resource')){
                $item = Route::item_path($object, $item);
                $item = Route::item_deep($object, $item);
                continue;
            }
            if(property_exists($item, 'read')){
                continue;
            }
            $item->resource = Route::parse($object, $item->resource);
            if(File::exist($item->resource)){
                $read = File::read($item->resource);
                $resource = Core::object($read);
                if(Core::object_is_empty($resource)){
                    if($logger_error){
                        $object->logger($logger_error)->error('Could not read route file (' . $item->resource .')');
                    }
                    throw new Exception('Could not read route file (' . $item->resource .')');
                }
                foreach($resource as $resource_key => $resource_item){
                    $check = $route->data($resource_key);
                    if(empty($check)){
                        $route->data($resource_key, $resource_item);
                    }
                }
                $reload = true;
                $item->read = true;
                $item->mtime = File::mtime($item->resource);
            } else {
                $item->read = false;
            }
        }
        if($reload === true){
            Route::load($object);
        }
    }

    /**
     * @throws Exception
     */
    public static function framework(App $object): void
    {
        $route = $object->data(App::ROUTE);
        $default_route = $object->config('framework.default.route');
        $priority = 1000;
        if(is_array($default_route) || is_object($default_route)){
            foreach($default_route as $record){
                $path = strtolower($record);
                $attribute = strtolower(str_replace(['.', ':'], ['-','-'], $record));
                $control = Core::ucfirst_sentence($record,':');
                $attribute = 'r3m-io-cli-' . $attribute;
                $item = new stdClass();
                $item->path = $path . '/';
                $item->controller = 'R3m:Io:Cli:' . $control . ':Controller:' . $control . ':run';
                $item->request = (object) [
                    'language' => 'en'
                ];
                $item->method = [
                    "CLI"
                ];
                $item->deep = 1;
                $item->priority = $priority;
                $route->data($attribute, $item);
            }
        }
    }

    public static function parse(App $object, string $resource): string
    {
        $resource = str_replace([
            '{{',
            '}}'
        ], [
            '{',
            '}'
        ], $resource);
        $explode = explode('}', $resource, 2);
        if(!isset($explode[1])){
            return $resource;
        }
        $temp = explode('{', $explode[0], 2);
        if(isset($temp[1])){
            $attribute = substr($temp[1], 1);
            $config = $object->data(App::CONFIG);
            $value = $config->data($attribute);
            $resource = str_replace('{$' . $attribute . '}', $value, $resource);
            return Route::parse($object, $resource);
        } else {
            return $resource;
        }
    }
}