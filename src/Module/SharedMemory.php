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

use Shmop;

use R3m\Io\App;

use R3m\Io\Exception\ObjectException;

use Exception;
use ErrorException;

class SharedMemory {

    /*
    public static function read(App $object, $url, $offset=0, $length=0): mixed
    {
        $data = null;
        $connect = null;
        try {
            $connect_shmop = @shmop_open(
                1,
                'a',
                0,
                0
            );
            $connect = @shmop_read($connect_shmop, 0, @shmop_size($connect_shmop));
            d($connect);
            $connect = explode("\0", $connect, 2);
            $connect = Core::object($connect[0], Core::OBJECT_ARRAY);
        }
        catch (ErrorException $exception) {
            d($exception);
            //no mapping
        }
        $id = false;
        d($connect);
        if(is_array($connect)){
            //make binary search ?
            foreach($connect as $nr => $record){
                if($record === $url){
                    $id = $nr;
                    break;
                }
            }
        }
        if($id === false){
            return null;
        }
        try {
            $shmop = @shmop_open(
                $id,
                'a',
                0,
                0
            );
            if($length > 0){
                $data = @shmop_read($shmop, $offset, $length);
            } else {
                $data = @shmop_read($shmop, $offset, @shmop_size($shmop));
            }
            $data = explode("\0", $data, 2);
            $data = $data[0];
            if(
                substr($data, 0, 1) === '{' &&
                substr($data, -1, 1) === '}'
            ){
                $data = Core::object($data, Core::OBJECT_OBJECT);
            }
            elseif(
                substr($data, 0, 1) === '[' &&
                substr($data, -1, 1) === ']'
            ){
                $data = Core::object($data, Core::OBJECT_ARRAY);
            }
            elseif($data === 'false'){
                $data = false;
            }
            elseif($data === 'true'){
                $data = true;
            }
            elseif($data === 'null'){
                $data = null;
            }
            elseif(is_numeric($data)){
                $data = $data + 0;
            }
            return $data;
        }
        catch (ErrorException $exception){
            d($exception);
            //cache miss
            return null;
        }
    }
    */

    /*
    public static function write(App $object, $url, $data='', $permission=File::CHMOD): false | int
    {
        try {
            if(
                is_array($data) ||
                is_object($data)
            ){
                $data = Core::object($data, Core::OBJECT_JSON_LINE);
            }
            if(!is_string($data)){
                $data = (string) $data;
            }
            //ftok goes wrong on linux with url
            $connect = SharedMemory::read($object, 'mapping');
            d($connect);
            if($connect === null){
                $connect = [];
                $id = 1;
                $connect[$id] = 'mapping';
                $id = 1000;
                $connect[$id] = $url;
            } else {
                $connect = Core::object($connect, Core::OBJECT_ARRAY);
                d($connect);
                if(!is_array($connect)){
                    $connect = [];
                    $id = 1;
                    $connect[$id] = 'mapping';
                    $id = 1000;
                    $connect[$id] = $url;
                } else {
                    $id = array_key_last($connect) + 1;
                    $connect[$id] = $url;
                }
            }
            $data .= "\0";
            $shm_size = mb_strlen($data);
            try {
                $shmop = @shmop_open(
                    $id,
                    'c',
                    $permission,
                    $shm_size
                );
            }
            catch (ErrorException $exception){
                $shmop = false;
            }

            if($shmop === false){
                $shmop = @shmop_open(
                    $id,
                    'w',
                    $permission,
                    $shm_size
                );
            }
            $write = shmop_write($shmop, $data, 0);
            if($write > 0){
                $connect = Core::object($connect, Core::OBJECT_JSON_LINE);
                $connect .= "\0";
                $shm_size = mb_strlen($connect);
                try {
                    $connect_shmop = @shmop_open(
                        1,
                        'c',
                        $permission,
                        $shm_size
                    );
                }
                catch (ErrorException $exception){
                    $connect_shmop = false;
                }
                if($connect_shmop === false){
                    $connect_shmop = @shmop_open(
                        1,
                        'w',
                        $permission,
                        $shm_size
                    );
                }
                $connect_write = shmop_write($connect_shmop, $connect, 0);
                d($connect);
                d($connect_write);
                if($connect_write > 0){
                    return $write;
                }
            }
        }
        catch(ErrorException | ObjectException $exception){
            return false;
        }
        return false;
    }
    */

