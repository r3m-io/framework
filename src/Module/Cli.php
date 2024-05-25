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

use R3m\Io\Config;
use R3m\Io\Exception\ObjectException;
use stdClass;
use Exception;

class Cli {
    const INPUT = 'input';
    const INPUT_HIDDEN = 'input-hidden';
    const STREAM = 'stream';
    const COLOR_BLACK = 0;
    const COLOR_RED = 1;
    const COLOR_GREEN = 2;
    const COLOR_YELLOW = 3;
    const COLOR_BLUE = 4;
    const COLOR_PURPLE = 5;
    const COLOR_LIGHTBLUE = 6;
    const COLOR_LIGHTGREY = 7;

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function read($url='', $text='')
    {
        $is_flush = false;
        if(ob_get_level() > 0){
            $is_flush =true;
        }
        if($is_flush){
            ob_flush();
        }
        if(empty($url)){
            $url = 'input';
        }
        $input = null;
        switch($url){
            case Cli::INPUT:
                fwrite(STDOUT, $text);
                if($is_flush){
                    ob_flush();
                }
                fflush(STDOUT);
                if($is_flush){
                    ob_flush();
                }
                $input = trim(fgets(STDIN));
            break;
            case Cli::INPUT_HIDDEN:
                fwrite(STDOUT, $text);
                if($is_flush){
                    ob_flush();
                }
                fflush(STDOUT);
                system('stty -echo');
                $input = trim(fgets(STDIN));
                system('stty echo');
            break;
            case Cli::STREAM :
                $input = trim(fgets(STDIN));
                $input = Core::object($input);
            break;
            default:
                throw new Exception('Could not detect type: (input | input-hidden | stream)');

        }
        return $input;
    }

    public static function default(): void
    {
        echo chr(27) . "[0m";
    }

    public static function width(): bool|string
    {
        return exec('tput cols');
    }

    public static function height(): bool|string
    {
        return exec('tput lines');
    }

    public static function tput($tput='', $arguments=[]): int | string
    {
        if(!is_array($arguments)){
            $arguments = (array) $arguments;
        }
        switch(strtolower($tput)){
            case 'screen.save' :
            case 'screen.write' :
            case 'smcup' :
                $tput = 'smcup';
                break;
            case 'screen.restore' :
            case 'rmcup' :
                $tput = 'rmcup';
                break;
            case 'home' :
            case 'cursor.home':
                $tput = 'home';
                break;
            case 'erase.line' :
            case 'el' :
                $tput = 'el';
                break;
            case 'cursor.invisible' :
            case 'civis' :
                $tput = 'civis';
                break;
            case 'cursor.normal' :
            case 'cnorm' :
                $tput = 'cnorm';
                break;
            case 'cursor.save' :
            case 'cursor.write' :
            case 'sc' :
                $tput = 'sc';
                break;
            case 'cursor.restore' :
            case 'rc' :
                $tput = 'rc';
                break;
            case 'color' :
            case 'setaf' :
                $color = isset($arguments[0]) ? (int) $arguments[0] : 9; //9 = default
                $tput = 'setaf ' . $color;
                break;
            case 'background' :
            case 'setab' :
                $color = isset($arguments[0]) ? (int) $arguments[0] : 0; //0 = default
                $tput = 'setab ' . $color;
                break;
            case 'cursor.up' :
            case 'up' :
            case 'cuu' :
                $amount = isset($arguments[0]) ? (int) $arguments[0] : 1;
                $tput = 'cuu ' . $amount;
                break;
            case 'cursor.down' :
            case 'down' :
            case 'cud' :
                $amount = isset($arguments[0]) ? (int) $arguments[0] : 1;
                $tput = 'cud ' . $amount;
                break;
            case 'cursor.position' :
            case 'position' :
            case 'cup' :
                $cols = isset($arguments[0]) ? (int) $arguments[0] : 0; //x
                $rows = isset($arguments[1]) ? (int) $arguments[1] : 0; //y
                $tput = 'cup ' . $rows . ' ' . $cols;
                break;
            case 'rows':
            case 'row':
            case 'height':
            case 'lines' :
                $tput = 'lines';
                break;
            case 'width':
            case 'columns':
            case 'column' :
            case 'cols' :
                $tput = 'cols';
                break;
            case 'default':
            case 'reset':
            case 'sgr0':
                $tput  = 'sgr0';
                break;
            case 'init':
                $tput = 'init';
                break;
        }
        ob_start();
        $result = system('tput ' . $tput);
        ob_end_clean();
        switch(strtolower($tput)){
            case 'rows':
            case 'row':
            case 'height':
            case 'lines' :
                $result = (int) $result;
                break;
            case 'width':
            case 'columns':
            case 'column' :
            case 'cols' :
                $result = (int) $result;
                break;
        }
        return $result;
    }

