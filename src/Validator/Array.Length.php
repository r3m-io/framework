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

use R3m\Io\App;

use R3m\Io\Module\Parse\Token;

/**
 * @throws Exception
 */
function validate_array_length(App $object, $array=null, $field='', $argument='', $function=false): bool
{
    $result = false;
    $length = null;
    if(is_array($argument)) {
        $arguments = $argument;
        foreach ($arguments as $argument) {
            if (
                $argument === null) {
                if ($array === null) {
                    return true;
                }
                continue;
            }
            if($length === null){
                $length = count($array);
            }
            $argument = Token::tree('{if($argument ' . $argument . ')}{/if}');
            $left = null;
            $equation = null;
            $right = null;
            foreach($argument[1]['method']['attribute'][0] as $nr => $record){
                if(empty($left)){
                    $left = $record;
                }
                elseif(empty($equation)){
                    $equation = $record['value'];
                }
                elseif(empty($right)){
                    $right = $record['execute'];
                    break;
                }
            }
            switch($equation){
                case '>' :
                    $result = $length > $right;
                    break;
                case '<' :
                    $result = $length < $right;
                    break;
                case '>=' :
                    $result = $length >= $right;
                    break;
                case '<=' :
                    $result = $length <= $right;
                    break;
                case '==' :
                    $result = $length == $right;
                    break;
                case '!=' :
                    $result = $length != $right;
                    break;
                case '===' :
                    $result = $length === $right;
                    break;
                case '!==' :
                    $result = $length !== $right;
                    break;
                default:
                    throw new Exception('Unknown equation');
            }
            if($result === false){
                return false;
            }
        }
        return true;
    } else {
        if(empty($array)){
            return false;
        }
        $length = count($array);
        $argument = Token::tree('{if($argument ' . $argument . ')}{/if}');
        $left = null;
        $equation = null;
        $right = null;
        foreach($argument[1]['method']['attribute'][0] as $nr => $record){
            if(empty($left)){
                $left = $record;
            }
            elseif(empty($equation)){
                $equation = $record['value'];
            }
            elseif(empty($right)){
                $right = $record['execute'];
                break;
            }
        }

        switch($equation){
            case '>' :
                $result = $length > $right;
                break;
            case '<' :
                $result = $length < $right;
                break;
            case '>=' :
                $result = $length >= $right;
                break;
            case '<=' :
                $result = $length <= $right;
                break;
            case '==' :
                $result = $length == $right;
                break;
            case '!=' :
                $result = $length != $right;
                break;
            case '===' :
                $result = $length === $right;
                break;
            case '!==' :
                $result = $length !== $right;
                break;
            default:
                throw new Exception('Unknown equation');
        }
    }
    return $result;    
}
