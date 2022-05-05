<?php

declare(strict_types=1);

namespace Ruga\Db;

/**
 * ConfigProvider.
 *
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * @see    https://docs.mezzio.dev/mezzio/v3/features/container/config/
 */
class ConfigProvider
{
    public function __invoke()
    {
        return [
            'db' => [
//                'driver' => 'Pdo_Mysql',
                'host' => '127.0.0.1',
                'database' => '',
                'username' => '',
                'password' => '',
                'driver_options' => [
//                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                ],
                \Ruga\Db\Schema\Updater::class => [
//                    \Ruga\Db\Schema\Updater::CONF_DBTAG => '',
//                    \Ruga\Db\Schema\Updater::CONF_REQUESTED_VERSION => 0,
//                    \Ruga\Db\Schema\Updater::CONF_SCHEMA_DIRECTORY => __DIR__ . '/ruga-dbschema',
                ],
            ],
            
            'dependencies' => [
                'services' => [],
                'aliases' => [
                    \Ruga\Db\Adapter\AdapterInterface::class => \Ruga\Db\Adapter\Adapter::class,
                    \Laminas\Db\Adapter\AdapterInterface::class => \Ruga\Db\Adapter\Adapter::class,
                    \Ruga\Db\Table\TableManagerInterface::class => \Ruga\Db\Table\TableManager::class,
                    \Ruga\Db\Cache\MetadataCacheInterface::class => \Ruga\Db\Cache\MetadataCache::class,
                ],
                'factories' => [
                    \Ruga\Db\Adapter\Adapter::class => \Ruga\Db\Adapter\AdapterFactory::class,
                    \Ruga\Db\Cache\MetadataCache::class => \Ruga\Db\Cache\MetadataCacheFactory::class,
                    \Ruga\Db\Table\TableManager::class => \Ruga\Db\Table\TableManagerFactory::class,
                    
                ],
                'abstract_factories' => [
                    \Ruga\Db\Table\AbstractTableFactory::class,
                ],
                'invokables' => [],
                'delegators' => [],
            ],
        ];
    }
}
