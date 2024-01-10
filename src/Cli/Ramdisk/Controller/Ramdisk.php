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
namespace R3m\Io\Cli\Ramdisk\Controller;

use R3m\Io\App;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Ramdisk extends Controller {
    const DIR = __DIR__;
    const NAME = 'Ramdisk';
    const INFO = [
        '{{binary()}} ramdisk clear                  | Ramdisk clear',
        '{{binary()}} ramdisk mount <size>           | Ramdisk allocation',
        '{{binary()}} ramdisk speedtest              | Ramdisk speedtest',
        '{{binary()}} ramdisk unmount                | Ramdisk unmount'
    ];

    /**
     * @throws ObjectException
     */
    public static function run(App $object){
        $name = false;
        $url = false;
        $command = false;
        try {
            $command = App::parameter($object, lcfirst(Ramdisk::NAME), 1);
            $name = false;
            switch (strtolower($command)){
                case 'mount':
                case 'unmount':
                case 'speedtest':
                case 'clear':
                    $name = Ramdisk::name(strtolower($command), Ramdisk::NAME);
                break;
                default:
                    $exception = new Exception('Unknown ramdisk command...');
                    Event::trigger($object, 'cli.' . strtolower(Ramdisk::NAME) . '.' . __FUNCTION__, [
                        'command' => $command,
                        'exception' => $exception
                    ]);
                    throw $exception;
            }
            if($name){
                $url = Ramdisk::locate($object, $name);
                $response = Ramdisk::response($object, $url);
                Event::trigger($object, 'cli.' . strtolower(Ramdisk::NAME) . '.' . strtolower($command), [
                    'name' => $name,
                    'url' => $url
                ]);
                return $response;
            }
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, 'cli.' . strtolower(Ramdisk::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'command' => $command,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}