<?php

declare(strict_types=1);

namespace Ruga\Db\Row;

use Laminas\Db\RowGateway\RowGatewayInterface;
use Ruga\Db\Table\AbstractTable;

/**
 * @see AbstractRow
 */
interface RowInterface extends RowGatewayInterface
{
    /**
     * Returns true, if the row is not yet saved to the data base.
     *
     * @return bool
     */
    public function isNew(): bool;
    
    
    
    /**
     * Create an array representation of the data in the row.
     *
     * @return array
     * @throws \Exception
     */
    public function toArray(): array;
    
    
    
    /**
     * Returns the data from self::toArray() as JSON.
     *
     * @param int $options The json_encode options @see https://www.php.net/manual/en/function.json-encode.php
     *
     * @return string
     */
    public function toJson(int $options = 0): string;
    
    
    
    /**
     * Populate Data and initialize row.
     *
     * @param array $rowData
     * @param bool  $rowExistsInDatabase
     *
     * @return self Provides a fluent interface
     */
    public function populate(array $rowData, $rowExistsInDatabase = false);
    
    
    
    /**
     * Returns the associated table gateway object.
     *
     * @return AbstractTable
     */
    public function getTableGateway(): AbstractTable;
    
    
}