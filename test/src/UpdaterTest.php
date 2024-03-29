<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;
use PHPUnit\Framework\TestCase;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Adapter\AdapterInterface;
use Ruga\Db\Schema\Updater;

/**
 * This test does not use the automatic database test provided by \Ruga\Db\Test\PHPUnit\AbstractTestSetUp.
 *
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class UpdaterTest extends TestCase
{
    private $config;
    
    
    
    /**
     * Return the cached config.
     * Adds a dbtag to the config.
     *
     * @return array
     */
    protected function getConfig(): array
    {
        if (!$this->config) {
            $config = new ConfigAggregator(
                [
                    new PhpFileProvider(__DIR__ . "/../config/config.php"),
                    new PhpFileProvider(__DIR__ . "/../config/config.local.php"),
                ], null, []
            );
            $this->config = $config->getMergedConfig();
            $this->config['db'][Updater::class][Updater::CONF_DBTAG] = \Ruga\Db\PHPUnit\AbstractTestSetUp::class;
        }
        return $this->config;
    }
    
    
    
    protected function clearDb(): void
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
    }
    
    
    
    public function testCanUpdateSchemaWithEmptyConfig(): void
    {
        $this->clearDb();
        
        $config = $this->getConfig();
        $config['db'][\Ruga\Db\Schema\Updater::class][\Ruga\Db\Schema\Updater::CONF_REQUESTED_VERSION] = 0;
        
        $adapter = new Adapter($config['db']);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        
        \Ruga\Db\Schema\Updater::update(
            $adapter,
            $config['db']
        );
        
        $db_dbtag = Updater::getDbTag($adapter);
        echo "Current dbtag: '{$db_dbtag}'" . PHP_EOL;
        $this->assertEquals($config['db'][Updater::class][Updater::CONF_DBTAG], $db_dbtag);
        
        $db_version = Updater::getDbVersion($adapter);
        echo "Current dbversion: '{$db_version}'" . PHP_EOL;
        $this->assertEquals($config['db'][Updater::class][Updater::CONF_REQUESTED_VERSION], $db_version);
    }
    
    
    
    public function testCanUpdateSchema(): void
    {
        $this->clearDb();
        
        $config = $this->getConfig();
//        $config['db'][\Ruga\Db\Schema\Updater::class][\Ruga\Db\Schema\Updater::CONF_REQUESTED_VERSION] = 0;
        
        $adapter = new Adapter($config['db']);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        
        \Ruga\Db\Schema\Updater::update(
            $adapter,
            $config['db']
        );
        
        $db_dbtag = Updater::getDbTag($adapter);
        echo "Current dbtag: '{$db_dbtag}'" . PHP_EOL;
        $this->assertEquals($config['db'][Updater::class][Updater::CONF_DBTAG], $db_dbtag);
        
        $db_version = Updater::getDbVersion($adapter);
        echo "Current dbversion: '{$db_version}'" . PHP_EOL;
        $this->assertEquals($config['db'][Updater::class][Updater::CONF_REQUESTED_VERSION], $db_version);
    }
    
    
    
    public function testCanDetectSchemaUpdateFailed()
    {
        $this->clearDb();
        
        $config = $this->getConfig();
        $config['db'][\Ruga\Db\Schema\Updater::class][\Ruga\Db\Schema\Updater::CONF_SCHEMA_DIRECTORY] = __DIR__ . '/../config/ruga-dbschema-with-error';
        $config['db'][\Ruga\Db\Schema\Updater::class][\Ruga\Db\Schema\Updater::CONF_REQUESTED_VERSION] = 2;
        
        $adapter = new Adapter($config['db']);
        $adapter->query('SET SESSION sql_mode="ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"')->execute();
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        
        $this->expectException(\Ruga\Db\Schema\Exception\SchemaUpdateFailedException::class);
        \Ruga\Db\Schema\Updater::update(
            $adapter,
            $config['db']
        );
    }
    
}
