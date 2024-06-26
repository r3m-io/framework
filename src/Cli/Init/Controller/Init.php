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
namespace R3m\Io\Cli\Init\Controller;

use R3m\Io\App;

use R3m\Io\Module\Controller;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\ObjectException;

class   Init extends Controller {
    const DIR = __DIR__;
    const NAME = 'Init';
    const INFO = '{{binary()}} init                           | Init events with flags / options';

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function run(App $object): void
    {
        Event::trigger($object, 'cli.init.run', [
            'flags' => App::flags($object),
            'options' => App::options($object)
        ]);
    }
}