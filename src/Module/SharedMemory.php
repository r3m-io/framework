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

use ErrorException;
use Exception;
use Shmop;

class SharedMemory {

    public static function open($key, $mode, $permission=File::CHMOD, $size=1): Shmop | bool
    {
        try {
            return @shmop_open($key, $mode, $permission, $size);
        }
        catch(ErrorException | Exception $exception){
            ddd($exception);
        }
    }

    public static function delete(Shmop $shmop): bool
    {
        return @shmop_delete($shmop);
    }

    public static function read(Shmop $shmop, $offset=0, $size=1): string
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