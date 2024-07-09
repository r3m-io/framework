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
namespace R3m\Io\Module\Parse;

use R3m\Io\Module\Data;

use Exception;

class Value {
    const TYPE_CAST_BOOLEAN = 'bool';
    const TYPE_CAST_INT = 'int';
    const TYPE_CAST_FLOAT = 'float';
    const TYPE_CAST_STRING = 'string';
    const TYPE_CAST_ARRAY = 'array';
    const TYPE_CAST_CLONE = 'clone';

    /**
     * @throws Exception
     */
    public static function get(Build $build, Data $storage, $record=[]): mixed
    {
        switch($record['type']){
            case Token::TYPE_INT :
            case Token::TYPE_FLOAT :
                return $record['execute'];
            case Token::TYPE_BOOLEAN :
            case Token::TYPE_NULL :
            case Token::TYPE_COMMA  :
            case Token::TYPE_DOT :
            case Token::TYPE_SEMI_COLON :
            case Token::TYPE_EXCLAMATION :
            case Token::TYPE_BRACKET_SQUARE_OPEN :
            case Token::TYPE_BRACKET_SQUARE_CLOSE :
            case Token::TYPE_PARENTHESE_OPEN :
            case Token::TYPE_PARENTHESE_CLOSE :
            case Token::TYPE_QUOTE_SINGLE_STRING :
            case Token::TYPE_BACKSLASH :
            case Token::TYPE_IS_PLUS :
            case Token::TYPE_IS_GREATER :
            case Token::TYPE_IS_GREATER_EQUAL :
            case Token::TYPE_IS_GREATER_GREATER :
            case Token::TYPE_IS_EQUAL :
            case Token::TYPE_IS_AND_EQUAL :
            case Token::TYPE_IS_ARRAY_OPERATOR :
            case Token::TYPE_IS_COALESCE :
            case Token::TYPE_IS_DIVIDE :
            case Token::TYPE_IS_DIVIDE_EQUAL:
            case Token::TYPE_IS_IDENTICAL :
            case Token::TYPE_IS_MINUS :
            case Token::TYPE_IS_MINUS_EQUAL :
            case Token::TYPE_IS_MINUS_MINUS :
            case Token::TYPE_IS_MODULO :
            case Token::TYPE_IS_MODULO_EQUAL :
            case Token::TYPE_IS_MULTIPLY :
            case Token::TYPE_IS_MULTIPLY_EQUAL :
            case Token::TYPE_IS_NOT_EQUAL :
            case Token::TYPE_IS_NOT_IDENTICAL :
            case Token::TYPE_IS_OBJECT_OPERATOR :
            case Token::TYPE_IS_OR_EQUAL :
            case Token::TYPE_IS_PLUS_EQUAL :
            case Token::TYPE_IS_PLUS_PLUS :
            case Token::TYPE_IS_POWER :
            case Token::TYPE_IS_POWER_EQUAL :
            case Token::TYPE_IS_SMALLER :
            case Token::TYPE_IS_SMALLER_EQUAL:
            case Token::TYPE_IS_SMALLER_SMALLER :
            case Token::TYPE_IS_SPACESHIP :
            case Token::TYPE_IS_XOR_EQUAL :
                return $record['value'];
            case Token::TYPE_CODE :
            case Token::TYPE_QUOTE_SINGLE :
            case Token::TYPE_STRING :
                $record['value'] = str_replace([
                    '{$ldelim}',
                    '{$rdelim}'
                ],[
                    '{',
                    '}'
                ], $record['value']);
                return $record['value'];
            case Token::TYPE_QUOTE_DOUBLE_STRING :
                if(str_contains($record['value'], '{') === false){
                    return $record['value'];
                }
                $record['value'] = str_replace('\\\'', '\'', $record['value']);
                $record['value'] = str_replace('\'', '\\\'', $record['value']);
                if($record['depth'] > 0){
                    return '$this->parse()->compile(\'' . substr($record['value'], 1, -1) . '\', [], $this->storage())';
                }
                elseif(!empty($record['is_assign'])){
                    return '$this->parse()->compile(\'' . substr($record['value'], 1, -1) . '\', [], $this->storage())';
                } else {
                    return '$this->parse()->compile(\'' . $record['value'] . '\', [], $this->storage())';
                }
            case Token::TYPE_CAST :
                return Value::getCast($record);
            case Token::TYPE_VARIABLE :
                //adding modifiers
                $token = [];
                $token[] = $record;
                return Variable::define($build, $storage, $token);
            case Token::TYPE_METHOD :
                $method = Method::get($build, $storage, $record);
                if($method['type'] == Token::TYPE_CODE){
                    return $method['value'];
                } else {
                    if(empty($record['method']['trait'])){
                        return '$this->' . $record['method']['php_name'] . '($this->parse(), $this->storage())';
                    } else {
                        $trait_name = str_replace('function_', '', $record['method']['php_name']);
                        return '$this->' . $trait_name . '()';
                    }
                }
            case Token::TYPE_COMMENT :
            case Token::TYPE_DOC_COMMENT :
                return '\'\'';
            case Token::TYPE_WHITESPACE :
            case Token::TYPE_CURLY_CLOSE :
            case Token::TYPE_CURLY_OPEN :
                return null;
            default:
                throw new Exception('Variable value type ' .  $record['type'] . ' not defined');
        }
    }

