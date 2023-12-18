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
use R3m\Io\Module\Dir;

function function_package_dir(Parse $parse, Data $data, $prefix='', $package=''){
    $explode = explode('_', $package);
    foreach($explode as $nr => $value){
        $explode[$nr] = ucfirst($value);
    }
    $package = implode('/', $explode);
    $package = Dir::ucfirst($package);
    ddd($package);
}
