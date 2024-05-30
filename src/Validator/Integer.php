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
function validate_integer(App $object, $string='', $field='', $argument='', $function=false): bool
{
    $int = intval($string);
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
                case 'greater-than':
                case 'gt':
                    $result = $int > $right;
                    break;
                case '<' :
                case 'lower-than':
                case 'lt':
                    $result = $int < $right;
                    break;
                case '>=' :
                case 'greater-then-equal':
                case 'gte':
                    $result = $int >= $right;
                    break;
                case '<=' :
                case 'lower-then-equal':
                case 'lte':
                    $result = $int <= $right;
                    break;
                case '==' :
                case 'equal':
                case 'exact':
                    $result = $int == $right;
                    break;
                case '!=' :
                case 'not-equal':
                case 'not-exact':
                    $result = $int != $right;
                    break;
                case '===' :
                case 'strictly-equal':
                case 'strictly-exact':
                    $result = $int === $right;
                    break;
                case '!==' :
                case 'not-strictly-equal':
                case 'not-strictly-exact':
                    $result = $int !== $right;
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
            $result = $int > $right;
        break;
        case '<' :
            $result = $int < $right;
        break;
        case '>=' :
            $result = $int >= $right;
        break;
        case '<=' :
            $result = $int <= $right;
        break;                
        case '==' :
            $result = $int == $right;
        break;
        case '!=' :
            $result = $int != $right;
        break;
        case '===' :
            $result = $int === $right;
            break;
        case '!==' :
            $result = $int !== $right;
            break;
        default:
            throw new Exception('Unknown equation');
    }
    return $result;    
}
