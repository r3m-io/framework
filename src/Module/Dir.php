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
use Exception;

use R3m\Io\Exception\ErrorException;
use R3m\Io\Exception\DirectoryCreateException;
use R3m\Io\Exception\FileMoveException;

class Dir {
    const CHMOD = 0750;
    const TYPE = 'Dir';
    const SEPARATOR = DIRECTORY_SEPARATOR;
    const FORMAT_FLAT = 'flat';

    private $node;

    private $count = 0;

    public static function change($dir=''): string
    {
        $tmp = getcwd() . DIRECTORY_SEPARATOR;
        if(is_dir($dir) === false){
            Dir::create($dir, Dir::CHMOD);
        }
        chdir($dir);
        return $tmp;
    }

    public static function current(): string
    {
        return getcwd();
    }

    /**
     * @throws DirectoryCreateException
     */
    public static function create($url='', $chmod=''): bool
    {
        if($url !== Dir::SEPARATOR){
            $url = rtrim($url, Dir::SEPARATOR);
        }
        if(File::exist($url) && !Dir::is($url)){
            unlink($url);
        }
        if(File::exist($url) && Dir::is($url)){
            return true;
        } else {
            try {
                $mkdir = false;
                if(!File::exist($url)){
                    if(empty($chmod)){
                        $mkdir = @mkdir($url, Dir::CHMOD, true);
                    } else {
                        $mkdir = @mkdir($url, $chmod, true);
                    }
                }
                return $mkdir;
            }
            catch (Exception | ErrorException $exception){
                throw new DirectoryCreateException('Cannot create directory: ' . $url, 0, $exception);
            }
        }
    }

    public static function exist($url=''): bool
    {
        if($url !== Dir::SEPARATOR){
            $url = rtrim($url, Dir::SEPARATOR);
        }
        if(
            File::exist($url) === true &&
            Dir::is($url) === true
        ){
            return true;
        }
        return false;
    }
    public static function is($url=''): bool
    {
        if($url !== Dir::SEPARATOR){
            $url = rtrim($url, Dir::SEPARATOR);
        }
        return is_dir($url);
    }

    public static function size($url, $recursive=false): bool | int
    {
        if(!Dir::is($url)){
            return false;
        }
        $url = rtrim($url, Dir::SEPARATOR);
        $dir = new Dir();
        $read = $dir->read($url, $recursive, Dir::FORMAT_FLAT);
        $total = 0;
        foreach($read as $file){
            $size = filesize($file->url);
            $total += $size;
        }
        return $total;
    }

    public static function name($url='', $levels=null): string
    {
        $is_backslash = false;
        if(stristr($url, '\\') !== false){
            $url = str_replace('\\', '/', $url);
            $is_backslash = true;
        }
        if(is_null($levels)){
            $name = dirname($url);
        } else {
            $levels += 0;
            $name = dirname($url, (int) $levels);
        }
        if($name == '.'){
            return '';
        }
        elseif(substr($name, -1, 1) != '/'){
            $name .= '/';
        }
        if($is_backslash === true){
            $name = str_replace('/', '\\', $name);
        }
        return $name;
    }

    public function ignore($ignore=null, $attribute=null)
    {
        $node = $this->node();
        if(!isset($node)){
            $node = (object) [];
        }
        if(!isset($node->ignore)){
            $node->ignore = [];
        }
        if($ignore !== null){
            if(is_array($ignore) && $attribute === null){
                $node->ignore = $ignore;
            }
            elseif($ignore == 'delete' && $attribute === null){
                $node->ignore = [];
            }
            elseif($ignore=='list' && $attribute !== null){
                $node->ignore = $attribute;
            }
            elseif($ignore=='find'){
                if(substr($attribute,-1) !== Dir::SEPARATOR){
                    $attribute .= Dir::SEPARATOR;
                }
                foreach ($node->ignore as $item){
                    if(stristr($attribute, $item) !== false){
                        return true;
                    }
                }
                return false;
            } else {
                if(substr($ignore,-1) !== Dir::SEPARATOR){
                    $ignore .= Dir::SEPARATOR;
                }
                $node->ignore[] = $ignore;
            }
        }
        $node = $this->node($node);
        return $node->ignore;
    }

