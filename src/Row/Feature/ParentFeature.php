<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\ResultSet\ResultSet;
use Ruga\Db\Row\Exception\FeatureMissingException;
use Ruga\Db\Row\Exception\NoConstraintsException;
use Ruga\Db\Row\Exception\TooManyConstraintsException;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Table\Feature\MetadataFeature;
use Ruga\Db\Table\TableInterface;

/**
 * The parent feature adds the ability to find, add and remove children
 */
class ParentFeature extends AbstractFeature implements ParentFeatureAttributesInterface
{
    private ?MetadataFeature $metadataFeature = null;
    
    private $dependentRows = [];
    
    
    
    private function getMetadataFeature(): MetadataFeature
    {
        if ($this->metadataFeature === null) {
            $this->metadataFeature = $this->rowGateway->getTableGateway()->getFeatureSet()->getFeatureByClassName(
                MetadataFeature::class
            );
            if (!$this->metadataFeature || !($this->metadataFeature instanceof MetadataFeature)) {
                throw new \Exception(
                    get_class($this) . " requires " . MetadataFeature::class . " in " . get_class(
                        $this->getTableGateway()
                    )
                );
            }
        }
        return $this->metadataFeature;
    }
    
    
    
    public function postInitialize()
    {
        if (!$this->rowGateway instanceof ParentFeatureAttributesInterface) {
            throw new \RuntimeException(
                get_class($this->rowGateway) . " must implement " . ParentFeatureAttributesInterface::class
            );
        }
    }
    
    
    private function saveDependentRows()
    {
        foreach ($this->dependentRows as $constraintName => $dependentRows) {
            foreach ($dependentRows as $uniqueid => $dependentRowInfo) {
                /** @var RowInterface $dependentRow */
                $dependentRow=$dependentRowInfo['dependentRow'];
                
                if($dependentRowInfo['action'] == 'save') {
                    if(!$this->rowGateway->isNew()) {
                        // If parent row is already saved, set foreign key values in dependent row
                        $dependentTableConstraint = $this->getDependentTableConstraint($this->resolveDependentTable($dependentRow), $constraintName);
                        foreach ($dependentTableConstraint['COLUMNS'] as $colPos => $column) {
                            $dependentRow->offsetSet($column, $this->rowGateway->offsetGet($dependentTableConstraint['REF_COLUMNS'][$colPos]));
                        }
                    } else {
                        throw new \RuntimeException('Parent row must be saved first');
                    }
                    $dependentRow->save();
                }
                if($dependentRowInfo['action'] == 'delete') {
                    $dependentRow->delete();
                }
            }
        }
    }
    
    
    /**
     * Before this (parent) row is updated, save all dependent (child) rows.
     *
     * @return void
     * @throws \Exception
     */
    public function preUpdate()
    {
        \Ruga\Log::functionHead($this);
        $this->saveDependentRows();
    }
    
    
    
    /**
     * After this (parent) row is inserted, save all dependent (child) rows.
     *
     * @return void
     * @throws \Exception
     */
    public function postInsert()
    {
        \Ruga\Log::functionHead($this);
        $this->saveDependentRows();
    }
    
    
    public function postSave()
    {
        // Successfully saved => delete dependent row list
        $this->dependentRows=[];
    }
    
    
    
    private function rowListAdd(RowInterface $dependentRow, string $constraintName, string $action='save')
    {
        $uniqueid=implode('-', $dependentRow->primaryKeyData ?? []);
        if(empty($uniqueid)) {
            $uniqueid='?' . date('U') . '?' . sprintf('%05u', count($this->dependentRows));
        }
        
        $uniqueid.='@' . get_class($dependentRow);
        
        $this->dependentRows[$constraintName][$uniqueid]['uniqueid']=$uniqueid;
        $this->dependentRows[$constraintName][$uniqueid]['action']=$action;
        $this->dependentRows[$constraintName][$uniqueid]['dependentRow']=$dependentRow;
    }
    
    
    /**
     * Resolves the given $dependentTable to a TableInterface object.
     *
     * @param mixed $dependentTable Table name, Table class name, Table object or Row object.
     *
     * @return TableInterface
     * @throws \Exception
     */
    private function resolveDependentTable($dependentTable): TableInterface
    {
        /** @var Adapter $adapter */
        $adapter = $this->rowGateway->getTableGateway()->getAdapter();
        
        if (is_string($dependentTable)) {
            $dependentTable = $adapter->tableFactory($dependentTable);
        } elseif ($dependentTable instanceof RowInterface) {
            $dependentTable = $dependentTable->getTableGateway();
        }
        
        if (!$dependentTable instanceof TableInterface) {
            throw new \InvalidArgumentException(
                "\$dependentTable must be (string) table name, RowInterface or TableInterface"
            );
        }
        
        return $dependentTable;
    }
    
    
    
