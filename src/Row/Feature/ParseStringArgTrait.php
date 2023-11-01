<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace Ruga\Db\Row\Feature;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Ruga\Db\Table\AbstractRugaTable;

trait ParseStringArgTrait
{
    /**
     * Parse the given argument and return table, column and rows.
     *
     * @param string            $arg
     * @param null|string|array $value
     *
     * @return array
     */
    private function parseArg(string $arg, $value = null)
    {
        /** @var AbstractRugaTable $table */
        $table = null;
        /** @var string $column */
        $column = null;
        /** @var ResultSetInterface $rows */
        $rows = null;
        
        $tableName = $arg;
        
        // Check, if value (after '=') is given in argument
        // in this case, the actual $value is overwritten
        if (strpos($tableName, '=') !== false) {
            [$tableName, $value] = preg_split('/\s*=\s*/', $tableName, 2);
        }
        
        // Check, if column name (after ':') is given in argument
        if (strpos($tableName, ':') !== false) {
            [$tableName, $column] = preg_split('/\s*:\s*/', $tableName, 2);
        }
        
        // Try to resolve the remaining string in $tableName to a table
        try {
            $table = $this->rowGateway->getTableGateway()->getAdapter()->tableFactory($tableName);
        } catch (ServiceNotFoundException $e) {
            // Table not found, try to resolve to a row by assuming it's a uniqueid
            if (!$row = $this->rowGateway->getTableGateway()->getAdapter()->rowFactory($tableName)) {
                // Row not found, try to resolve to a row by assuming there is a uniqueid in $value
                if (!$row = $this->rowGateway->getTableGateway()->getAdapter()->rowFactory($value)) {
                    throw $e;
                }
            }
            $table = $row->getTableGateway();
            $value = $row->uniqueid;
        }
        
        // populate the rows return value
        if (!$rows) {
            if (empty($column)) {
                $rows = $table->findById($value);
            } else {
                $rows = $table->select([$column => $value]);
            }
        }
        
        return [$table, $column, $rows];
    }
    
}