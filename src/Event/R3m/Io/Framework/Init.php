<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;

use R3m\Io\Module\File;
use R3m\Io\Module\Route;

use Exception;

class Init {

    const FLAG_CONFIGURE_PUBLIC_CREATE = 'configure-public-create';
    const FLAG_RESTORE_PUBLIC_CREATE = 'restore-public-create';
    const FLAG_RESTORE_SSH = 'restore-ssh';
    const FLAG_RESTORE_LETS_ENCRYPT = 'restore-lets-encrypt';
    const FLAG_BACKUP_APACHE2 = 'backup-apache2';
    const FLAG_CONFIGURE_APACHE2 = 'configure-apache2';
    const FLAG_RESTORE_APACHE2 = 'restore-apache2';
    const FLAG_RESTART_APACHE2 = 'restart-apache2';
    const FLAG_CONFIGURE_DOCTRINE = 'configure-doctrine';
    const FLAG_RESTORE_DOCTRINE = 'restore-doctrine';
    const FLAG_DOCTRINE_ORM_GENERATE_PROXIES = 'doctrine-orm:generate-proxies';
    const FLAG_RESTART_PHP = 'restart-php';
    const FLAG_BACKUP_PHP = 'backup-php';
    const FLAG_RESTORE_PHP = 'restore-php';
    const FLAG_INSTALL_CRON = 'install-cron';
    const FLAG_RESTART_CRON = 'restart-cron';
    const FLAG_START_CRON = 'start-cron';
    const FLAG_RESTART_SYSTEM = 'restart-system';

    /**
     * @throws Exception
     */
    public static function run(App $object, $event, $options=[]): void
    {
        $flags = [];
        if(
            array_key_exists('flags', $options) &&
            is_object($options['flags']) &&
            property_exists($options['flags'], 'url') &&
            File::exist($options['flags']->url)
        ){
            $read = File::read($options['flags']->url);
            $flags = explode(PHP_EOL, $read);
            if(is_array($flags)){
                foreach($flags as $nr => $flag){
                    $flags[$nr] = trim($flag);
                    $flags[$nr] = ltrim($flags[$nr], '-');
                    if(empty($flag[$nr])){
                        unset($flags[$nr]);
                        continue;
                    }
                    $explode = explode('#', $flags[$nr], 2);
                    if(array_key_exists(1, $explode)){
                        $flags[$nr] = rtrim($explode[0]);
                    }
                    $explode = explode('//', $flags[$nr], 2);
                    if(array_key_exists(1, $explode)){
                        $flags[$nr] = rtrim($explode[0]);
                    }
                    if(substr($flags[$nr], 0, 1) === '#'){
                        unset($flags[$nr]);
                        continue;
                    }
                    if(substr($flags[$nr], 0, 2) === '//'){
                        unset($flags[$nr]);
                        continue;
                    }
                    if(empty($flag[$nr])){
                        unset($flags[$nr]);
                    }
                }
            }
            $flags = array_values($flags);
        }
        if(
            array_key_exists('flags', $options) &&
            is_object($options['flags'])
        ) {
            foreach ($options['flags'] as $flag => $value) {
                $flags[] = $flag;
            }
        }
        $url = $object->config('project.dir.data') .
            'Init' .
            $object->config('ds') .
            'Init' .
            $object->config('extension.json')
        ;
        $data = $object->data_read($url);
        if(!$data){
            return;
        }
        $list = $data->get('Init');
        foreach($list as $nr => $init){
            if(
                property_exists($init, 'name') &&
                property_exists($init, 'controller')
            ){
                if(in_array(
                    $init->name,
                    $flags,
                    true
                )){
                    $init = Route::controller($init);
                    $options['is_flag'] = true;
                    $init->controller::{$init->function}($object, $event, $options);
                }
            }
        }
    }
}
