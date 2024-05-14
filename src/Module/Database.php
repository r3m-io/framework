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

use Doctrine\DBAL\Schema\SQLiteSchemaManager;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use Doctrine\DBAL\Schema\PostgreSqlSchemaManager;
use Doctrine\DBAL\Schema\SqlServerSchemaManager;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Platforms\PostgresSQLPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\MariaDBPlatform;

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
//        $connection = DriverManager::getConnection($connection, $config, new EventManager());
        $connection = DriverManager::getConnection($connection, $config);
        $eventManager = new EventManager();
        return new EntityManager($connection, $config, $eventManager);
//        return EntityManager::create($connection, $config);
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
        if(
            array_key_exists('name', $options) &&
            !empty($options['name'])
        ){
            $name = $options['name'];
        }
        $app_cache = $object->get(App::CACHE);
        if($app_cache){
            $entityManager = $app_cache->get(Database::NAME . '.entityManager.' . $name . '.' . $environment);
            if(!$entityManager){
                $environment === '*';
                $entityManager = $app_cache->get(Database::NAME . '.entityManager.' . $name . '.' . $environment);
            }
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
        $environment = '*';
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
    public static function instance(App $object, $name, $environment=null): void
    {
        if($environment === null){
            $environment = $object->config('framework.environment');
        }
        $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
        if($connect === null){
            $environment = '*';
            $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
            if($connect === null){
                return;
            }
        }
        $app_cache = $object->data(App::CACHE);
        $key = 'doctrine.instance.' . $name . '.' . $environment;
        if($app_cache->has($key)){
            return;
        }
        $connection = false;
        $cache = null;
        if(
            property_exists($connect, 'driver') &&
            $connect->driver === 'pdo_sqlite'
        ){
            $parameters = [];
            $parameters[] = $connect->path;
            $parameters = Config::parameters($object, $parameters);
            if(array_key_exists(0, $parameters)){
                $connect->path = $parameters[0];
            }
            $config = Database::config($object);
            $entity_manager = Database::connect($object, $config, $connect);
        } else {
            $entity_manager = Database::entityManager($object, [
                'name' => $name
            ]);
        }
        if($entity_manager) {
            $cache = (object)[
                'entity' => (object)[
                    'manager' => $entity_manager
                ]
            ];
            $app_cache->set($key, $cache);
            $connection = $entity_manager->getConnection();
        }
        if($connection){
            $platform = $connection->getDatabasePlatform();
            $schema_manager = $connection->createSchemaManager();
            $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
            if(property_exists($cache, 'entity')){
                $cache->connection = $connection;
                $cache->platform = $platform;
                $cache->schema = (object) [
                    'manager' => $schema_manager
                ];
                $app_cache->set($key, $cache);
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function entity_manager(App $object, $name, $environment=null): bool | EntityManager
    {
        if(empty($environment)){
            $environment = $object->config('framework.environment');
        }
        $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
        if(empty($connect)){
            $environment = '*';
            $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
            if(empty($connect)){
                return false;
            }
        }
        $app_cache = $object->data(App::CACHE);
        $key = 'doctrine.instance.' . $name . '.' . $environment;
        if($app_cache->has($key)){
            $cache = $app_cache->get($key);
            if(
                property_exists($cache, 'entity') &&
                property_exists($cache->entity, 'manager')
            ){
                return $cache->entity->manager;
            }
        } else {
            throw new Exception('No instance found for ' . $name . '.' . $environment);
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function connection(App $object, $name, $environment=null): bool | Connection
    {
        if(empty($environment)){
            $environment = $object->config('framework.environment');
        }
        $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
        if(empty($connect)){
            $environment = '*';
            $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
            if(empty($connect)){
                return false;
            }
        }
        $app_cache = $object->data(App::CACHE);
        $key = 'doctrine.instance.' . $name . '.' . $environment;
        if($app_cache->has($key)) {
            $cache = $app_cache->get($key);
            if (
                property_exists($cache, 'connection')
            ) {
                return $cache->connection;
            }
        } else {
            throw new Exception('No instance found for ' . $name . '.' . $environment);
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function platform(App $object, $name, $environment=null): bool | MySQLPlatform | SQLitePlatform | SQLServerPlatform | PostgresSQLPlatform | OraclePlatform | MariaDBPlatform
    {
        d($name);
        d($environment);
        if(empty($environment)){
            $environment = $object->config('framework.environment');
        }
        $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
        if(empty($connect)){
            $environment = '*';
            $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
            if(empty($connect)){
                return false;
            }
        }
        $app_cache = $object->data(App::CACHE);
        $key = 'doctrine.instance.' . $name . '.' . $environment;
        if($app_cache->has($key)) {
            $cache = $app_cache->get($key);
            d($cache);
            if (
                property_exists($cache, 'platform')
            ) {
                return $cache->platform;
            }
        } else {
            throw new Exception('No instance found for ' . $name . '.' . $environment);
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function schema_manager(App $object, $name, $environment=null): bool | SQLiteSchemaManager | MySqlSchemaManager | PostgreSqlSchemaManager | SqlServerSchemaManager
    {
        if(empty($environment)){
            $environment = $object->config('framework.environment');
        }
        $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
        if(empty($connect)){
            $environment = '*';
            $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
            if(empty($connect)){
                return false;
            }
        }
        $app_cache = $object->data(App::CACHE);
        $key = 'doctrine.instance.' . $name . '.' . $environment;
        if($app_cache->has($key)) {
            $cache = $app_cache->get($key);
            if (
                property_exists($cache, 'schema') &&
                property_exists($cache->schema, 'manager')
            ) {
                return $cache->schema->manager;
            }
        } else {
            throw new Exception('No instance found for ' . $name . '.' . $environment);
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function options(App $object, $options=null, $name=null, $environment=null, $table=null, &$count=0, &$is_install=false): void
    {
        $count = 0;
        $is_install = false;

        if(empty($environment)){
            $environment = $object->config('framework.environment');
        }
        $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
        if(empty($connect)){
            $environment = '*';
            $connect = $object->config('doctrine.environment.' . $name . '.' . $environment);
            if(empty($connect)){
                return;
            }
        }
        $app_cache = $object->data(App::CACHE);
        $key = 'doctrine.instance.' . $name . '.' . $environment;
        $instance = $app_cache->get($key);
        if(!$instance){
            return;
        }
        if(property_exists($instance, 'connection') === false){
            return;
        }
        if(property_exists($instance, 'schema') === false){
            return;
        }
        if(property_exists($instance->schema, 'manager') === false){
            return;
        }
        Core::interactive();
        $connection = $instance->connection;
        $schema_manager = $instance->schema->manager;
        if ($schema_manager->tablesExist([ $table ]) === true){
            if(
                property_exists($options, 'drop') &&
                $options->drop === true
            ){

                /* not working in sqlite3
                $sql = 'DROP TABLE :table ;';
                $connection->executeStatement($sql, [
                    'table' => $table
                ]);
                */
                $sanitized_table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
                // Construct the SQL query with the sanitized table name
                $sql = "DROP TABLE IF EXISTS $sanitized_table ;";
                $stmt = $connection->prepare($sql);
                $stmt->executeStatement();
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
                /* not working
                $sql = 'RENAME TABLE :old_table TO :new_table ;';
                $stmt = $connection->prepare($sql);
                $stmt->bindValue('old_table', $table);
                $stmt->bindValue('new_table', $options->rename);
                $stmt->executeStatement();
                */

                // Sanitize and validate the table names (e.g., removing any unwanted characters)
                $sanitized_table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
                $sanitized_rename = preg_replace('/[^a-zA-Z0-9_]/', '', $options->rename);
                // Construct the SQL query with the sanitized table names
                $sql = "RENAME TABLE $sanitized_table TO $sanitized_rename";
                $stmt = $connection->prepare($sql);
                $stmt->executeStatement();
                echo 'Renamed: ' . $sanitized_table . ' into ' . $sanitized_rename . '.' . PHP_EOL;
                $is_install = true;
                $count++;
            }
        } else {
            $is_install = true;
        }
    }

}