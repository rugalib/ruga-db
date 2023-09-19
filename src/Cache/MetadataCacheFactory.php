<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Cache;

use Psr\Container\ContainerInterface;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class MetadataCacheFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['db'] ?? [];
        
        if ($config[MetadataCache::class] ?? null) {
            return \Laminas\Cache\StorageFactory::factory($config[MetadataCache::class]);
        }

//        throw new \Exception('No db cache defined (key [db][MetadataCache::class])');
        
        \Ruga\Log::addLog(
            "No metadata cache defined. Using memory (aka NO!) cache. Please set key [db][MetadataCache::class] in configuration.",
            \Ruga\Log\Severity::WARNING
        );
        return \Laminas\Cache\StorageFactory::factory(
            [
                'adapter' => [
                    'name' => \Laminas\Cache\Storage\Adapter\Memory::class,
                    'options' => ['ttl' => PHP_INT_MAX,],
                ],
                'plugins' => [
                    'exception_handler' => ['throw_exceptions' => false,],
                    'serializer' => [],
                ],
            ]
        );
    }
    
    
}