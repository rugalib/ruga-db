<?php

return [
    'db' => [
        'driver' => 'Pdo_Mysql',
        'host' => '127.0.0.1',
        'database' => '',
        'username' => '',
        'password' => '',
        'driver_options' => [
//            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
//            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
        ],
        \Ruga\Db\Schema\Updater::class => [
            \Ruga\Db\Schema\Updater::CONF_DBTAG => 'no-dbtag-set',
            \Ruga\Db\Schema\Updater::CONF_REQUESTED_VERSION => 18,
            \Ruga\Db\Schema\Updater::CONF_SCHEMA_DIRECTORY => __DIR__ . '/ruga-dbschema',
            \Ruga\Db\Schema\Updater::CONF_TABLES => [
                'MemberTable' => \Ruga\Db\Test\Model\MemberTable::class,
                \Ruga\Db\Test\Model\MetaTable::class,
                'CartTable' => \Ruga\Db\Test\Model\CartTable::class,
                'CartItemTable' => \Ruga\Db\Test\Model\CartItemTable::class,
                'MusterTable' => \Ruga\Db\Test\Model\MusterTable::class,
                'PartyHasOrganizationTable' => \Ruga\Db\Test\Model\PartyHasOrganizationTable::class,
                'OrganizationTable' => \Ruga\Db\Test\Model\OrganizationTable::class,
                'PartyTable' => \Ruga\Db\Test\Model\PartyTable::class,
            ]
        ],
    ],
    'cache' => [
        'adapter' => [
            'name' => \Laminas\Cache\Storage\Adapter\Memory::class,
            'options' => [
                'ttl' => PHP_INT_MAX,
            ],
        ],
        'plugins' => [
            'exception_handler' => [
                'throw_exceptions' => false,
            ],
            'serializer' => [],
        ],
    ],
    
    'dependencies' => [
        'factories' => [
        ],
        'aliases' => [
            // Alias used by Resolver
            'Mem' => \Ruga\Db\Test\Model\MemberTable::class,
        ],
    ],
];
