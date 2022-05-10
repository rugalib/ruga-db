<?php

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Adapter\AdapterFactory;
use Ruga\Db\Adapter\AdapterInterface;
use Ruga\Db\Adapter\Exception\WrongDbVersionException;
use Ruga\Db\Schema\Updater;

/**
 * This test does not use the automatic database test provided by \Ruga\Db\Test\PHPUnit\AbstractTestSetUp.
 *
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class AdapterTest extends TestCase
{
    private $config;
    
    
    
    /**
     * Return the cached config.
     * Adds a dbtag to the config.
     *
     * @return array
     */
    protected function getConfig()
    {
        if (!$this->config) {
            $config = new ConfigAggregator(
                [
                    new \Ruga\Db\ConfigProvider(),
                    new PhpFileProvider(__DIR__ . "/../config/config.php"),
                    new PhpFileProvider(__DIR__ . "/../config/config.local.php"),
                ], null, []
            );
            $this->config = $config->getMergedConfig();
            $this->config['db'][Updater::class][Updater::CONF_DBTAG] = self::class;
        }
        return $this->config;
    }
    
    
    
    public function testCanCreateAdapter(): void
    {
        $config = $this->getConfig();
        $adapter = new Adapter($config['db']);
        $this->assertInstanceOf(\Laminas\Db\Adapter\Adapter::class, $adapter);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertInstanceOf(Adapter::class, $adapter);
        
        echo "Current schema: '{$adapter->getCurrentSchema()}'";
        $this->assertEquals($config['db']['database'], $adapter->getCurrentSchema());
    }
    
    
    
    public function testCanSetGlobalAdapterFeature(): void
    {
        $config = $this->getConfig();
        $adapter = new Adapter($config['db']);
        GlobalAdapterFeature::setStaticAdapter($adapter);
        $this->assertInstanceOf(AdapterInterface::class, GlobalAdapterFeature::getStaticAdapter());
    }
    
    
    
    public function testCanCreateAdapterByFactory(): void
    {
        // First, create a container
        $dependencies = $this->getConfig()['dependencies'];
        $dependencies['services']['config'] = $this->getConfig();
        $container = new ServiceManager($dependencies);
        $this->assertInstanceOf(ServiceManager::class, $container);
        
        // An adapter is created by the factory. It uses cache, table manager and checks the database version.
        $adapter = (new AdapterFactory())($container, Adapter::class, [AdapterFactory::DISABLE_VERSION_CHECK]);
        $this->assertInstanceOf(Adapter::class, $adapter);
    }
    
    
}
