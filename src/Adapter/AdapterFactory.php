<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Adapter;

use Psr\Container\ContainerInterface;
use Ruga\Db\Adapter\Exception\ConfigKeyMissingException;
use Ruga\Db\Adapter\Exception\WrongDbVersionException;
use Ruga\Db\Cache\MetadataCacheInterface;
use Ruga\Db\Table\TableManagerInterface;

/**
 * @see     Adapter
 * @author  Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class AdapterFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
    const DISABLE_VERSION_CHECK = 'DISABLE_VERSION_CHECK';
    const DISABLE_TABLE_MANAGER = 'DISABLE_TABLE_MANAGER';
    const DISABLE_STATIC_ADAPTER = 'DISABLE_STATIC_ADAPTER';
    const DISABLE_METADATA_CACHE = 'DISABLE_METADATA_CACHE';
    
    /**
     * Create an adapter.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array<mixed>  $options
     *
     * @return AdapterInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AdapterInterface
    {
        $options = $options ?? [];
        
        // Check, if configuration is there
        if (!$config = ($container->get('config')['db'] ?? null)) {
            throw new ConfigKeyMissingException("Key 'db' is missing in configuration");
        }
        
        // Create adapter instance
        $adapter = new Adapter($config);
    
        if(!in_array(self::DISABLE_METADATA_CACHE, $options))
        {
            // Retreive metadata cache and enable feature
            $metadataCache = $container->get(MetadataCacheInterface::class);
            \Ruga\Db\Table\Feature\MetadataFeature::setGlobalMetadataCache($metadataCache);
        }
    
        if(!in_array(self::DISABLE_STATIC_ADAPTER, $options))
        {
            // Set adapter as global static adapter
            \Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);
        }
    
        if(!in_array(self::DISABLE_TABLE_MANAGER, $options))
        {
            // Add table manager to the adapter, if service manager knows TableManagerInterface
            if ($container->has(TableManagerInterface::class)) {
                $adapter->setTableManager($container->get(TableManagerInterface::class));
            }
        }
        
        if(!in_array(self::DISABLE_VERSION_CHECK, $options))
        {
            // Read current database version
            try {
                $dbVersion = \Ruga\Db\Schema\Updater::getDbVersion($adapter) ?? 0;
            } catch (\Exception $e) {
                $dbVersion = 0;
            }
    
            // Get requested database version from configuration
            $dbVersionRequested = $config[\Ruga\Db\Schema\Updater::class][\Ruga\Db\Schema\Updater::CONF_REQUESTED_VERSION] ?? 0;
    
            // Check, if versions match
            if ($dbVersion != $dbVersionRequested) {
                \Ruga\Log::addLog(
                    "Db version is wrong! found={$dbVersion} | expected={$dbVersionRequested}",
                    \Ruga\Log\Severity::ERROR,
                    \Ruga\Log\Type::RESULT
                );
                throw new WrongDbVersionException(
                    "Db version is wrong! found={$dbVersion} | expected={$dbVersionRequested}"
                );
            } else {
                \Ruga\Log::addLog("dbVersion={$dbVersion}");
            }
        }
        return $adapter;
    }
}
