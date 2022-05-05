<?php

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
class AdapterFactory
{
    public function __invoke(ContainerInterface $container, string $resolvedName, $options): AdapterInterface
    {
        if (!$config = ($container->get('config')['db'] ?? null)) {
            throw new ConfigKeyMissingException("Key 'db' is missing in configuration");
        }
        
        $dbVersionRequested = $config[\Ruga\Db\Schema\Updater::class][\Ruga\Db\Schema\Updater::CONF_REQUESTED_VERSION] ?? 0;
        $adapter = new Adapter($config);
        
        $metadataCache = $container->get(MetadataCacheInterface::class);
        \Ruga\Db\Table\Feature\MetadataFeature::setGlobalMetadataCache($metadataCache);
        
        \Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);
        
        if ($container->has(TableManagerInterface::class)) {
            $adapter->setTableManager($container->get(TableManagerInterface::class));
        }
        
        try {
            $dbVersion = \Ruga\Db\Schema\Updater::getDbVersion($adapter) ?? 0;
        } catch (\Exception $e) {
            $dbVersion = 0;
        }
        
        
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
        
        
        return $adapter;
    }
}
