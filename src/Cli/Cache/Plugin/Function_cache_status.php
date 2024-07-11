<?php

use R3m\Io\Config;

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;

function function_cache_status(Parse $parse, Data $data){
    $object = $parse->object();
    phpinfo(INFO_CONFIGURATION);
    phpinfo(INFO_MODULES);
}
