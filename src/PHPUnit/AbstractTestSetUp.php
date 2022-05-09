<?php
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

declare(strict_types=1);

namespace Ruga\Db\PHPUnit;

use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Adapter\AdapterFactory;
use Ruga\Db\Schema\Updater;

/**
 * Common setup for all tests that use the database.
 */
abstract class AbstractTestSetUp extends TestCase
{
    private $config;
    
    /** @var Adapter */
    private $adapter;
    
    /** @var ServiceManager */
    private $container;
    
    
    
    protected function setUp(): void
    {
        $adapter = new \Ruga\Db\Adapter\Adapter($this->getConfig()['db']);
        
        $metadata = \Laminas\Db\Metadata\Source\Factory::createSourceFromAdapter($adapter);
        
        if (empty($metadata->getTableNames()) && empty($metadata->getViewNames()) && empty(
            $metadata->getTriggerNames()
            )) {
            \Ruga\Log::log_msg("Database '{$adapter->getCurrentSchema()}' is empty.");
        } else {
            $db_dbtag = Updater::getDbTag($adapter);
            $conf_dbtag = $this->getConfig()['db'][Updater::class][Updater::CONF_DBTAG];
            
            // Check if dbtag in database matches dbtag in config
            if ($conf_dbtag != $db_dbtag) {
                throw new \Exception(
                    "Database '{$adapter->getCurrentSchema()}' dbtag '{$db_dbtag}' does not match the dbtag of this test '{$conf_dbtag}'. NOT DELETING!"
                );
            }
            
            $query = "SET FOREIGN_KEY_CHECKS = 0;" . PHP_EOL;
            foreach ($metadata->getTableNames() as $table) {
                $query .= "DROP TABLE `{$table}`;" . PHP_EOL;
            }
            $query .= "SET FOREIGN_KEY_CHECKS = 1;" . PHP_EOL;
            
            // Clear the database
            \Ruga\Log::log_msg("Clearing database '{$adapter->getCurrentSchema()}'");
            $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        }
        
        // Start schema updater
        \Ruga\Db\Schema\Updater::update(
            $adapter,
            $this->getConfig()['db']
        );
    }
    
    
    
    /**
     * Return the cached config.
     * Adds a dbtag to the config.
     *
     * @return array
     */
    protected function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->configProvider();
            $this->config['db'][Updater::class][Updater::CONF_DBTAG] = self::class;
        }
        return $this->config;
    }
    
    
    
    /**
     * Return the test specific merged config.
     *
     * @return array
     */
    abstract public function configProvider();
    
    
    
    /**
     * Return the adapter.
     *
     * @return Adapter
     */
    public function getAdapter(): Adapter
    {
        if (!$this->adapter) {
            $this->adapter = (new AdapterFactory())($this->getContainer(), Adapter::class, null);
        }
        return $this->adapter;
    }
    
    
    
    /**
     * Create and return the service manager.
     *
     * @return ServiceManager
     */
    public function getContainer(): ServiceManager
    {
        if (!$this->container) {
            $dependencies = $this->getConfig()['dependencies'];
            $dependencies['services']['config'] = $this->getConfig();
            $this->container = new ServiceManager($dependencies);
        }
        return $this->container;
    }
}
