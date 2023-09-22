<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Exception;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\ExpressionInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use ReflectionException;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Row\Exception\FeatureMissingException;
use Ruga\Db\Row\Exception\NoConstraintsException;
use Ruga\Db\Row\Exception\NoDefaultValueException;
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
                throw new Exception(
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
                $dependentRow = $dependentRowInfo['dependentRow'];
                
                if ($dependentRowInfo['action'] == 'save') {
                    if (!$this->rowGateway->isNew()) {
                        // If parent row is already saved, set foreign key values in dependent row
                        $dependentTableConstraint = $this->getDependentTableConstraint(
                            $this->resolveDependentTable($dependentRow),
                            $constraintName
                        );
                        foreach ($dependentTableConstraint['COLUMNS'] as $colPos => $column) {
                            $dependentRow->offsetSet(
                                $column,
                                $this->rowGateway->offsetGet(
                                    $dependentTableConstraint['REF_COLUMNS'][$colPos]
                                )
                            );
                        }
                    } else {
                        throw new \RuntimeException('Parent row must be saved first');
                    }
                    $dependentRow->save();
                }
                
                if ($dependentRowInfo['action'] == 'unlink') {
                    $dependentTableConstraint = $this->getDependentTableConstraint(
                        $this->resolveDependentTable($dependentRow),
                        $constraintName
                    );
                    foreach ($dependentTableConstraint['COLUMNS'] as $colPos => $column) {
                        $dependentRow->offsetSet(
                            $column,
                            null
                        );
                    }
                    
                    $dependentRow->save();
                }
                
                if ($dependentRowInfo['action'] == 'delete') {
                    $dependentRow->delete();
                }
            }
        }
    }
    
    
    
    /**
     * Before this (parent) row is updated, save all dependent (child) rows.
     *
     * @return void
     * @throws Exception
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
     * @throws Exception
     */
    public function postInsert()
    {
        \Ruga\Log::functionHead($this);
        $this->saveDependentRows();
    }
    
    
    
    public function postSave()
    {
        // Successfully saved => delete dependent row list
        array_map(static fn($dependentRow) => $dependentRow->parentRowListClear(), $this->dependentRows);
        $this->dependentRows = [];
    }
    
    
    
    public function dependentRowListClear()
    {
        $this->dependentRows = [];
    }
    
    
    
    /**
     * Add $dependentRow to the internal list of children. Also called by ChildFeature to add the child to the parent's
     * list.
     *
     * @param RowInterface|null $dependentRow
     * @param string            $constraintName
     * @param string|null       $action
     *
     * @return void
     * @throws Exception
     */
    public function dependentRowListAdd(?RowInterface $dependentRow, string $constraintName, ?string $action = null)
    {
        if ($dependentRow === null) {
            return;
        }
        
        $dependentTable = $this->resolveDependentTable($dependentRow);
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $constraintName);
        $constraintName = $dependentTableConstraint['NAME'];
        
        $uniqueid = implode('-', $dependentRow->primaryKeyData ?? []);
        $uniqueid = empty($uniqueid) ? '?' . spl_object_hash($dependentRow) : $uniqueid;
        $uniqueid .= '@' . get_class($dependentRow);
        
        if (!array_key_exists($uniqueid, $this->dependentRows[$constraintName] ?? [])) {
            $this->dependentRows[$constraintName][$uniqueid]['dependentRow'] = $dependentRow;
            $this->dependentRows[$constraintName][$uniqueid]['uniqueid'] = $uniqueid;
            $this->dependentRows[$constraintName][$uniqueid]['action'] = $action ?? 'save';
        }
        
        // Update action
        if ($action) {
            $this->dependentRows[$constraintName][$uniqueid]['action'] = $action;
        }
    }
    
    
    
    /**
     * Get all cached dependent rows for a given constraint name.
     *
     * @param string $constraintName
     *
     * @return array
     */
    private function dependentRowListGetDependentRows(string $constraintName): array
    {
        $a = [];
        foreach (($this->dependentRows[$constraintName] ?? []) as $uniqueid => $depententRowInfo) {
            if (($depententRowInfo['action'] ?? '') == 'save') {
                $a[] = $depententRowInfo['dependentRow'];
            }
        }
        return $a;
    }
    
    
    
    /**
     * Resolves the given $dependentTable to a TableInterface object.
     *
     * @param string|RowInterface|TableInterface $dependentTable Table name, Table class name, Table object or Row
     *                                                           object.
     *
     * @return TableInterface
     * @throws Exception
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
            $constraint['TABLE'] = $dependentTable->getTable();
            $constraint['TABLE_CLASS'] = get_class($dependentTable);
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
    private function getDependentTableConstraint(TableInterface $dependentTable, ?string $ruleKey = null): array
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
     * If dependent row implements ChildFeature, store a reference to this parent row in child.
     *
     * @param RowInterface $dependentRow
     * @param string       $constraintName
     * @param string|null  $action
     *
     * @return void
     */
    private function addParentToChild(RowInterface $dependentRow, string $constraintName, ?string $action = null)
    {
        if ($dependentRow instanceof ChildFeatureAttributesInterface) {
            $dependentRow->parentRowListAdd($this->rowGateway, $constraintName, $action);
        }
    }
    
    
    
    /**
     * Find dependent rows (children) in table $dependentTable.
     *
     * @param string|RowInterface|TableInterface $dependentTable Table name, Table class name, Table object or Row
     *                                                           object.
     * @param string|null                        $ruleKey        Name of constraint or reference map entry to use.
     * @param Select|null                        $select         Additional select statements.
     *
     * @return ResultSetInterface
     * @throws Exception
     */
    public function findDependentRowset(
        $dependentTable,
        ?string $ruleKey = null,
        ?Select $select = null
    ): ResultSetInterface {
        $dependentTable = $this->resolveDependentTable($dependentTable);
        
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
                    try {
                        $rightVal = $row->offsetGet($dependentTableConstraint['REF_COLUMNS'][$colPos]);
                    } catch (NoDefaultValueException $e) {
                        $rightVal = 'parentRow_IS_NEW';
                        $n->and->equalTo(1, 0, ExpressionInterface::TYPE_VALUE);
                    }
                    $n->and->equalTo(
                        "{$dependentTableConstraint['TABLE']}.{$column}",
                        $rightVal
                    );
                }
            }
        );
        
        // add existing where at the end in parentheses
        if ($existingWhere->count() > 0) {
            $select->where->addPredicate($existingWhere);
        }
        
        \Ruga\Log::addLog(
            "SQL={$select->getSqlString($dependentTable->getAdapter()->getPlatform())}",
            \Ruga\Log\Severity::DEBUG
        );
        /** @var ResultSetInterface $rowset */
        $rowset = $dependentTable->selectWith($select);

