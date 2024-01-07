<?php
/**
 * @author          Remco van der Velde
 * @since           07-01-2024
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Route;

use R3m\Io\App;

class TypeNull {

    public static function validate(App $object, $string=''): bool
    {
        if(strtolower($string) == 'null'){
            return true;
        }
        return false;
    }

}