<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\ResultSet\ResultSet;
use Ruga\Db\Row\Exception\FeatureMissingException;
use Ruga\Db\Row\Exception\InvalidForeignKeyException;
use Ruga\Db\Row\Exception\NoConstraintsException;
use Ruga\Db\Row\Exception\TooManyConstraintsException;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Table\Feature\MetadataFeature;
use Ruga\Db\Table\TableInterface;

/**
 * The parent feature adds the ability to find, add and remove children
 */
class ChildFeature extends AbstractFeature implements ChildFeatureAttributesInterface
{
    private ?MetadataFeature $metadataFeature = null;
    
    private $parentRows = [];
    
    
    
    public function postInitialize()
    {
        if (!$this->rowGateway instanceof ChildFeatureAttributesInterface) {
            throw new \RuntimeException(
                get_class($this->rowGateway) . " must implement " . ChildFeatureAttributesInterface::class
            );
        }
    }
    
    
    
    public function preSave()
    {
        // Check if all parents are non-new
        $aNewParentRows = [];
        foreach ($this->parentRows as $constraintName => $parentRows) {
            foreach ($parentRows as $uniqueid => $parentRowInfo) {
                /** @var RowInterface $parentRow */
                $parentRow = $parentRowInfo['parentRow'];
                
                if ($parentRowInfo['action'] == 'save') {
                    if ($parentRow->isNew()) {
                        $aNewParentRows[] = $parentRow;
                        
                        throw new InvalidForeignKeyException(
                            'Parent row must be saved to database first (' . get_class($parentRow) . ').'
                        );
                    } else {
                        // Update foreign key
                        $parentTable = $this->resolveTableArgument($parentRow);
                        $parentTableConstraint = $this->getParentTableConstraint($parentTable, $constraintName);
                        foreach ($parentTableConstraint['COLUMNS'] as $colPos => $column) {
                            $this->rowGateway->offsetSet(
                                $column,
                                $parentRow->offsetGet($parentTableConstraint['REF_COLUMNS'][$colPos])
                            );
                        }
                    }
                }
                if ($parentRowInfo['action'] == 'unlink') {
                    // Clear foreign key
                    $parentTable = $this->resolveTableArgument($parentRow);
                    $parentTableConstraint = $this->getParentTableConstraint($parentTable, $constraintName);
                    foreach ($parentTableConstraint['COLUMNS'] as $colPos => $column) {
                        $this->rowGateway->offsetSet(
                            $column,
                            null
                        );
                    }
                }
                if ($parentRowInfo['action'] == 'delete') {
                    $parentRow->delete();
                }
            }
        }
        if (count($aNewParentRows) > 0) {
            throw new InvalidForeignKeyException(
                'Parent row must be saved to database first (' . implode(
                    ', ',
                    array_map(static fn($item) => get_class($item), $aNewParentRows)
                ) . ').'
            );
        }
    }
    
    
    
    public function postSave()
    {
        // Clear parent's cache list after saving
        // this is necessary, because parent is not saved by child save operation
        foreach ($this->parentRows as $parentConstraintName => $parentConstraintRows) {
            foreach ($parentConstraintRows as $uniqueid => $parentRowInfo) {
                $parentRowInfo['parentRow']->dependentRowListClear();
            }
        }
        // Successfully saved => delete dependent row list
        $this->parentRows = [];
    }
    
    
    
    public function parentRowListClear()
    {
        $this->parentRows = [];
    }
    
    
    
    /**
     * Add $parentRow to the internal list of parents. Also called by ParentFeature to add the parent to the child's
     * list.
     *
     * @param RowInterface|null $parentRow
     * @param string            $constraintName
     * @param string|null       $action
     *
     * @return void
     */
    public function parentRowListAdd(?RowInterface $parentRow, string $constraintName, ?string $action = null)
    {
        if ($parentRow === null) {
            return;
        }
        
        $uniqueid = implode('-', $parentRow->primaryKeyData ?? []);
        $uniqueid = empty($uniqueid) ? '?'.spl_object_hash($parentRow) : $uniqueid;
        $uniqueid .= '@' . get_class($parentRow);
        
        // Only add row if it does not already exist in cache
        if (!array_key_exists($uniqueid, $this->parentRows[$constraintName] ?? [])) {
            $this->parentRows[$constraintName][$uniqueid]['parentRow'] = $parentRow;
            $this->parentRows[$constraintName][$uniqueid]['uniqueid'] = $uniqueid;
            $this->parentRows[$constraintName][$uniqueid]['action'] = $action ?? 'save';
        }
        
        // Update action
        if ($action) {
            $this->parentRows[$constraintName][$uniqueid]['action'] = $action;
        }
    }
    
    
    
    /**
     * Get the cached parent row.
     *
     * @param string $constraintName
     *
     * @return RowInterface|null
     */
    private function parentRowListGet(string $constraintName): ?RowInterface
    {
        foreach (($this->parentRows[$constraintName] ?? []) as $uniqueid => $parentInfo) {
            if (($parentInfo['action'] ?? '') == 'save') {
                return $parentInfo['parentRow'] ?? null;
            }
        }
        return null;
    }
    
    
    