    public function read($url='', $recursive=false, $format='flat'){
        if(substr($url,-1) !== Dir::SEPARATOR){
            $url .= Dir::SEPARATOR;
        }
        if($this->ignore('find', $url)){
            return [];
        }
        $count = $this->count();
        $list = [];
        $cwd = getcwd();
        if(is_dir($url) === false){
            return false;
        }
        try {
            @chdir($url);
        } catch (Exception | ErrorException $exception){
            return false;
        }
        try {
            if ($handle = @opendir($url)) {
                while (false !== ($entry = readdir($handle))) {
                    $recursiveList = [];
                    if($entry == '.' || $entry == '..'){
                        continue;
                    }
                    $file = (object) [];
                    $file->url = $url . $entry;
                    if(is_dir($file->url)){
                        $file->url .= Dir::SEPARATOR;
                        $file->type = Dir::TYPE;
                    }
                    if($this->ignore('find', $file->url)){
                        continue;
                    }
                    $file->name = $entry;
                    if(isset($file->type)){
                        if(!empty($recursive)){
                            $directory = new Dir();
                            $directory->ignore('list', $this->ignore());
                            $recursiveList = $directory->read($file->url, $recursive, $format);
                            $count =  $count + $directory->count();
                            if($format !== 'flat'){
                                $file->list = $recursiveList;
                                unset($recursiveList);
                            }
                        }
                    } else {
                        $file->type = File::TYPE;
                    }
                    if(is_link($entry)){
                        $file->link = true;
                    }
                    $list[] = $file;
                    if(!empty($recursiveList)){
                        foreach ($recursiveList as $recursive_file){
                            $list[] = $recursive_file;
                        }
                    }
                    $count++;
                }
            }
        } catch (Exception | ErrorException $exception){
            return false;
        }
        if(is_resource($handle)){
            closedir($handle);
        }
        if(is_dir($cwd)){
            @chdir($cwd);
        }
        $this->count($count);
        return $list;
    }

    public function count($count=null): int
    {
        if($count === null){
            return $this->getCount();
        } else {
            $this->setCount($count);
            return $this->getCount();
        }
    }

    public function setCount($count=0): void
    {
        $this->count = $count;
    }

    public function getCount(): int
    {
        return $this->count ?? 0;
    }

    public static function amount ($url='') : int
    {
        $dir = Dir::current();
        Dir::change($url);
        exec('ls -1p | grep -v / | wc -l', $output);
        if(isset($output[0])){
            $output = (int) $output[0];
        } else {
            $output = 0;
        }
        Dir::change($dir);
        return $output;
    }

    public static function copy($source='', $target=''): bool
    {
        if(substr($source, -1) !== Dir::SEPARATOR){
            $source .= Dir::SEPARATOR;
        }
        if(is_dir($source)){
            $source = escapeshellarg($source);
            $target = escapeshellarg($target);
            exec('cp ' . $source . '* ' . $target . ' -R');
            return true;
        } else {
            return false;
        }
    }

    public static function rename($source='', $destination='', $overwrite=false): bool
    {
        try {
            return File::rename($source, $destination, $overwrite);
        } catch (Exception | FileMoveException $exception){
            return false;
        }
    }

    public static function move($source='', $destination='', $overwrite=false): bool
    {
        try {
            return File::move($source, $destination, $overwrite);
        } catch (Exception | FileMoveException $exception){
            return false;
        }
    }

    public static function remove($dir=''): bool
    {
        if(Dir::is($dir) === false){
            return true;
        }
        if($dir === '/'){
            return false;
        }
        $dir = escapeshellarg($dir);
        exec('rm -rf ' . $dir);
        return true;
    }

    public function delete($dir=''): bool
    {
        if(Dir::is($dir) === false){
            return true;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $nr => $file) {
            if($this->ignore('find', "$dir/$file")){
                continue;
            }
            if(is_dir("$dir/$file")){
                $this->delete("$dir/$file");
            } else {
                unlink("$dir/$file");
                unset($files[$nr]);
            }
        }
        if($this->ignore('find', "$dir")){
            return true;
        }
        return rmdir($dir);
    }
    public function node($node=null): mixed
    {
        if($node !== null){
            $this->setNode($node);
        }
        return $this->getNode();
    }
    private function setNode($node=null): void
    {
        $this->node = $node;
    }
    private function getNode(): mixed
    {
        return $this->node;
    }

    public static function ucfirst($dir=''): string
    {
        $explode = explode('/', $dir);
        $result = '';
        foreach($explode as $part){
            if(empty($part)){
                continue;
            }
            $result .= ucfirst($part) . '/';
        }
        return $result;
    }
}