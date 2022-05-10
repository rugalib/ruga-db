<?php

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
    
    
    
    public function testCanUpdateSchemaWithEmptyConfig(): void
    {
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
        $config = $this->getConfig();
        $config['db'][\Ruga\Db\Schema\Updater::class][\Ruga\Db\Schema\Updater::CONF_SCHEMA_DIRECTORY] = __DIR__ . '/../config/ruga-dbschema-with-error';
        $config['db'][\Ruga\Db\Schema\Updater::class][\Ruga\Db\Schema\Updater::CONF_REQUESTED_VERSION] = 2;
        
        $adapter = new Adapter($config['db']);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        
        $this->expectException(\Ruga\Db\Schema\Exception\SchemaUpdateFailedException::class);
        \Ruga\Db\Schema\Updater::update(
            $adapter,
            $config['db']
        );
    }
    
}
