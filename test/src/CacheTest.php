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
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class CacheTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    public function configProvider()
    {
        $config = parent::configProvider();
        
        $config['db'][\Ruga\Db\Cache\MetadataCache::class] = [
            'adapter' => [
                'name' => \Laminas\Cache\Storage\Adapter\Filesystem::class,
                'options' => [
                    'cache_dir' => __DIR__ . '/../tmp',
                    'ttl' => 10,
                    'dir_level' => 0,
                ],
            ],
            'plugins' => [
                'exception_handler' => [
                    'throw_exceptions' => false,
                ],
                'serializer' => [],
            ],
        ];
        return $config;
    }
    
    
    
    public function testCanReadDbHash()
    {
        $dbhash = \Ruga\Db\Schema\Updater::getDbHash($this->getAdapter());
        print_r($dbhash);
        $this->assertIsString($dbhash);
    }
    
    
    
    public function testCanReadDbTag()
    {
        $dbtag = \Ruga\Db\Schema\Updater::getDbTag($this->getAdapter());
        print_r($dbtag);
        $this->assertIsString($dbtag);
    }
    
    
    
    public function testCanReadDbHashFromAdapter()
    {
        $dbhash = $this->getAdapter()->getDbHash();
        print_r($dbhash);
        $this->assertIsString($dbhash);
    }
    
    
    
    public function testCacheFileExists()
    {
        $t = new \Ruga\Db\Test\Model\MetaTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Meta $row */
        $row = $t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Meta::class, $row);
        $this->assertSame(1, $row->id);
        $this->assertSame('data 1', $row->data);
        
        $cacheKey = str_replace(
            ['\\', '/'],
            '_',
            implode(
                '-',
                [
                    \Ruga\Db\Table\Feature\MetadataFeature::class,
                    'metadata',
                    $t->getSchema(),
                    $t->getTable(),
                    $t->getAdapter()->getDbHash(),
                ]
            )
        );
        $this->assertFileExists(__DIR__ . "/../tmp/laminascache-{$cacheKey}.dat");
    }
    
}
