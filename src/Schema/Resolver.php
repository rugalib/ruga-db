<?php

declare(strict_types=1);

namespace Ruga\Db\Schema;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Ruga\Db\Table\TableInterface;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class Resolver
{
    private $config;
    private $adapter;
    private $tables;
    
    
    
    /**
     * Resolver constructor.
     *
     * @param $adapter
     * @param $config
     */
    public function __construct($adapter, $config)
    {
        foreach ($config['dependencies']['aliases'] ?? [] as $name => $class) {
            if (is_a($class, TableGatewayInterface::class, true)) {
                if (!isset($this->tables[$class])) {
                    $this->tables[$class] = $class;
                }
                if (!isset($this->tables[$name])) {
                    $this->tables[$name] = $class;
                }
            }
        }
        
        $this->config = $config['db'] ?? $config ?? [];
        $this->adapter = $adapter;
        
        foreach ($this->config[Updater::class][Updater::CONF_TABLES] ?? [] as $name => $class) {
            if (!isset($this->tables[$class])) {
                $this->tables[$class] = $class;
            }
            if (!isset($this->tables[$name])) {
                $this->tables[$name] = $class;
            }
        }
        
        foreach ($this->config[Updater::class][Updater::CONF_TABLES] ?? [] as $name => $class) {
            if (!isset($this->tables[$class])) {
                $this->tables[$class] = $class;
            }
            if (!isset($this->tables[$name])) {
                $this->tables[$name] = $class;
            }
        }
        
        foreach (($this->config[Updater::class]['components'] ?? []) as $component => $component_config) {
            foreach ($component_config[Updater::CONF_TABLES] ?? [] as $name => $class) {
                if (!isset($this->tables[$class]) || ($component == $class)) {
                    $this->tables[$class] = $class;
                }
                if (!isset($this->tables[$name]) || ($component == $class)) {
                    $this->tables[$name] = $class;
                }
            }
        }
    }
    
    
    
    /**
     * Return the table name for the query.
     *
     * @param string $query Search string (Table name, short class name, FQCN, alias)
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getTableName($query): string
    {
        $str = null;
        $className = null;
        if ($query instanceof TableInterface) {
            $str = get_class($query);
        } elseif (is_a($query, TableInterface::class, true)) {
            $className = $query;
        } elseif (is_string($query)) {
            $str = $query;
        }
        
        $className = $this->tables[$str] ?? $className;
        if ($className) {
            return (new \ReflectionClass($className))->getConstant('TABLENAME');
        } else {
            return $str;
        }
    }
}