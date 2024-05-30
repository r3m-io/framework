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
function validate_float(App $object, $string='', $field='', $argument='', $function=false): bool
{
    $float = floatval($string);
    if(is_array($argument)){
        $arguments = $argument;
        foreach($arguments as $argument){
            if(
                $argument === null)
            {
                if($string === null){
                    return true;
                }
                continue;
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
            $result = false;
            switch($equation){
                case '>' :
                case 'greater':
                case 'gt':
                    $result = $float > $right;
                    break;
                case '<' :
                case 'smaller':
                case 'lt':
                    $result = $float < $right;
                    break;
                case '>=' :
                case 'greater-or-equal':
                case 'gte':
                    $result = $float >= $right;
                    break;
                case '<=' :
                case 'smaller-or-equal':
                case 'lte':
                    $result = $float <= $right;
                    break;
                case '==' :
                case 'exact':
                    $result = $float == $right;
                    break;
                case '!=' :
                case 'not-exact':
                    $result = $float != $right;
                    break;
                case '===' :
                case 'strictly-exact':
                    $result = $float === $right;
                    break;
                case '!==' :
                case 'not-strictly-exact':
                    $result = $float !== $right;
                    break;
                default:
                    throw new Exception('Unknown equation');
            }
            if($result === false){
                return false;
            }
        }
        return true;
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
    $result = false;
    switch($equation){
        case '>' :
            $result = $float > $right;
            break;
        case '<' :
            $result = $float < $right;
            break;
        case '>=' :
            $result = $float >= $right;
            break;
        case '<=' :
            $result = $float <= $right;
            break;
        case '==' :
            $result = $float == $right;
            break;
        case '!=' :
            $result = $float != $right;
            break;
        case '===' :
            $result = $float === $right;
            break;
        case '!==' :
            $result = $float !== $right;
            break;
        default:
            throw new Exception('Unknown equation');
    }
    return $result;
}
