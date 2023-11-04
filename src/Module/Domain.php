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

use R3m\Io\App;
use R3m\Io\Config;

class Domain {
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';

    const PORT_DEFAULT = [
        80,
        443
    ];

    /**
     * @throws \Exception
     */
    public static function configure(App $object): bool
    {
        if(defined('IS_CLI')){
            return false;
        }
        $key = 'domain.url';
        $value = Host::url();
        $object->config($key, $value);
        $subdomain = Host::subdomain();
        $port = Host::port();
        $key = 'domain.dir.root';

        $object->logger($object->config('project.log.system'))->info('port: ' . $port);

        if(empty($subdomain)){
            $sentence = strtolower($object->config('host.domain')) .
                '.' .
                strtolower($object->config('host.extension')) .
                $object->config('ds')
            ;
            $value = $object->config('project.dir.domain') .
                $sentence;
        } else {
            $sentence = strtolower($object->config('host.subdomain')) .
                '.' .
                strtolower($object->config('host.domain')) .
                '.' .
                strtolower($object->config('host.extension')) .
                $object->config('ds')
            ;
            $value = $object->config('project.dir.domain') .
                $sentence;
        }
        $object->config($key, $value);
        $key = 'domain.dir.data';
        $value =
            $object->config('domain.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::DATA) .
            $object->config('ds');
        if(!in_array($port, $object->config('server.default.port'))){
            $value .= $port . $object->config('ds');
        }
        $object->config($key, $value);
        $key = 'domain.dir.cache';
        $value =
            $object->config('framework.dir.temp') .
            $object->config(Config::DICTIONARY . '.' . Config::DOMAIN) .
            $object->config('ds');
        if(!in_array($port, $object->config('server.default.port'))){
            $value .= $port . $object->config('ds');
        }
        $object->config($key, $value);
        $key = 'domain.dir.public';
        $value =
            $object->config('domain.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::PUBLIC) .
            $object->config('ds');
        if(!in_array($port, $object->config('server.default.port'))){
            $value .= $port . $object->config('ds');
        }
        $object->config($key, $value);
        $key = 'domain.dir.source';
        $value =
            $object->config('domain.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::SOURCE) .
            $object->config('ds');
        if(!in_array($port, $object->config('server.default.port'))){
            $value .= $port . $object->config('ds');
        }
        $object->config($key, $value);
        $key = 'domain.dir.view';
        $value =
            $object->config('domain.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::VIEW) .
            $object->config('ds');
        if(!in_array($port, $object->config('server.default.port'))){
            $value .= $port . $object->config('ds');
        }
        $object->config($key, $value);
        return true;
    }
}