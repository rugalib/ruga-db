<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Table;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Psr\Container\ContainerInterface;
use Ruga\Db\Cache\MetadataCache;
use Ruga\Db\Cache\MetadataCacheInterface;
use Ruga\Db\Schema\Updater;

/**
 * @see     TableManager
 * @author  Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class TableManagerFactory
{
    public function __invoke(ContainerInterface $container): TableManager
    {
        /** @var StorageInterface $cache */
        $cache = $container->get(MetadataCacheInterface::class);
        $cacheKey = MetadataCache::prepareCacheKey([TableManager::class, 'config']);
        
        if (!($config = $cache->getItem($cacheKey))) {
            $aggregator = new ConfigAggregator(
                [
                    new ArrayProvider(
                        [
                            'aliases' => [],
                            'factories' => [],
                            'abstract_factories' => [AbstractTableFactory::class],
                        ]
                    ),
                    
                    // merge TableManager configuration
                    new ArrayProvider(($container->get('config') ?? [])['db'][TableManager::class] ?? []),
                    
                    // merge global service manager aliases
                    new ArrayProvider(
                        [
                            'aliases' =>
                                array_filter(
                                    ($container->get('config') ?? [])['dependencies']['aliases'] ?? [],
                                    function (string $value) {
                                        if (!class_exists($value, true)) {
                                            return false;
                                        }
                                        return in_array(TableInterface::class, class_implements($value, true));
                                    },
                                    0
                                ),
                        ]
                    ),
                    
                    // merge global service manager factories
                    new ArrayProvider(
                        [
                            'factories' =>
                                array_filter(
                                    ($container->get('config') ?? [])['dependencies']['factories'] ?? [],
                                    function (string $key) {
                                        if (!class_exists($key, true)) {
                                            return false;
                                        }
                                        return in_array(TableInterface::class, class_implements($key, true));
                                    },
                                    ARRAY_FILTER_USE_KEY
                                ),
                        ]
                    ),
                    
                    // merge component tables
                    new ArrayProvider(
                        ['aliases' => ($container->get('config') ?? [])['db'][Updater::class]['tables'] ?? []]
                    ),
                    new ArrayProvider(
                        [
                            'aliases' => (function (array $components) use ($container): array {
                                $a = [];
                                foreach ($components as $componentName => $componentConfig
                                ) {
                                    foreach ($componentConfig['tables'] ?? [] as $key => $value) {
                                        $a[$key] = $value;
                                    }
                                }
                                return $a;
                            })(
                                ($container->get('config') ?? [])['db'][Updater::class]['components'] ?? []
                            ),
                        ]
                    ),
                ]
            );
            
            $config = $aggregator->getMergedConfig();
    
            // Complete factories from aliases
            foreach ($config['aliases'] as $key => $val) {
                if (class_exists($val, true)) {
                    $config['factories'][$val] = AbstractTableFactory::class;
                }
            }
    
            // Complete aliases from aliases
            foreach ($config['aliases'] as $key => $val) {
                if (class_exists($val, true)) {
                    $refClass = new \ReflectionClass($val);
                    $tableName = $refClass->getConstant('TABLENAME');
                    $config['aliases'][$tableName] = $val;
                    $shortClassName = $refClass->getShortName();
                    $config['aliases'][$shortClassName] = $val;
                }
            }
            
            // Complete aliases from factories
            foreach ($config['factories'] as $key => $val) {
                if (class_exists($key, true)) {
                    $refClass = new \ReflectionClass($key);
                    $tableName = $refClass->getConstant('TABLENAME');
                    $config['aliases'][$tableName] = $key;
                    $shortClassName = $refClass->getShortName();
                    $config['aliases'][$shortClassName] = $key;
                }
            }
        }
        
        $cache->setItem($cacheKey, $config);
        
//        file_put_contents('tmp/TableManager_config.json', json_encode($config, JSON_PRETTY_PRINT));
        return new TableManager($container, $config);
    }
}