    /**
     * Find all matching constraints for the parent-child-relation.
     *
     * @param TableInterface $dependentTable
     * @param string|null    $ruleKey
     *
     * @return array
     */
    private function resolveDependentTableConstraints(TableInterface $dependentTable, ?string $ruleKey = null): array
    {
        // Check dependent table for Metadata feature
        if (!$dependentTable->getFeatureSet()->getFeatureByClassName(MetadataFeature::class)) {
            throw new FeatureMissingException(MetadataFeature::class);
        }
        
        $dependentTableConstraints = [];
        // Find matching constraints in metadata
        foreach (($dependentTable->getMetadata()['constraints'] ?? []) as $constraint) {
            if (($constraint['TYPE'] === 'FOREIGN KEY') && ($constraint['REF_TABLE'] == $this->rowGateway->getTableGateway(
                    )->getTable())) {
                if (($ruleKey === null) || ($ruleKey === $constraint['NAME']) || in_array(
                        $ruleKey,
                        $constraint['COLUMNS']
                    )) {
                    $dependentTableConstraints[$constraint['NAME']] = $constraint;
                }
            }
        }
        
        // Find matching constraints in REFERENCEMAP
        foreach ($dependentTable::REFERENCEMAP ?? [] as $name => $constraint) {
            $constraint['NAME'] = $name;
            if ($constraint['REF_TABLE_CLASS']) {
                $constraint['REF_TABLE'] = $constraint['REF_TABLE_CLASS']::TABLENAME;
            }
            if ($constraint['REF_TABLE'] == $this->rowGateway->getTableGateway()->getTable()) {
                if (($ruleKey === null) || ($ruleKey === $name) || in_array($ruleKey, $constraint['COLUMNS'])) {
                    $dependentTableConstraints[$name] = $constraint;
                }
            }
        }
        
        return $dependentTableConstraints;
    }
    
    
    
    /**
     * Get exactly one matching parent-child-relation. Throws exceptions otherwise.
     *
     * @param TableInterface $dependentTable
     * @param string|null    $ruleKey
     *
     * @return array
     */
    public function getDependentTableConstraint(TableInterface $dependentTable, ?string $ruleKey = null): array
    {
        $dependentTableConstraints = $this->resolveDependentTableConstraints($dependentTable, $ruleKey);
        if (count($dependentTableConstraints) > 1) {
            throw new TooManyConstraintsException(
                "More than 1 constraints found for relation {$dependentTable->getTable()} }o--|| {$this->rowGateway->getTableGateway()->getTable()}: "
                . implode(', ', array_map(static fn($item) => $item['NAME'], $dependentTableConstraints))
            );
        }
        if (count($dependentTableConstraints) < 1) {
            throw new NoConstraintsException(
                "No constraints found for relation {$dependentTable->getTable()} }o--|| {$this->rowGateway->getTableGateway()->getTable()}"
            );
        }
        
        return array_shift($dependentTableConstraints);
    }
    
    
    
    /**
     * Find dependent rows (children) in table $dependentTable.
     *
     * @param mixed       $dependentTable Table name, Table class name, Table object or Row object.
     * @param string|null $ruleKey        Name of constraint or reference map entry to use.
     * @param Select|null $select         Additional select statements.
     *
     * @return ResultSetInterface
     * @throws \Exception
     */
    public function findDependentRowset(
        $dependentTable,
        ?string $ruleKey = null,
        ?Select $select = null
    ): ResultSetInterface {
        $dependentTable = $this->resolveDependentTable($dependentTable);
        
        /** @var TableInterface $dependentTable */
        if ($select === null) {
            $select = $dependentTable->getSql()->select();
        } else {
            // Set table
            $select->from($dependentTable->getTable());
        }
        
        // save existing where
        $existingWhere = $select->where;
        $select->reset(Select::WHERE);
        
        // add the dependent where
        $row = $this->rowGateway;
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $ruleKey);
        $select->where(
            function (Where $where) use ($dependentTableConstraint, $row) {
                $n = $where->NEST;
                foreach ($dependentTableConstraint['COLUMNS'] as $colPos => $column) {
                    $n->and->equalTo($column, $row->offsetGet($dependentTableConstraint['REF_COLUMNS'][$colPos]));
                }
            }
        );
        