    /**
     * @throws Exception
     */
    private static function getCast($record=[]): string
    {
        switch(strtolower($record['value'])){
            case 'bool':
            case 'boolean':
                $result = Value::TYPE_CAST_BOOLEAN;
            break;
            case 'int':
            case 'integer':
                $result = Value::TYPE_CAST_INT;
            break;
            case 'float':
            case 'double':
                $result = Value::TYPE_CAST_FLOAT;
            break;
            case 'string':
                $result = Value::TYPE_CAST_STRING;
            break;
            case 'array':
                $result = Value::TYPE_CAST_ARRAY;
            break;
            case 'clone':
                $result = Value::TYPE_CAST_CLONE . ' ';
                return $result;
            default:
                throw new Exception('could not create cast: ' . $record['value']);
        }
        return '(' . $result . ')';
    }

    public static function line_contains_replace($contains=[], $replace=[], $string): mixed
    {
        if(!is_string($string)){
            return $string;
        }
        $lines = explode(PHP_EOL, $string);
        foreach($lines as $nr => $line){
            $pos = [];
            $count = 0;
            $is_collect = false;
            foreach($contains as $nr_contains => $contain){
                $trim = [];
                foreach($contain as $word_index => $word){
                    if($word === 'whitespace'){
                        $trim[$word_index] = ltrim($line, "\n\t\r ");
                        if(array_key_exists($word_index + 1, $contain)){
                            $next_word = $contain[$word_index + 1];
                        } else {
                            $next_word = false;
                        }
                        if($trim[$word_index] !== $line){
                            if(substr($trim[$word_index], 0, 1) === $next_word){
                                $pos[$nr_contains][$word_index] = 0;
                            } else {
                                $pos[$nr_contains][$word_index] = false;
                            }
                        } else {
                            $pos[$nr_contains][$word_index] = false;
                        }
                    } else {
                        $chars = mb_str_split($line);
                        $previous_char = false;
                        $is_single_quote = false;
                        $is_double_quote = false;
                        $line_check = '';
                        foreach($chars as $char_nr => $char){
                            if(
                                (
                                    $previous_char === false ||
                                    $previous_char !== '\\'
                                ) &&
                                $char === '\''
                            ){
                                if(
                                    $is_single_quote === false &&
                                    $is_double_quote === false
                                ){
                                    $is_single_quote = true;
                                }
                                elseif($is_single_quote === true){
                                    $is_single_quote = false;
                                }
                            }
                            if((
                                    $previous_char === false ||
                                    $previous_char !== '\\'
                                ) &&
                                $char === '"'
                            ){
                                if(
                                    $is_single_quote === false &&
                                    $is_double_quote === false
                                ){
                                    $is_double_quote = true;
                                }
                                elseif($is_double_quote === true){
                                    $is_double_quote = false;
                                }
                            }
                            if(
                                $is_single_quote === false &&
                                $is_double_quote === false
                            ){
                                $line_check .= $char;
                            } else {
                                $line_check = ' ';
                            }
                            $previous_char = $char;
                        }
                        $pos[$nr_contains][$word_index] = strpos($line_check, $word);
                        if(str_contains($line_check,'try')){
                            $is_collect = true;
                            d($line_check);
                            ddd($pos);
                        }
                    }
                    $count++;
                }
            }
            if($is_collect){
                d($pos);
            }

            foreach($pos as $nr_contains => $sublist){
                $is_break = false;
                $previous_pos = false;
                foreach($sublist as $word_index => $position){
                    if($position === false){
                        $is_break = true;
                        break;
                    }
                    if(
                        $previous_pos &&
                        $position < $previous_pos
                    ){
                        $is_break = true;
                        break;
                    }
                    $previous_pos = $position;
                }
                if($is_break === false){
                    $lines[$nr] = str_replace($replace[$nr_contains][0], $replace[$nr_contains][1], $lines[$nr]);
                }
            }
        }
        return implode(PHP_EOL, $lines);
    }
}