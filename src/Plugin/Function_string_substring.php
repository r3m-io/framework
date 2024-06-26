    <?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-16
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_string_substring(Parse $parse, Data $data, $string='', $offset=0, $length=null){
    if($length === null){
        $result = substr($string, $offset);
    } else {
        $result = substr($string, $offset, $length);
    }
    return $result;
}
