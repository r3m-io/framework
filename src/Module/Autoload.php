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


use R3m\Io\Exception\LocateException;
use stdClass;

use R3m\Io\App;
use R3m\Io\Config;

use Exception;

use R3m\Io\Exception\ObjectException;

class Autoload {
    const DIR = __DIR__;
    const FILE = 'Autoload.json';
    const FILE_PREFIX = 'Autoload.Prefix.json';
    const TEMP = 'Temp';
    const NAME = 'Autoload';
    const EXT_PHP = 'php';
    const EXT_TPL = 'tpl';
    const EXT_JSON = 'json';
    const EXT_CLASS_PHP = 'class.php';
    const EXT_TRAIT_PHP = 'trait.php';

    const TYPE_ERROR = 'error';

    protected $expose;
    protected $read;
    protected $fileList;
    protected $cache_dir;

    protected $object;

    public $prefixList = array();
    public $environment = 'init';
    public $is_init = false;

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function configure(App $object): void
    {
        $autoload = new Autoload();
        $autoload->object($object);
        if(
            empty($object->config('ramdisk.is.disabled')) &&
            !empty($object->config('ramdisk.url'))
        ) {
            $cache_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                Autoload::NAME .
                $object->config('ds');
            $url = $cache_dir . Autoload::FILE_PREFIX;
            if(file_exists($url)){
                $prefix = json_decode(file_get_contents($url));
            } else {
                $prefix = $object->config('autoload.prefix');
            }
        } else {
            $prefix = $object->config('autoload.prefix');
        }
        if(
            !empty($prefix) &&
            is_array($prefix)
        ){
            foreach($prefix as $record){
                $parameters = Core::object($record, 'array');
                $parameters = Config::parameters($object, $parameters);
                if(
                    array_key_exists('prefix', $parameters) &&
                    array_key_exists('directory', $parameters) &&
                    array_key_exists('extension', $parameters)
                ){
                    $autoload->addPrefix($parameters['prefix'],  $parameters['directory'], $parameters['extension']);
                }
                elseif(
                    array_key_exists('prefix', $parameters) &&
                    array_key_exists('directory', $parameters)
                ){
                    $autoload->addPrefix($parameters['prefix'],  $parameters['directory']);
                }
            }
        } else {
            $autoload->addPrefix('Package',  $object->config(Config::DATA_PROJECT_DIR_PACKAGE));
            $autoload->addPrefix('Source',  $object->config(Config::DATA_PROJECT_DIR_SOURCE));
            $autoload->is_init = true;
        }
        if(
            empty($object->config('ramdisk.is.disabled')) &&
            !empty($object->config('ramdisk.url'))
        ){
            $cache_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                Autoload::NAME .
                $object->config('ds')
            ;

            $class_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'Class' .
                $object->config('ds')
            ;
            $object->config('autoload.cache.class', $class_dir);
            $compile_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'Compile' .
                $object->config('ds')
            ;
            $object->config('autoload.cache.compile', $compile_dir);
            if(!is_dir($object->config('ramdisk.url'))){
                mkdir($object->config('ramdisk.url'), 0750, true);
                if(empty($object->config(Config::POSIX_ID))){
                    exec('chown www-data:www-data ' . $object->config('ramdisk.url'));
                }
            }
            if(!is_dir($class_dir)){
                mkdir($class_dir,0750, true);
            }

        }
        if(empty($cache_dir)){
            $cache_dir = $object->config('autoload.cache.directory');
            if($cache_dir){
                $parameters = [];
                $parameters['cache'] = $cache_dir;
                $parameters = Config::parameters($object, $parameters);
                $cache_dir = $parameters['cache'];
            }
        }
        if(empty($cache_dir)){
            $cache_dir =
                $object->config(Config::DATA_FRAMEWORK_DIR_TEMP) .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                Autoload::NAME .
                $object->config(Config::DS)
            ;
        }
        $autoload->cache_dir($cache_dir);
        $autoload->register();
        $autoload->environment($object->config('framework.environment'));
        $object->data(App::AUTOLOAD_R3M, $autoload);        
    }

