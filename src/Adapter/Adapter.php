<?php

declare(strict_types=1);

namespace Ruga\Db\Adapter;

use Ruga\Db\Adapter\Exception\TableManagerMissingException;
use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Schema\Updater;
use Ruga\Db\Table\AbstractTable;
use Ruga\Db\Table\TableInterface;
use Ruga\Db\Table\TableManager;

/**
 * Class Adapter
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * @see     AdapterFactory
 */
class Adapter extends \Laminas\Db\Adapter\Adapter implements AdapterInterface
{
    /** @var string */
    private $dbhash;
    
    /** @var TableManager */
    private $tableManager;
    
    
    
    /**
     * Set the table manager and enable the functions tableFactory and rowFactory.
     *
     * @param TableManager $tableManager
     */
    public function setTableManager(TableManager $tableManager)
    {
        $this->tableManager = $tableManager;
    }
    
    
    
    /**
     * Return an instance of the desired table.
     * This function uses TableManager to find and instantiate the table.
     *
     * @param $table
     *
     * @return AbstractTable|TableInterface|null
     * @throws \Exception
     * @see TableManager
     */
    public function tableFactory($table): TableInterface
    {
        \Ruga\Log::functionHead($this);
        if (!$this->tableManager) {
            throw new TableManagerMissingException("No table manager provided to the adapter");
        }
        return $this->tableManager->get($table);
    }
    
    
    
    /**
     * Find the row and return an instance.
     * Returns null if $id is not found or not unique. Returns the row instance if exactly one row was found.
     *
     * @param int|array|string $id
     * @param null|string      $ref_table
     *
     * @return AbstractRow|RowInterface|array|\ArrayObject|null
     * @throws \Exception
     * @see TableInterface::findById()
     * @see Adapter::tableFactory()
     */
    public function rowFactory($id, $ref_table = null)/*: ?RowInterface*/
    {
        \Ruga\Log::functionHead($this);
        
        if (empty($id)) {
            // no id given => return null
            return null;
        }
        
        /** @var TableInterface $ref_table */
        if (is_numeric($id) || is_array($id)) {
            if (empty($ref_table)) {
                throw new \Exception("If id is numeric or an array, \$ref_table must be a valid table class name.");
            }
            $ref_id = $id;
        } else {
            if (strpos($id, '@') !== false) {
                // uniqueid given
                @list($ref_id, $ref_table) = explode('@', $id);
            } else {
                return null;
            }
        }
        
        $result = $this->tableFactory($ref_table)->findById($ref_id);
        return $result->count() == 1 ? $result->current() : null;
    }
    
    
    
    /**
     * Return the dbhash stored in the database.
     *
     * @return string|null
     * @throws \Exception
     */
    public function getDbHash()
    {
        if (!$this->dbhash) {
            $this->dbhash = Updater::getDbHash($this);
        }
        return $this->dbhash;
    }
}