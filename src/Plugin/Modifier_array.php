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
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;

/**
 * @throws \R3m\Io\Exception\ObjectException
 */
function modifier_array(Parse $parse, Data $data, $value){
    return Core::object($value, Core::OBJECT_ARRAY);
}