    /**
     * Resolves the given $table to a TableInterface object.
     *
     * @param mixed $table Table name, Table class name, Table object or Row object.
     *
     * @return TableInterface
     * @throws \Exception
     */
    private function resolveTableArgument($table): TableInterface
    {
        /** @var Adapter $adapter */
        $adapter = $this->rowGateway->getTableGateway()->getAdapter();
        
        if (is_string($table)) {
            $table = $adapter->tableFactory($table);
        } elseif ($table instanceof RowInterface) {
            $table = $table->getTableGateway();
        }
        
        if (!$table instanceof TableInterface) {
            throw new \InvalidArgumentException(
                "\$table must be (string) table name, RowInterface or TableInterface"
            );
        }
        
        return $table;
    }
    
    
    
    /**
     * Find all matching constraints for the parent-child-relation.
     *
     * @param TableInterface $parentTable
     * @param string|null    $ruleKey
     *
     * @return array
     */
    private function resolveParentTableConstraints(TableInterface $parentTable, ?string $ruleKey = null): array
    {
        $dependentTable = $this->rowGateway->getTableGateway();
        
        // Check table for Metadata feature
        if (!$dependentTable->getFeatureSet()->getFeatureByClassName(MetadataFeature::class)) {
            throw new FeatureMissingException(MetadataFeature::class);
        }
        
        $parentTableConstraints = [];
        // Find matching constraints in metadata
        foreach (($dependentTable->getMetadata()['constraints'] ?? []) as $constraint) {
            if (($constraint['TYPE'] === 'FOREIGN KEY') && ($constraint['REF_TABLE'] == $parentTable->getTable())) {
                if (($ruleKey === null) || ($ruleKey === $constraint['NAME']) || in_array(
                        $ruleKey,
                        $constraint['COLUMNS']
                    )) {
                    $parentTableConstraints[$constraint['NAME']] = $constraint;
                }
            }
        }
        
        // Find matching constraints in REFERENCEMAP
        foreach ($dependentTable::REFERENCEMAP ?? [] as $name => $constraint) {
            $constraint['NAME'] = $name;
            if ($constraint['REF_TABLE_CLASS']) {
                $constraint['REF_TABLE'] = $constraint['REF_TABLE_CLASS']::TABLENAME;
            }
            if ($constraint['REF_TABLE'] == $parentTable->getTable()) {
                if (($ruleKey === null) || ($ruleKey === $name) || in_array($ruleKey, $constraint['COLUMNS'])) {
                    $parentTableConstraints[$name] = $constraint;
                }
            }
        }
        
        return $parentTableConstraints;
    }
    
    
    
    /**
     * Get exactly one matching parent-child-relation. Throws exceptions otherwise.
     *
     * @param TableInterface $parentTable
     * @param string|null    $ruleKey
     *
     * @return array
     */
    private function getParentTableConstraint(TableInterface $parentTable, ?string $ruleKey = null): array
    {
        $parentTableConstraints = $this->resolveParentTableConstraints($parentTable, $ruleKey);
        if (count($parentTableConstraints) > 1) {
            throw new TooManyConstraintsException(
                "More than 1 constraints found for relation {$parentTable->getTable()} ||--o{ {$this->rowGateway->getTableGateway()->getTable()}: "
                . implode(', ', array_map(static fn($item) => $item['NAME'], $parentTableConstraints))
            );
        }
        if (count($parentTableConstraints) < 1) {
            throw new NoConstraintsException(
                "No constraints found for relation {$parentTable->getTable()} ||--o{ {$this->rowGateway->getTableGateway()->getTable()}"
            );
        }
        
        return array_shift($parentTableConstraints);
    }
    
    
    
    /**
     * If parent row implements ParentFeature, store a reference to this dependent row in parent.
     *
     * @param RowInterface $parentRow
     * @param string       $constraintName
     *
     * @return void
     */
    private function addChildToParent(RowInterface $parentRow, string $constraintName, ?string $action = null)
    {
        if ($parentRow instanceof ParentFeatureAttributesInterface) {
            $parentRow->dependentRowListAdd($this->rowGateway, $constraintName, $action);
        }
    }
    
    
    