        // add existing where at the end in parentheses
        if ($existingWhere->count() > 0) {
            $select->where->addPredicate($existingWhere);
        }
        
        \Ruga\Log::addLog("SQL={$select->getSqlString($dependentTable->getAdapter()->getPlatform())}");
        /** @var ResultSetInterface $rowset */
        $rowset=$dependentTable->selectWith($select);
        
        $a=[];
        foreach($rowset as $row) {
            $a[]=$row;
            $this->rowListAdd($row, $dependentTableConstraint['NAME']);
        }

        // Must re-initialize ResultSet to keep reference to the rows
        $rowset->initialize($a);
        return $rowset;
    }
    
    
    
    /**
     * Create a new row of a dependent table.
     *
     * @param mixed       $dependentTable Table name, Table class name, Table object or Row object.
     * @param array       $rowData
     * @param string|null $ruleKey        Name of constraint or reference map entry to use.
     *
     * @return RowInterface
     * @throws \ReflectionException
     */
    
    public function createDependentRow($dependentTable, array $rowData = [], ?string $ruleKey = null): RowInterface
    {
        $dependentTable = $this->resolveDependentTable($dependentTable);
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $ruleKey);
        
        if(!$this->rowGateway->isNew()) {
            // If parent row is already saved, set foreign key values in dependent row
            foreach ($dependentTableConstraint['COLUMNS'] as $colPos => $column) {
                $rowData[$column] = $this->rowGateway->offsetGet($dependentTableConstraint['REF_COLUMNS'][$colPos]);
            }
        }
        
        $dependentRow = $dependentTable->createRow($rowData);
        
        // Add dependent row to list for later saving
        $this->rowListAdd($dependentRow, $dependentTableConstraint['NAME']);
        
        return $dependentRow;
    }
    
    
    
    /**
     * Delete a dependent row. The delete is done, when the parent row is saved.
     *
     * @param RowInterface $dependentRow
     * @param string|null  $ruleKey
     *
     * @return void
     * @throws \Exception
     */
    public function deleteDependentRow(RowInterface $dependentRow, ?string $ruleKey = null)
    {
        $dependentTable = $this->resolveDependentTable($dependentRow);
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $ruleKey);

        $this->unlinkDependentRow($dependentRow, $ruleKey);
        
        // Add dependent row to list for later saving
        $this->rowListAdd($dependentRow, $dependentTableConstraint['NAME'], 'delete');
    }
    
    
    /**
     * Link a dependent row to this parent.
     *
     * @param RowInterface $dependentRow
     * @param string|null  $ruleKey
     *
     * @return void
     * @throws \Exception
     */
    public function linkDependentRow(RowInterface $dependentRow, ?string $ruleKey = null): RowInterface
    {
        $dependentTable = $this->resolveDependentTable($dependentRow);
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $ruleKey);
        
        if(!$this->rowGateway->isNew()) {
            // If parent row is already saved, set foreign key values in dependent row
            foreach ($dependentTableConstraint['COLUMNS'] as $colPos => $column) {
                $dependentRow->offsetSet(
                    $column,
                    $this->rowGateway->offsetGet($dependentTableConstraint['REF_COLUMNS'][$colPos])
                );
            }
        }
        
        // Add dependent row to list for later saving
        $this->rowListAdd($dependentRow, $dependentTableConstraint['NAME']);
        
        return $dependentRow;
    }
    
    
    
    /**
     * Unlink a dependent row from this parent.
     *
     * @param RowInterface $dependentRow
     * @param string|null  $ruleKey
     *
     * @return RowInterface
     * @throws \Exception
     */
    public function unlinkDependentRow(RowInterface $dependentRow, ?string $ruleKey = null): RowInterface
    {
        $dependentTable = $this->resolveDependentTable($dependentRow);
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $ruleKey);
        
        foreach ($dependentTableConstraint['COLUMNS'] as $colPos => $column) {
            $dependentRow->offsetSet($column, null);
        }
        
        // Add dependent row to list for later saving
        $this->rowListAdd($dependentRow, $dependentTableConstraint['NAME']);
        
        return $dependentRow;
    }
    
    
}