    public static function color($color=null, $background=null): string
    {
        $result = [];
        if (
            $color &&
            is_array($color) &&
            array_key_exists('r', $color) &&
            array_key_exists('g', $color) &&
            array_key_exists('b', $color)
        )
        {
            $result[] = chr(27) . '[38;2;' . $color['r'] . ';' . $color['g'] . ';' . $color['b'] . 'm'; //rgb foreground color
        }
        elseif
        (
            $color &&
            is_object($color) &&
            property_exists($color, 'r') &&
            property_exists($color, 'g') &&
            property_exists($color, 'b')
        ){
            $result[] = chr(27) . '[38;2;' . $color->r . ';' . $color->g . ';' . $color->b . 'm'; //rgb foreground color
        }
        if (
            is_array($background) &&
            array_key_exists('r', $background) &&
            array_key_exists('g', $background) &&
            array_key_exists('b', $background)
        ) {
            $result[] = chr(27) . '[48;2;' . $background['r'] . ';' . $background['g'] . ';' . $background['b'] . 'm'; //rgb background color
        } elseif (
            is_object($background) &&
            property_exists($background, 'r') &&
            property_exists($background, 'g') &&
            property_exists($background, 'b')
        ) {
            $result[] = chr(27) . '[48;2;' . $color->r . ';' . $color->g . ';' . $color->b . 'm'; //rgb background color
        }
        return implode('', $result);
    }

    public static function alert($text='', $options=[]): void
    {
        if(
            array_key_exists('capitals', $options) &&
            $options['capitals'] === true
        ){
            $text = strtoupper($text);
        }
        $text = ' ' . $text . ' ';
        echo Cli::color(['r'=>255, 'g'=>255, 'b'=>255], ['r'=>200, 'g'=>100, 'b'=>0]) . $text . Cli::tput('reset');
    }

    public static function critical($text='', $options=[]): void
    {
        if(
            array_key_exists('capitals', $options) &&
            $options['capitals'] === true
        ){
            $text = strtoupper($text);
        }
        $text = ' ' . $text . ' ';
        echo Cli::color(['r'=>255, 'g'=>255, 'b'=>255], ['r'=>200, 'g'=>0, 'b'=>200]) . $text . Cli::tput('reset');
    }

    public static function debug($text='', $options=[]): void
    {
        if(
            array_key_exists('capitals', $options) &&
            $options['capitals'] === true
        ){
            $text = strtoupper($text);
        }
        $text = ' ' . $text . ' ';
        echo Cli::color(['r'=>255, 'g'=>255, 'b'=>255], ['r'=>0, 'g'=>200, 'b'=>0]) . $text . Cli::tput('reset');
    }

    public static function emergency($text='', $options=[]): void
    {
        if(
            array_key_exists('capitals', $options) &&
            $options['capitals'] === true
        ){
            $text = strtoupper($text);
        }
        $text = ' ' . $text . ' ';
        echo Cli::color(['r'=>255, 'g'=>255, 'b'=>255], ['r'=>255, 'g'=>0, 'b'=>0]) . $text . Cli::tput('reset');
    }

    public static function error($text='', $options=[]): void
    {
        if(
            array_key_exists('capitals', $options) &&
            $options['capitals'] === true
        ){
            $text = strtoupper($text);
        }
        $text = ' ' . $text . ' ';
        echo Cli::color(['r'=>255, 'g'=>255, 'b'=>255], ['r'=>200, 'g'=>0, 'b'=>0]) . $text . Cli::tput('reset');
    }

    public static function info($text='', $options=[]): void
    {
        if(
            array_key_exists('capitals', $options) &&
            $options['capitals'] === true
        ){
            $text = strtoupper($text);
        }
        $text = ' ' . $text . ' ';
        echo Cli::color(['r'=>255, 'g'=>255, 'b'=>255], ['r'=>0, 'g'=>150, 'b'=>200]) . $text . Cli::tput('reset');
    }

    public static function notice($text='', $options=[]): void
    {
        if(
            array_key_exists('capitals', $options) &&
            $options['capitals'] === true
        ){
            $text = strtoupper($text);
        }
        $text = ' ' . $text . ' ';
        echo Cli::color(['r'=>255, 'g'=>255, 'b'=>255], ['r'=>0, 'g'=>0, 'b'=>0]) . $text . Cli::tput('reset');
    }

    public static function warning($text='', $options=[]): void
    {
        if(
            array_key_exists('capitals', $options) &&
            $options['capitals'] === true
        ){
            $text = strtoupper($text);
        }
        $text = ' ' . $text . ' ';
        echo Cli::color(['r'=>255, 'g'=>255, 'b'=>255], ['r'=>255, 'g'=>124, 'b'=>13]) . $text . Cli::tput('reset');
    }

    public static function labels(){
        echo CLi::alert('') . 'Alert' . PHP_EOL;
        echo CLi::critical('') . 'Critical' . PHP_EOL;
        echo CLi::debug('') . 'Debug' . PHP_EOL;
        echo CLi::emergency('') . 'Emergency' . PHP_EOL;
        echo CLi::error('') . 'Error' . PHP_EOL;
        echo CLi::info('') . 'Info' . PHP_EOL;
        echo CLi::notice('') . 'Notice' . PHP_EOL;
        echo CLi::warning('') . 'Warning' . PHP_EOL;
    }

}