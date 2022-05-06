<?php

declare(strict_types=1);

namespace Ruga\Db\Table;

use Laminas\ServiceManager\AbstractPluginManager;

class TableManager extends AbstractPluginManager implements TableManagerInterface
{
    /**
     * An object type that the created instance must be instanced of
     *
     * @var null|string
     */
    protected $instanceOf = TableInterface::class;
}