//        $a = [];
        foreach ($rowset as $row) {
//            $a[] = $row;
            $this->dependentRowListAdd($row, $dependentTableConstraint['NAME']);
            $this->addParentToChild($row, $dependentTableConstraint['NAME']);
        }
        
        
        $a = $this->dependentRowListGetDependentRows($dependentTableConstraint['NAME']);
        
        // Must re-initialize ResultSet to keep reference to the rows
        $rowset->initialize($a);
        return $rowset;
    }
    
    
    
    /**
     * Create a new dependent row.
     *
     * @param string|RowInterface|TableInterface $dependentTable Table name, Table class name, Table object or Row
     *                                                           object.
     * @param array                              $rowData
     * @param string|null                        $ruleKey        Name of constraint or reference map entry to use.
     *
     * @return RowInterface
     * @throws ReflectionException
     * @throws Exception
     */
    
    public function createDependentRow($dependentTable, array $rowData = [], ?string $ruleKey = null): RowInterface
    {
        $dependentTable = $this->resolveDependentTable($dependentTable);
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $ruleKey);
        
        try {
            // If parent row is already saved, set foreign key values in dependent row
            foreach ($dependentTableConstraint['COLUMNS'] as $colPos => $column) {
                $rowData[$column] = $this->rowGateway->offsetGet($dependentTableConstraint['REF_COLUMNS'][$colPos]);
            }
        } catch (NoDefaultValueException $e) {
            \Ruga\Log::addLog("parentRow is NEW", \Ruga\Log\Severity::DEBUG);
        }
        
        $dependentRow = $dependentTable->createRow($rowData);
        
        // Add dependent row to list for later saving
        $this->dependentRowListAdd($dependentRow, $dependentTableConstraint['NAME'], 'save');
        $this->addParentToChild($dependentRow, $dependentTableConstraint['NAME'], 'save');
        
        return $dependentRow;
    }
    
    
    
    /**
     * Delete a dependent row. The deletion is done, when the parent row is saved.
     *
     * @param RowInterface $dependentRow
     * @param string|null  $ruleKey
     *
     * @return void
     * @throws Exception
     */
    public function deleteDependentRow(RowInterface $dependentRow, ?string $ruleKey = null)
    {
        $dependentTable = $this->resolveDependentTable($dependentRow);
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $ruleKey);
        
        $this->unlinkDependentRow($dependentRow, $ruleKey);
        
        // Add dependent row to list for later saving
        $this->dependentRowListAdd($dependentRow, $dependentTableConstraint['NAME'], 'delete');
        $this->addParentToChild($dependentRow, $dependentTableConstraint['NAME'], 'unlink');
    }
    
    
    
    /**
     * Link a dependent row to this parent.
     *
     * @param RowInterface $dependentRow
     * @param string|null  $ruleKey
     *
     * @return void
     * @throws Exception
     */
    public function linkDependentRow(RowInterface $dependentRow, ?string $ruleKey = null): RowInterface
    {
        $dependentTable = $this->resolveDependentTable($dependentRow);
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $ruleKey);
        
        try {
            // If parent row is already saved, set foreign key values in dependent row
            foreach ($dependentTableConstraint['COLUMNS'] as $colPos => $column) {
                $dependentRow->offsetSet(
                    $column,
                    $this->rowGateway->offsetGet($dependentTableConstraint['REF_COLUMNS'][$colPos])
                );
            }
        } catch (NoDefaultValueException $e) {
            \Ruga\Log::addLog("parentRow is NEW", \Ruga\Log\Severity::DEBUG);
        }
        
        // Add dependent row to list for later saving
        $this->dependentRowListAdd($dependentRow, $dependentTableConstraint['NAME'], 'save');
        $this->addParentToChild($dependentRow, $dependentTableConstraint['NAME'], 'save');
        
        return $dependentRow;
    }
    
    
    
    /**
     * Unlink a dependent row from this parent.
     *
     * @param RowInterface $dependentRow
     * @param string|null  $ruleKey
     *
     * @return RowInterface
     * @throws Exception
     */
    public function unlinkDependentRow(RowInterface $dependentRow, ?string $ruleKey = null): RowInterface
    {
        $dependentTable = $this->resolveDependentTable($dependentRow);
        $dependentTableConstraint = $this->getDependentTableConstraint($dependentTable, $ruleKey);
        
        foreach ($dependentTableConstraint['COLUMNS'] as $column) {
            $dependentRow->offsetSet($column, null);
        }
        
        // Add dependent row to list for later saving
        $this->dependentRowListAdd($dependentRow, $dependentTableConstraint['NAME'], 'unlink');
        $this->addParentToChild($dependentRow, $dependentTableConstraint['NAME'], 'unlink');
        
        return $dependentRow;
    }
    
    
}