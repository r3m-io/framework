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
use R3m\Io\Module\CLi;

function function_terminal_put(Parse $parse, Data $data, $command, $argument=null){
    $result = Cli::tput($command, $argument);
    return $result;
}
