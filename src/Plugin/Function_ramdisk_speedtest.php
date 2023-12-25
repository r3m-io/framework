<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

/**
 * @throws Exception
 */
function function_ramdisk_speedtest(Parse $parse, Data $data){
    $object = $parse->object();
    $id = posix_geteuid();
    if (!empty($id)){
        throw new Exception('RamDisk speedtest can only be run by root...');
    }
    $url = $object->config('ramdisk.url') . 'speedtest';
    if($url){
        $command = 'dd if=/dev/zero of=' . $url . 'zero bs=4k count=100000';
        Core::execute($object, $command, $output, $notification);
        echo 'Write:' . PHP_EOL;
        if($output){
            echo $output . PHP_EOL;
        }
        if($notification){
            echo $notification . PHP_EOL;
        }
        $command = 'dd if=' . $url . 'zero of=/dev/null bs=4k count=100000';
        Core::execute($object, $command, $output, $notification);
        echo 'Read:' . PHP_EOL;
        if($output){
            echo $output . PHP_EOL;
        }
        if($notification){
            echo $notification . PHP_EOL;
        }
        File::delete($url . 'zero');
    }

}
