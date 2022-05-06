<?php

declare(strict_types=1);

namespace Ruga\Db\Table;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;
use Ruga\Db\Adapter\AdapterInterface;

class AbstractTableFactory implements AbstractFactoryInterface
{
    
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TableInterface
    {
        return new $requestedName($container->get(AdapterInterface::class));
    }
    
    
    
    /**
     * Can the factory create an instance for the service?
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     *
     * @return bool
     * @throws \Exception
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
//        \Ruga\Log::functionHead();
        if (!class_exists($requestedName, true)) {
            return false;
        }
        return in_array(TableInterface::class, class_implements($requestedName, true), true);
    }
}
