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
        return ORMSetup::createAttributeMetadataConfiguration($paths, false, $proxyDir, $cache);
    }

    /**
     * @throws Exception
     */
    public static function connect(App $object, $config, $connection=[]): EntityManager
    {
        $connection = Core::object($connection, Core::OBJECT_OBJECT);
        if(property_exists($connection, 'path')){
            $parameters = [];
            $parameters[] = $connection->path;
            $parameters = Config::parameters($object, $parameters);
            if(array_key_exists(0, $parameters)){
                $connection->path = $parameters[0];
            }
        }
        if (
            property_exists($connection, 'logging') &&
            !empty($connection->logging)
        ){
            $logger = new Logger(Database::LOGGER_DOCTRINE);
            $logger->pushHandler(new StreamHandler($object->config('project.dir.log') . 'sql.log', Logger::DEBUG));
            $logger->pushProcessor(new PsrLogMessageProcessor(null, true));
            $object->logger($logger->getName(), $logger);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                $logger->info('Logger initialised.');
            }
            $config->setMiddlewares([new Logging\Middleware($logger)]);
        }
        if(
            property_exists($connection, 'driver') &&
            $connection->driver === 'pdo_sqlite' &&
            property_exists($connection, 'path') &&
            !File::exist($connection->path)
        ){
            $dir = Dir::name($connection->path);
            Dir::create($dir, Dir::CHMOD);
            $command = 'sqlite3 ' . $connection->path . ' "VACUUM;"';
            exec($command);
            File::permission($object, [
                'dir' => $dir,
                'file' => $connection->path
            ]);
        }
        $connection = Core::object($connection, Core::OBJECT_ARRAY);
        $connection = DriverManager::getConnection($connection, $config, new EventManager());
        return EntityManager::create($connection, $config);
    }


    /**
     * @throws Exception
     */
    public static function entityManager(App $object, $options=[]): ?EntityManager
    {
        $environment = $object->config('framework.environment');
        if(empty($environment)){
            $environment = Config::MODE_DEVELOPMENT;
        }
        $options = Core::object($options, Core::OBJECT_ARRAY);
        if(array_key_exists('environment', $options)){
            $environment = $options['environment'];
        }
        $name = $object->config('framework.api');
        if(array_key_exists('name', $options)){
            $name = $options['name'];
        }
        $app_cache = $object->get(App::CACHE);
        if($app_cache){
            $entityManager = $app_cache->get(Database::NAME . '.entityManager.' . $name . '.' . $environment);
        }
        if(!empty($entityManager)){
            return $entityManager;
        }
        $connection = $object->config('doctrine.environment.' . $name . '.' . $environment);
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
            $proxy_dir = false;
            if(array_key_exists(0, $parameters)){
                $proxy_dir = $parameters[0];
            }
            $cache = null;
            if($proxy_dir) {
                $config = ORMSetup::createAttributeMetadataConfiguration($paths, false, $proxy_dir, $cache);
                if (!empty($connection['logging'])) {
                    $logger = new Logger(Database::LOGGER_DOCTRINE);
                    $logger->pushHandler(new StreamHandler($object->config('project.dir.log') . 'sql.log', Logger::DEBUG));
                    $logger->pushProcessor(new PsrLogMessageProcessor(null, true));
                    $object->logger($logger->getName(), $logger);
                    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                        $logger->info('Logger initialised.');
                    }
                    $config->setMiddlewares([new Logging\Middleware($logger)]);
                }
                $connection = DriverManager::getConnection($connection, $config);
                $eventManager = new EventManager();
                $em = new EntityManager($connection, $config, $eventManager);
                $app_cache->set(Database::NAME . '.entityManager.' . $name . '.' . $environment, $em);
                return $em;
            }
        }
        return null;
    }

    /**
     * @throws Exception
     */
    public static function instance(App $object, &$entity_manager=null, &$connection=null, &$platform=null, &$schema_manager=null): void
    {
        $entity_manager = Database::entityManager($object);
        if($entity_manager){
            $connection = $entity_manager->getConnection();
        }
        if($connection){
            $platform = $connection->getDatabasePlatform();
            $schema_manager = $connection->createSchemaManager();
        }
    }

    /**
     * @throws Exception
     */
    public static function options(App $object, $connection, $schema_manager, $options=null, $table=null, &$count=0, &$is_install=false): void
    {
        $count = 0;
        $is_install = false;
        if ($schema_manager->tablesExist([ $table ]) === true){
            if(
                property_exists($options, 'drop') &&
                $options->drop === true
            ){
                $sql = 'DROP TABLE :table ;';
                $connection->executeStatement($sql, [
                    'table' => $table
                ]);
                echo 'Dropped: ' . $table . '.' . PHP_EOL;
                $is_install = true;
                $count++;
            }
            if(
                property_exists($options, 'truncate') &&
                $options->truncate === true
            ){
                $sql = 'TRUNCATE TABLE :table ;';
                $connection->executeStatement($sql , [
                    'table' => $table
                ]);
                echo 'Truncated: ' . $table . '.' . PHP_EOL;
                $is_install = true;
                $count++;
            }
            if(
                property_exists($options, 'rename') &&
                (
                    is_string($options->rename) ||
                    is_bool($options->rename)
                )
            ){
                d($options);
                if($options->rename === true){
                    $options->rename = $table . '_old';
                    $counter = 1;
                    while(true){
                        if($schema_manager->tablesExist([$options->rename]) === false){
                            break;
                        }
                        $options->rename = $table . '_old_' . $counter;
                        $counter++;
                        if(
                            $counter >= PHP_INT_MAX ||
                            $counter < 0
                        ){
                            throw new Exception('Out of range.');
                        }
                    }
                }
                // Chat gtp says that a RENAME table cannot contain parameterized table names and that is not working.
                // Sanitize and validate the table names (e.g., removing any unwanted characters)
                $sanitizedTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
                $sanitizedRename = preg_replace('/[^a-zA-Z0-9_]/', '', $options->rename);

                // Construct the SQL query with the sanitized table names
                $sql = "RENAME TABLE $sanitizedTable TO $sanitizedRename ;";
                $stmt = $connection->prepare($sql);
                $stmt->executeStatement();
                /*
                $connection->executeStatement($sql, [
                    'table' => $table,
                    'rename' => $options->rename
                ]);
                */
                echo 'Renamed: ' . $table . ' into ' . $options->rename . '.' . PHP_EOL;
                $is_install = true;
                $count++;
            }
        } else {
            $is_install = true;
        }
    }

}