    /**
     * @throws Exception
     */
    public static function id(App $object){
        // don't write in the first million
        $id = random_int(1000000, 4294967295); // 4294967295 is the maximum value of a 32-bit unsigned integer
        return $id;
    }

// Example usage:
//$shm_id = shmop_open($key, "c", 0644, $segment_size);
//$id = generateUniqueShmopId($shm_id, $segment_size);
//echo bin2hex($id); // Convert binary to hexadecimal for easier display


    public static function url(App $object, $id)
    {
        $shmop = SharedMemory::open(1, 'c', File::CHMOD, (1 * 1024 * 1024));
        $read = SharedMemory::read($shmop, 0, SharedMemory::size($shmop));
        $temp = explode("\0", $read, 2);
        $temp = trim($temp[0]);
        $url = null;
        if($temp !== ''){
            $temp = json_decode($temp, true);
            if(
                array_key_exists('url', $temp) &&
                array_key_exists($id, $temp['url'])
            ){
                $url = $temp['url'][$id];
            }
        }
        return $url;
    }

    public static function key_delete(App $object, $id)
    {
        $shmop = SharedMemory::open(1, 'c', File::CHMOD, (1 * 1024 * 1024));
        $read = SharedMemory::read($shmop, 0, SharedMemory::size($shmop));
        $temp = explode("\0", $read, 2);
        $temp = trim($temp[0]);
        if($temp !== ''){
            $temp = json_decode($temp, true);
            if(is_array($id)){
                foreach($id as $nr){
                    unset($temp['url'][$nr]);
                    unset($temp['size'][$nr]);
                    unset($temp['mtime'][$nr]);
                }
            } else {
                unset($temp['url'][$id]);
                unset($temp['size'][$id]);
                unset($temp['mtime'][$id]);
            }
            $write = json_encode($temp);
            $write .= "\0";
            SharedMemory::write($shmop, $write, 0);
        }
    }

    /**
     * @throws Exception
     */
    public static function key(App $object, $url, $size=0, $mtime=null): array
    {
        $shmop = SharedMemory::open(1, 'c', File::CHMOD, (2 * 1024 * 1024));
        $read = SharedMemory::read($shmop, 0, SharedMemory::size($shmop));
        ddd(rtrim($read));
//        $temp = explode("\0", $read, 2);
        if(
            array_key_exists(1, $temp) &&
            !empty(trim($temp[0]))
        ){
            d($temp);
            ddd($temp[0]);
            $temp = gzdecode($temp[0]);
            $temp = json_decode($temp, true);
            $id = array_search($url, $temp['url']);
            if($id === false){
                while(true){
                    $id = SharedMemory::id($object);
                    if(!array_key_exists($id, $temp['url'])) {
                        break;
                    }
                }
                $temp['url'][$id] = $url;
                $temp['size'][$id] = $size;
                $temp['mtime'][$id] = $mtime;
                $write = json_encode($temp);
                $write = gzencode($write, 9);
                $write .= "\0";
                SharedMemory::write($shmop, $write, 0);
            }
        } else {
            $temp = [];
            $id = SharedMemory::id($object);
            $temp['url'][$id] = $url;
            $temp['size'][$id] = $size;
            $temp['mtime'][$id] = $mtime;
            $write = json_encode($temp);
            $write = gzencode($write, 9);
            $write .= "\0";
            SharedMemory::write($shmop, $write, 0);
        }
        return [
            'id' => $id,
            'url' => $url,
            'size' => $temp['size'][$id],
            'mtime' => $temp['mtime'][$id]
        ];
    }

    public static function open($key, $mode, $permission=File::CHMOD, $size=1): Shmop | bool
    {
        return @shmop_open($key, $mode, $permission, $size);
    }

    public static function delete(Shmop $shmop): bool
    {
        return @shmop_delete($shmop);
    }

    public static function read(Shmop $shmop, $offset=0, $size=1): mixed
    {
        return @shmop_read($shmop, $offset, $size);
    }

    public static function size(Shmop $shmop): int
    {
        return @shmop_size($shmop);
    }

    public static function write(Shmop $shmop, $data, $offset=0): int
    {
        return @shmop_write($shmop, $data, $offset);
    }
}