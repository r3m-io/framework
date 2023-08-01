<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;

function function_array_bestmatch_list(Parse $parse, Data $data, $array=[], $search='', $with_score=false){
    return Core::array_bestmatch_list($array, $search, $with_score);
}
