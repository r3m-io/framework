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

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Doctrine\DBAL\Logging;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;

use Doctrine\ORM\ORMSetup;

use R3m\Io\App;
use R3m\Io\Config;

use Exception;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

class Database {
    const NAMESPACE = __NAMESPACE__;
    const NAME = 'Database';

    const LOGGER_DOCTRINE = 'Doctrine';

    /**
     * @throws Exception
     */
    public static function config(App $object){
        $paths = $object->config('doctrine.paths');
        $paths = Config::parameters($object, $paths);
        $parameters = [];
        $parameters[] = $object->config('doctrine.proxy.dir');
        $parameters = Config::parameters($object, $parameters);
        if(array_key_exists(0, $parameters)){
            $proxyDir = $parameters[0];
        }
        if(empty($paths)){
            return false;
        }
        if(empty($proxyDir)){
            return false;
        }
        $cache = null;
        return ORMSetup::createAnnotationMetadataConfiguration($paths, false, $proxyDir, $cache);
    }

    /**
     * @throws Exception
     */
    public static function connect(App $object, $config, $connection=[]): EntityManager
    {
        $connection = Core::object($connection, CORE::OBJECT_ARRAY);
        $parameters = [];
        $parameters[] = $connection;
        $parameters = Config::parameters($object, $parameters);
        if(array_key_exists(0, $parameters)){
            $connection = $parameters[0];
        }
        if(!empty($connection['logging'])){
            $logger = new Logger(Database::LOGGER_DOCTRINE);
            $logger->pushHandler(new StreamHandler($object->config('project.dir.log') . 'sql.log', Logger::DEBUG));
            $logger->pushProcessor(new PsrLogMessageProcessor(null, true));
            $object->logger($logger->getName(), $logger);
            $logger->info('Logger initialised.');
            $config->setMiddlewares([new Logging\Middleware($logger)]);
        }
        if(
            array_key_exists('driver', $connection) &&
            $connection['driver'] === 'pdo_sqlite' &&
            array_key_exists('path', $connection) &&
            !File::exist($connection['path'])
        ){
            $dir = Dir::name($connection['path']);
            Dir::create($dir, Dir::CHMOD);
            File::write($connection['path'], '');
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 777 ' . $dir);
                exec('chmod 666 ' . $connection['path']);
                exec('chown www-data:www-data ' . $dir);
                exec('chown www-data:www-data ' . $connection['path']);
            } else {
                exec('chmod 750 ' . $dir);
                exec('chmod 640 ' . $connection['path']);
                exec('chown www-data:www-data ' . $dir);
                exec('chown www-data:www-data ' . $connection['path']);
            }
        }
        $connection = DriverManager::getConnection($connection, $config, new EventManager());
        return EntityManager::create($connection, $config);
    }


    /**
     * @deprecated use Database::config && Database::connect
     */
    public static function entityManager(App $object, $options=[]): ?EntityManager
    {
        $environment = $object->config('framework.environment');
        if(empty($environment)){
            $environment = Config::MODE_DEVELOPMENT;
        }
        if(array_key_exists('environment', $options)){
            $environment = $options['environment'];
        }
        $name = $object->config('framework.api');
        if(array_key_exists('name', $options)){
            $name = $options['name'];
        }
        $entityManager = $object->get(Database::NAME . '.entityManager.' . $name . '.' . $environment);
        if(!empty($entityManager)){
            return $entityManager;
        }
        $connection = $object->config('doctrine.' . $name . '.' . $environment);
        if(!empty($connection)){
            $connection = (array) $connection;
            if(empty($connection)){
                $logger = new Logger(Database::LOGGER_DOCTRINE);
                $logger->pushHandler(new StreamHandler($object->config('project.dir.log') . 'sql.log', Logger::DEBUG));
                $logger->pushProcessor(new PsrLogMessageProcessor(null, true));
                $object->logger($logger->getName(), $logger);
                $logger->error('Error: No connection string...');
                return null;
            }
            $paths = $object->config('doctrine.paths');
            $paths = Config::parameters($object, $paths);
            $parameters = [];
            $parameters[] = $object->config('doctrine.proxy.dir');
            $parameters = Config::parameters($object, $parameters);
            if(array_key_exists(0, $parameters)){
                $proxyDir = $parameters[0];
            }
            $cache = null;
            $config = ORMSetup::createAnnotationMetadataConfiguration($paths, false, $proxyDir, $cache);

            if(!empty($connection['logging'])){
                $logger = new Logger(Database::LOGGER_DOCTRINE);
                $logger->pushHandler(new StreamHandler($object->config('project.dir.log') . 'sql.log', Logger::DEBUG));
                $logger->pushProcessor(new PsrLogMessageProcessor(null, true));
                $object->logger($logger->getName(), $logger);
                $logger->info('Logger initialised.');
                $config->setMiddlewares([new Logging\Middleware($logger)]);
            }
            $connection = DriverManager::getConnection($connection, $config, new EventManager());
            $em = EntityManager::create($connection, $config);
            $object->set(Database::NAME .'.entityManager.' . $name . '.' . $environment, $em);
            return $em;
        }
        return null;
    }
}