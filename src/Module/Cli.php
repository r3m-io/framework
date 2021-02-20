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

class Cli {

    public static function read($url='', $text=''){
        $is_flush = false;
        if(ob_get_level() > 0){
            $is_flush =true;
        }
        if($is_flush){
            ob_flush();
        }
        $input = null;
        if($url=='input'){
            echo $text;
            if($is_flush){
                ob_flush();
            }
//             system('stty -echo');
            $input = trim(fgets(STDIN));
//             system('stty echo');
//             echo PHP_EOL;

//             readline_completion_function(array($this, 'complete'));
//             $input = rtrim(readline($text), ' ');
        }
        elseif($url=='input-hidden'){
            echo $text;
            if($is_flush){
                ob_flush();
            }
            system('stty -echo');
            $input = trim(fgets(STDIN));
            system('stty echo');
            echo PHP_EOL;
        } 
        return $input;
    }

    public static function tput($tput='', $arguments=[]){
        if(!is_array($arguments)){
            $arguments = (array) $arguments;
        }
        switch(strtolower($tput)){
            case 'screen.save' :
            case 'screen.write' :
                $tput = 'smcup';
                break;
            case 'screen.restore' :
                $tput = 'rmcup';
                break;
            case 'home' :
            case 'cursor.home':
                $tput = 'home';
                break;
            case 'cursor.invisible' :
                $tput = 'civis';
                break;
            case 'cursor.normal' :
                $tput = 'cnorm';
                break;
            case 'cursor.save' :
            case 'cursor.write' :
                $tput = 'sc';
                break;
            case 'cursor.restore' :
                $tput = 'rc';
                break;
            case 'color' :
                $color = isset($arguments[0]) ? (int) $arguments[0] : 9; //9 = default
                $tput = 'setaf ' . $color;
                break;
            case 'background' :
                $color = isset($arguments[0]) ? (int) $arguments[0] : 0; //0 = default
                $tput = 'setab ' . $color;
                break;
            case 'cursor.up' :
            case 'up' :
                $amount = isset($arguments[0]) ? (int) $arguments[0] : 1;
                $tput = 'cuu' . $amount;
                break;
            case 'cursor.down' :
            case 'down' :
                $amount = isset($arguments[0]) ? (int) $arguments[0] : 1;
                $tput = 'cud' . $amount;
                break;
            case 'cursor.position' :
            case 'position' :
                $cols = isset($arguments[0]) ? (int) $arguments[0] : 0; //x
                $rows = isset($arguments[1]) ? (int) $arguments[1] : 0; //y
                $tput = 'cup ' . $rows . ' ' . $cols;
                break;
            case 'rows':
            case 'row':
            case 'height':
                $tput = 'lines';
                break;
            case 'width':
            case 'columns':
            case 'column' :
                $tput = 'cols';
                break;
            case 'default':
            case 'reset':
                $tput  = 'sgr0';
                break;
        }
        ob_start();
        $result = system('tput ' . $tput);
        ob_end_clean();
        return $result;
    }
}