    public function register($method='load', $prepend=false): bool
    {
        $functions = spl_autoload_functions();
        if(is_array($functions)){
            foreach($functions as $function){
                $object = reset($function);
                if(is_object($object) && get_class($object) == get_class($this)){
                    return true; //register once...
                }
            }
        }
        $object = $this->object();
        $logger = false;
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            $logger = $object->config('project.log.debug');
        }
        if($logger){
            $object->logger($logger)->info('Registering autoloader', [$method, $prepend]);
        }
        return spl_autoload_register(array($this, $method), true, $prepend);
    }

    public function unregister($method='load'): bool
    {
        return spl_autoload_unregister(array($this, $method));
    }

    public function priority(): void
    {
        $functions = spl_autoload_functions();
        foreach($functions as $nr => $function){
            $object = reset($function);
            if(is_object($object) && get_class($object) == get_class($this) && $nr > 0){
                spl_autoload_unregister($function);
                spl_autoload_register($function, false, true); //prepend (prioritize)
            }
        }
    }

    public function object(App $object=null){
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject(App $object): void
    {
        $this->object = $object;
    }

    private function getObject(){
        return $this->object;
    }

    private function setEnvironment($environment='production'): void
    {
        $this->environment = $environment;
    }

    private function getEnvironment(){
        return $this->environment;
    }

    public function environment($environment=null){
        if($environment !== null){
            $this->setEnvironment($environment);
        }
        return $this->getEnvironment();
    }

    public function addPrefix($prefix='', $directory='', $extension=''): void
    {
        $prefix = trim($prefix, '\\\/'); //.'\\';
        $directory = str_replace('\\\/', DIRECTORY_SEPARATOR, rtrim($directory,'\\\/')) . DIRECTORY_SEPARATOR; //see File::dir()
        $list = $this->getPrefixList();
        if(empty($list)){
            $list = [];
        }
        if(empty($extension)){
            $found = false;
            foreach($list as $record){
                if(
                    $record['prefix'] == $prefix &&
                    $record['directory'] == $directory
                ){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                $list[]  = array(
                    'prefix' => $prefix,
                    'directory' => $directory
                );
            }
        } else {
            $found = false;
            foreach($list as $record){
                if(
                    $record['prefix'] == $prefix &&
                    $record['directory'] == $directory &&
                    !empty($record['extension']) &&
                    $record['extension'] == $extension
                ){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                $list[]  = array(
                    'prefix' => $prefix,
                    'directory' => $directory,
                    'extension' => $extension
                );
            }
        }
        $this->setPrefixList($list);
    }

    public function prependPrefix($prefix='', $directory='', $extension=''): void
    {
        $prefix = trim($prefix, '\\\/'); //.'\\';
        $directory = str_replace('\\\/', DIRECTORY_SEPARATOR, rtrim($directory,'\\\/')) . DIRECTORY_SEPARATOR; //see File::dir()
        $list = $this->getPrefixList();
        $prepend = [];
        if(empty($list)){
            $list = [];
        }
        if(empty($extension)){
            $found = false;
            foreach($list as $record){
                if(
                    $record['prefix'] == $prefix &&
                    $record['directory'] == $directory
                ){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                $prepend[]  = array(
                    'prefix' => $prefix,
                    'directory' => $directory
                );
            }
        } else {
            $found = false;
            foreach($list as $record){
                if(
                    $record['prefix'] == $prefix &&
                    $record['directory'] == $directory &&
                    !empty($record['extension']) &&
                    $record['extension'] == $extension
                ){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                $prepend[]  = array(
                    'prefix' => $prefix,
                    'directory' => $directory,
                    'extension' => $extension
                );
            }
        }
        foreach($list as $record){
            $prepend[] = $record;
        }
        $this->setPrefixList($prepend);
    }

    private function setPrefixList($list = array()): void
    {
        $this->prefixList = $list;
    }

    public function getPrefixList(){
        return $this->prefixList;
    }

    /**
     * @throws Exception
     */
    public function load($load): bool
    {
//        Logger::debug('Autoload loader: ', [ $load ], 'debug'); //found miss slow and fatal crash (push the button)
        $file = $this->locate($load);
        if (!empty($file)) {
            require_once $file;
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function name_reducer(App $object, $name='', $length=100, $separator='_', $pop_or_shift='pop'): string
    {
        $name_length = strlen($name);
        $logger_error = $object->config('project.log.error');
        if($name_length >= $length){
            $explode = explode($separator, $name);
            $explode = array_unique($explode);
            $tmp = implode('_', $explode);
            if(strlen($tmp) < $length){
                $name = $tmp;
            } else {
                while(strlen($tmp) >= $length){
                    $count = count($explode);
                    if($count === 1){
                        break;
                    }
                    switch($pop_or_shift){
                        case 'pop':
                            array_pop($explode);
                        break;
                        case 'shift':
                            array_shift($explode);
                        break;
                        default:
                            if($logger_error){
                                $object->logger($logger_error)->info('cannot reduce name with: ' . $pop_or_shift);
                            }
                            throw new Exception('cannot reduce name with: ' . $pop_or_shift);
                    }
                    $tmp = implode('_', $explode);
                }
                $name = $tmp;
            }
        }
        return str_replace($separator, '_', $name);
    }

    /**
     * @throws Exception
     */
    public function fileList($item=array(), $url=''): array
    {
        if(empty($item)){
            return [];
        }
        if(empty($this->read)){
            $this->read = $this->read($url);
        }
        $data = [];
        $caller = get_called_class();
        $object = $this->object();
        if(
            $object &&
            empty($object->config('ramdisk.is.disabled')) &&
            $object->config('autoload.cache.class') &&
            $object->config('cache.autoload.url.name_length') &&
            $object->config('cache.autoload.url.name_separator') &&
            $object->config('cache.autoload.url.name_pop_or_shift') &&
            $object->config('cache.autoload.url.directory_length') &&
            $object->config('cache.autoload.url.directory_separator') &&
            $object->config('cache.autoload.url.directory_pop_or_shift')
        ){
            $load = $item['directory'] . $item['file'];
            $load_directory = dirname($load);
            $load = basename($load) . '.' . Autoload::EXT_PHP;
            $load_compile = Autoload::name_reducer(
                $object,
                $load,
                $object->config('cache.parse.url.name_length'),
                $object->config('cache.parse.url.name_separator'),
                $object->config('cache.parse.url.name_pop_or_shift')
            );
            if(str_contains($load_compile, '_')){
                $data[] = $object->config('autoload.cache.compile') . $load_compile;
            }
            $load = Autoload::name_reducer(
                $object,
                $load,
                $object->config('cache.autoload.url.name_length'),
                $object->config('cache.autoload.url.name_separator'),
                $object->config('cache.autoload.url.name_pop_or_shift')
            );
            $load_directory = Autoload::name_reducer(
                $object,
                $load_directory,
                $object->config('cache.autoload.url.directory_length'),
                $object->config('cache.autoload.url.directory_separator'),
                $object->config('cache.autoload.url.directory_pop_or_shift')
            );
            $load_url = $object->config('autoload.cache.class') . $load_directory . '_' . $load;
            $data[] = $load_url;
            $object->config('autoload.cache.file.name', $load_url);
        }
        if(
            property_exists($this->read, 'autoload') &&
            property_exists($this->read->autoload, $caller) &&
            property_exists($this->read->autoload->{$caller}, $item['load'])
        ){
            $data[] = $this->read->autoload->{$caller}->{$item['load']};
        }
        $item['file_dot'] = str_replace('_', '.', $item['file']);
        $data[] = $item['directory'] . $item['file_dot'] . DIRECTORY_SEPARATOR . $item['file_dot'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file_dot'] . DIRECTORY_SEPARATOR . str_replace('_', '.', $item['baseName']) . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file_dot'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $this->fileList[$item['baseName']][] = $data;
        $result = [];
        foreach($data as $nr => $file){
            $result[$file] = $file;
        }
        if(str_contains($item['directory'], '0/Compile')){
            $debug = debug_backtrace(1);
            ddd($debug);
        }
        d($url);
        d($item);
        d($result);
        return $result;
    }

    /**
     * @throws LocateException
     * @throws Exception
     */
    public function locate($load=null, $is_data=false){
        $dir = $this->cache_dir();
        $url = $dir . Autoload::FILE;
        $load = ltrim($load, '\\');
        $prefixList = $this->getPrefixList();
        $fileList = [];
        $object = $this->object();
        $logger_error = $object->config('project.log.error');
        $logger_debug = $object->config('project.log.debug');
        if($object->config('ramdisk.url')){
            $dir_temp = $object->config('ramdisk.url') .
                $object->config('posix.id') .
                $object->config('ds') .
                'Autoload' .
                $object->config('ds')
            ;
        } else {
            $dir_temp = $object->config('framework.dir.temp') .
                $object->config('posix.id') .
                $object->config('ds') .
                'Autoload' .
                $object->config('ds')
            ;
        }
        if(!Dir::is($dir_temp)){
            Dir::create($dir_temp, Dir::CHMOD);
        }
        if(!empty($prefixList)){
            foreach($prefixList as $nr => $item){
                if(empty($item['prefix'])){
                    continue;
                }
                if(empty($item['directory'])){
                    continue;
                }
                $item['file'] = false;
                if (str_starts_with($load, $item['prefix'])) {
                    $item['file'] =
                    trim(substr($load, strlen($item['prefix'])),'\\');
                    $item['file'] =
                    str_replace('\\', DIRECTORY_SEPARATOR, $item['file']);
                    $tmp = explode('.', $item['file']);
                    if(count($tmp) >= 2){
                        array_pop($tmp);
                    }
                    $item['file'] = implode('.', $tmp);
                }
                elseif($is_data === false) {
                    continue; //changed @ 2023-11-16
                    /*
                    File::append(
                        $url_prefix,
                        'prefix: ' . $item['prefix'] . ', ' . 'directory: ' . $item['directory'] . ', load: ' . $load . PHP_EOL
                    );
                    $tmp = explode('.', $load);
                    if(count($tmp) >= 2){
                        array_pop($tmp);
                    }
                    $item['file'] = implode('.', $tmp);
                    */
                }
                if(empty($item['file'])){
                    $item['file'] = $load;
                }
                if(!empty($item['file'])){
                    $item['load'] = $load;
                    $item['file'] = str_replace('\\', DIRECTORY_SEPARATOR, $item['file']);
                    $item['file'] = str_replace('.'  . DIRECTORY_SEPARATOR , DIRECTORY_SEPARATOR, $item['file']);
                    $item['baseName'] = basename(
                        $this->removeExtension($item['file'],
                            array(
                                Autoload::EXT_PHP,
                                Autoload::EXT_TPL
                            )
                    ));
                    $item['baseName'] = explode(DIRECTORY_SEPARATOR, $item['baseName'], 2);
                    $item['baseName'] = end($item['baseName']);
                    $item['dirName'] = dirname($item['file']);
                    if($item['dirName'] == '.'){
                        unset($item['dirName']);
                    }
                    $fileList[$nr] = $this->fileList($item, $url);
                    if(is_array($fileList[$nr]) && empty($this->expose())){
                        foreach($fileList[$nr] as $file){
                            if($logger_error){
                                $object->logger($logger_error)->info('Autoload file: ' . $file, [is_readable($file) , file_exists($file)]);
                            }
                            /*
                            File::append(
                                $dir_temp .
                                'Autoload.File.log',
                                $file  . ' --> ' . is_readable($file) . '-' .file_exists($file) . PHP_EOL
                            );
                            */
                            if(file_exists($file)){
                                if(
                                    empty($object->config('ramdisk.is.disabled')) &&
                                    $object->config('autoload.cache.file.name')
                                ){
                                    $config_dir = $object->config('ramdisk.url') .
                                        $object->config(Config::POSIX_ID) .
                                        $object->config('ds') .
                                        Autoload::NAME .
                                        $object->config('ds')
                                    ;
                                    $config_url = $config_dir .
                                        'File.Mtime' .
                                        $object->config('extension.json')
                                    ;
                                    $mtime = $object->get(sha1($config_url));
                                    if(empty($mtime)){
                                        $mtime = [];
                                        if(file_exists($config_url)){
                                            $content = file_get_contents($config_url);
                                            if($content){
                                                $mtime = json_decode($content, true);
                                            }
                                        }
                                    }
                                    if(
                                        $mtime &&
                                        $file === $object->config('autoload.cache.file.name') &&
                                        array_key_exists(sha1($file), $mtime) &&
                                        file_exists($mtime[sha1($file)]) &&
                                        filemtime($file) === filemtime($mtime[sha1($file)])
                                    ){
                                        //from ramdisk
                                        $this->cache($file, $load);
                                        return $file;
                                    } else {
                                        if(Autoload::ramdisk_exclude_load($object, $load)){
                                            //controllers cannot be cached
                                            //don't cache R3m\Io\Module\Compile because they are already cached
                                        }
                                        else {
                                            //from disk
                                            //copy to ramdisk
                                            $dirname = dirname($object->config('autoload.cache.file.name'));
                                            if(!is_dir($dirname)){
                                                mkdir($dirname, 0750, true);
                                            }
                                            $read = file_get_contents($file);
                                            if(Autoload::ramdisk_exclude_content($object, $read, $file)){
                                                //files with content __DIR__, __FILE__ cannot be cached
                                            } else {
                                                //save to file
                                                file_put_contents($object->config('autoload.cache.file.name'), $read);
                                                touch($object->config('autoload.cache.file.name'), filemtime($file));

                                                //save file reference for filemtime comparison
                                                $mtime[sha1($object->config('autoload.cache.file.name'))] = $file;
                                                if(!is_dir($config_dir)){
                                                    mkdir($config_dir, 0750, true);
                                                }
                                                $write = json_encode($mtime, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                                                file_put_contents($config_url, $write);
                                                $object->set(sha1($config_url), $mtime);
                                                exec('chmod 640 ' . $object->config('autoload.cache.file.name'));
                                                exec('chmod 640 ' . $config_url);
                                            }
                                        }
                                    }
                                }
                                $this->cache($file, $load);
                                return $file;
                            }
                        }
                    }
                }
            }
        }
        if($is_data === true){
            if($this->environment() == 'development'){
                throw new LocateException('Could not find data file (' . $load . ')', Autoload::exception_filelist($fileList));
            } else {
                throw new LocateException('Could not find data file (' . $load . ')');
            }
        }
        /**
         * need fast logger writing to autoload log which should be in tmp ?
         */

        //json objects.

        $data = new Data();
        $data->set('Autoload.load', $load);
        $data->set('Autoload.type', Autoload::TYPE_ERROR);
        $data->set('Autoload.fileList', $fileList);
        $data->set('Autoload.prefixList', $prefixList);
        $data->set('Autoload.environment', $this->environment());
        $data->set('Autoload.expose', $this->expose());
        $data->set('Autoload.time', microtime(true));

        File::append(
            $dir_temp .
            'Autoload.log',
            json_encode($data->data(),JSON_PRETTY_PRINT)
        );
        //we might comment the environment / expose, where did we use expose ?
        if(
            $this->environment() == 'init' ||
            $this->environment() == 'development' ||
            !empty($this->expose())
        ){
            if(empty($this->expose())){
                throw new LocateException('Autoload error, cannot load (' . $load .') class. (see ' . $dir_temp . 'Autoload.log' . ')', Autoload::exception_filelist($fileList));
            }
            $object = new stdClass();
            $object->load = $load;
            $debug = debug_backtrace(1);
            $output = [];
            for($i=0; $i < 5; $i++){
                if(!isset($debug[$i])){
                    continue;
                }
                $output[$i] = $debug[$i];
            }
            $attribute = 'R3m\Io\Exception\LocateException';
            if(!empty($this->expose())){
                $attribute = $load;
            }
            if(
                isset($item['baseName']) &&
                isset($this->fileList[$item['baseName']])
            ){
                $object->{$attribute} = $this->fileList[$item['baseName']];
            }
            $object->debug = $output;
            if(ob_get_level() !== 0){
                ob_flush();
            }
            if(empty($this->expose())){
                echo '<pre>';
                echo json_encode($object, JSON_PRETTY_PRINT);
                echo '</pre>';
                die;

            } else {
                echo json_encode($object, JSON_PRETTY_PRINT);
            }
        }
        return false;
    }

    public function __destruct(){
        $object = $this->object();
        $dir = $this->cache_dir();
        if(!empty($this->read)){
            if($dir){
                $url = $dir . Autoload::FILE;
                $this->write($url, $this->read);
                if(file_exists($url)) {
                    exec('chmod 640 ' . $url);
                }
            }
        }
        $prefixList = $this->getPrefixList();
        if(!empty($prefixList) && $this->is_init === false){
            $url = $dir. Autoload::FILE_PREFIX;
            $start = $object->config('time.start');
            $exist = file_exists($url);
            if($exist){
                $mtime = filemtime($url);
                if($mtime > $start - 60){
                    //if file is younger than 1 minute: return
                    return;
                }
            }
            $this->write($url, $prefixList);
            if(file_exists($url)) {
                exec('chmod 640 ' . $url);
            }
        }
    }

    public function cache_dir($directory=null){
        if($directory !== null){
            $this->cache_dir = $directory;
        }
        return $this->cache_dir;
    }

    private function cache($file='', $class=''): void
    {
        if(empty($this->read)){
            $dir = $this->cache_dir();
            $url = $dir . Autoload::FILE;
            $this->read = $this->read($url);
        }
        if(empty($this->read->autoload)){
            $this->read->autoload = new stdClass();
        }
        $caller = get_called_class();
        if(empty($this->read->autoload->{$caller})){
            $this->read->autoload->{$caller}= new stdClass();
        }
        $this->read->autoload->{$caller}->{$class} = (string) $file;
    }

    protected function write($url='', $data=''): int|bool
    {
        $data = (string) json_encode($data, JSON_PRETTY_PRINT);
        if(empty($data)){
            return false;
        }
        $dir = dirname($url);
        if(is_dir($dir) === false){
            try {
                @mkdir($dir, 0750, true);
            } catch(Exception $exception){
                return false;
            }
        }
        if(is_dir($dir) === false){
            return false;
        }
        return file_put_contents($url, $data, LOCK_EX);
    }

    private function read($url=''): object
    {
        if(file_exists($url) === false){
            $this->read = new stdClass();
            return $this->read;
        }
        $this->read =  json_decode(implode('', file($url)));
        if(empty($this->read)){
            $this->read = new stdClass();
        }
        return $this->read;
    }

    private function removeExtension($filename='', $extension=array()): string
    {
        foreach($extension as $ext){
            $ext = '.' . ltrim($ext, '.');
            $filename = explode($ext, $filename, 2);
            if(count($filename) > 1 && empty(end($filename))){
                array_pop($filename);
            }
            $filename = implode($ext, $filename);
        }
        return $filename;
    }

    public function expose($expose=null): ?bool
    {
        if(!empty($expose) || $expose === false){
            $this->expose = (bool) $expose;
        }
        return $this->expose;
    }

    private static function exception_filelist($filelist=[]): array
    {
        $result = [];
        foreach($filelist as  $list){
            foreach($list as $record){
                if(substr($record, 0, 5) === '[---]'){
                    $result[] = '[---]';
                } else {
                    $result[] = $record;
                }
            }
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    public static function ramdisk_exclude_load(App $object, $load=''): bool
    {
        $is_exclude = false;
        $exclude_load = $object->config('ramdisk.autoload.exclude.load');
        if(
            !empty($exclude_load) &&
            is_array($exclude_load)
        ){
            foreach($exclude_load as $needle){
                if(stristr($load, $needle) !== false){
                    $is_exclude = true;
                    break;
                }
            }
        }
        return $is_exclude;
    }

    /**
     * @throws Exception
     */
    public static function ramdisk_exclude_content(App $object, $content='', $file=''): bool
    {
        $exclude_content = $object->config('ramdisk.autoload.exclude.content');
        $is_exclude = false;
        $exclude = [];
        $exclude_dir = false;
        $exclude_url = false;
        if($object->config('ramdisk.url')){
            $exclude_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                Autoload::NAME .
                $object->config('ds')
            ;
            $exclude_url = $exclude_dir .
                'Exclude' .
                $object->config('extension.json')
            ;
            if(file_exists($exclude_url)){
                $read = file_get_contents($exclude_url);
                if($read){
                    $exclude = json_decode($read, true);
                    if(
                        array_key_exists(sha1($file), $exclude) &&
                        file_exists($file) &&
                        filemtime($file) === $exclude[sha1($file)]
                    ){
                        return true;
                    }
                }
            }
        }
        if(
            !empty($exclude_content) &&
            is_array($exclude_content)
        ){
            foreach ($exclude_content as $needle){
                if(stristr($content, $needle) !== false){
                    $is_exclude = true;
                    break;
                }
            }
        }
        if(
            $is_exclude &&
            $exclude_dir &&
            $exclude_url &&
            file_exists($file)
        ){
            $exclude[sha1($file)] = filemtime($file);
            $write = json_encode($exclude, JSON_PRETTY_PRINT);
            if(!file_exists($exclude_dir)){
                mkdir($exclude_dir, 0750, true);
            }
            file_put_contents($exclude_url, $write);
            exec('chmod 640 ' . $exclude_url);
        }
        return $is_exclude;
    }

    public static function ramdisk_configure(App $object): void
    {
        $function ='ramdisk_load';
        spl_autoload_register(array($object, $function), true, true);
    }
}