    /**
     * Find the parent row.
     *
     * @param             $parentTable
     * @param string|null $ruleKey
     * @param Select|null $select
     *
     * @return RowInterface|null
     * @throws \Exception
     */
    public function findParentRow($parentTable, ?string $ruleKey = null, ?Select $select = null): ?RowInterface
    {
        $parentTable = $this->resolveTableArgument($parentTable);
        $parentTableConstraint = $this->getParentTableConstraint($parentTable, $ruleKey);
        
        if ($select === null) {
            $select = $parentTable->getSql()->select();
        } else {
            // Set table
            $select->from($parentTable->getTable());
        }
        
        // save existing where
        $existingWhere = $select->where;
        $select->reset(Select::WHERE);
        
        // add the dependent where
        $row = $this->rowGateway;
        $select->where(
            function (Where $where) use ($parentTableConstraint, $row) {
                $n = $where->NEST;
                foreach ($parentTableConstraint['REF_COLUMNS'] as $colPos => $column) {
                    $n->and->equalTo(
                        "{$parentTableConstraint['REF_TABLE']}.{$column}",
                        $row->offsetGet($parentTableConstraint['COLUMNS'][$colPos])
                    );
                }
            }
        );
        
        // add existing where at the end in parentheses
        if ($existingWhere->count() > 0) {
            $select->where->addPredicate($existingWhere);
        }
        
        \Ruga\Log::addLog(
            "SQL={$select->getSqlString($parentTable->getAdapter()->getPlatform())}",
            \Ruga\Log\Severity::DEBUG
        );
        $parentRow = $parentTable->selectWith($select)->current();
        
        // Add parent row to list
        $this->parentRowListAdd($parentRow, $parentTableConstraint['NAME']);
        
        return $this->parentRowListGet($parentTableConstraint['NAME']);
    }
    
    
    
    /**
     * Create a new parent row.
     *
     * @param             $parentTable
     * @param array       $rowData
     * @param string|null $ruleKey
     *
     * @return RowInterface
     * @throws \ReflectionException
     */
    public function createParentRow($parentTable, array $rowData = [], ?string $ruleKey = null): RowInterface
    {
        $parentTable = $this->resolveTableArgument($parentTable);
        $parentTableConstraint = $this->getParentTableConstraint($parentTable, $ruleKey);
        
        $parentRow = $parentTable->createRow($rowData);
        // No foreign key yet!
        
        // Add parent row to list
        $this->parentRowListAdd($parentRow, $parentTableConstraint['NAME'], 'save');
        $this->addChildToParent($parentRow, $parentTableConstraint['NAME'], 'save');
        
        return $parentRow;
    }
    
    
    
    /**
     * Delete the parent row. The delete is done, when the dependent row is saved.
     *
     * @param             $parentTable
     * @param string|null $ruleKey
     *
     * @return void
     * @throws \Exception
     */
    public function deleteParentRow($parentTable, ?string $ruleKey = null)
    {
        $parentTable = $this->resolveTableArgument($parentTable);
        $parentTableConstraint = $this->getParentTableConstraint($parentTable, $ruleKey);
        $parentRow = $this->unlinkParentRow($parentTable, $ruleKey);
        
        // Add parent row to list
        $this->parentRowListAdd($parentRow, $parentTableConstraint['NAME'], 'delete');
        $this->addChildToParent($parentRow, $parentTableConstraint['NAME'], 'unlink');
    }
    
    
    
    /**
     * Link parent to this dependent row.
     *
     * @param RowInterface $parentRow
     * @param string|null  $ruleKey
     *
     * @return RowInterface
     * @throws \Exception
     */
    public function linkParentRow(RowInterface $parentRow, ?string $ruleKey = null): RowInterface
    {
        $parentTable = $this->resolveTableArgument($parentRow);
        $parentTableConstraint = $this->getParentTableConstraint($parentTable, $ruleKey);
        
        if (!$parentRow->isNew()) {
            // If parent row is already saved, set foreign key values in dependent row
            foreach ($parentTableConstraint['COLUMNS'] as $colPos => $column) {
                $this->rowGateway->offsetSet(
                    $column,
                    $parentRow->offsetGet($parentTableConstraint['REF_COLUMNS'][$colPos])
                );
            }
        }
        
        // Add parent row to list
        $this->parentRowListAdd($parentRow, $parentTableConstraint['NAME'], 'save');
        $this->addChildToParent($parentRow, $parentTableConstraint['NAME'], 'save');
        
        return $parentRow;
    }
    
    
    
    /**
     * Remove relation between this row and the given parent.
     *
     * @param mixed       $parentTable
     * @param string|null $ruleKey
     *
     * @return RowInterface The former parent row
     * @throws \Exception
     */
    public function unlinkParentRow($parentTable, ?string $ruleKey = null): RowInterface
    {
        $parentTable = $this->resolveTableArgument($parentTable);
        $parentTableConstraint = $this->getParentTableConstraint($parentTable, $ruleKey);
        
        $parentRow = $this->findParentRow($parentTable, $ruleKey);
        foreach ($parentTableConstraint['COLUMNS'] as $colPos => $column) {
            $this->rowGateway->offsetSet(
                $column,
                null
            );
        }
        
        // Add parent row to list
        $this->parentRowListAdd($parentRow, $parentTableConstraint['NAME'], 'unlink');
        $this->addChildToParent($parentRow, $parentTableConstraint['NAME'], 'unlink');
        
        return $parentRow;
    }
    
    
}