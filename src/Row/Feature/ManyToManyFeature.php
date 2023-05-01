<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\RowGateway\RowGateway;
use Laminas\Db\Sql\Expression;
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
 * The parent feature adds the ability to find, add and remove many-to-many relations.
 */
class ManyToManyFeature extends AbstractFeature implements ManyToManyFeatureAttributesInterface
{
    private ?MetadataFeature $metadataFeature = null;
    
    private $manyToManyRows = [];
    
    
    
    public function postInitialize()
    {
        if (!$this->rowGateway instanceof ManyToManyFeatureAttributesInterface) {
            throw new \RuntimeException(
                get_class($this->rowGateway) . " must implement " . ManyToManyFeatureAttributesInterface::class
            );
        }
    }
    
    
    
    /**
     * Save (or delete) the associated match row.
     *
     * @param RowInterface $iRow
     * @param array        $mRowsList
     *
     * @return void
     * @throws \Exception
     */
    private function saveMRow(RowInterface $iRow, array $mRowsList)
    {
        foreach ($mRowsList as $mConstraintName => $mRows) {
            foreach ($mRows as $mUniqueid => $mRowInfo) {
                /** @var RowInterface $mRow */
                $mRow = $mRowInfo['mRow'];
                
                if ($mRowInfo['action'] == 'save') {
                    $mRow->save();
                    
                    // Update foreign key
                    $iTable = $this->resolveTableArgument($iRow);
                    $mTable = $this->resolveTableArgument($mRow);
                    $mTableConstraint = $this->getManyToManyTableConstraint($mTable, $iTable, $mConstraintName);
                    foreach ($mTableConstraint['COLUMNS'] as $colPos => $column) {
                        $iRow->offsetSet(
                            $column,
                            $mRow->offsetGet($mTableConstraint['REF_COLUMNS'][$colPos])
                        );
                    }
                }
                if ($mRowInfo['action'] == 'unlink') {
                    $mRow->save();
                    
                    // Update foreign key
                    $iTable = $this->resolveTableArgument($iRow);
                    $mTable = $this->resolveTableArgument($mRow);
                    $mTableConstraint = $this->getManyToManyTableConstraint($mTable, $iTable, $mConstraintName);
                    foreach ($mTableConstraint['COLUMNS'] as $colPos => $column) {
                        $iRow->offsetSet(
                            $column,
                            null
                        );
                    }
                }
                if ($mRowInfo['action'] == 'delete') {
                    $mRow->delete();
                }
            }
        }
    }
    
    
    
    /**
     * Save (or delete) intersection and match rows.
     *
     * @return void
     * @throws \Exception
     */
    private function saveIntersectionRow()
    {
        foreach ($this->manyToManyRows as $iConstraintName => $iRows) {
            foreach ($iRows as $iUniqueid => $iRowInfo) {
                /** @var RowInterface $iRow */
                $iRow = $iRowInfo['iRow'];
                
                if ($iRowInfo['action'] == 'save') {
                    $this->saveMRow($iRow, $iRowInfo['m']);
                    
                    if (!$this->rowGateway->isNew()) {
                        // If parent row is already saved, set foreign key values in dependent row
                        $iTable = $this->resolveTableArgument($iRow);
                        $nTable = $this->resolveTableArgument($this->rowGateway);
                        $iTableConstraint = $this->getManyToManyTableConstraint(
                            $nTable,
                            $iTable,
                            $iConstraintName
                        );
                        foreach ($iTableConstraint['COLUMNS'] as $colPos => $column) {
                            $iRow->offsetSet(
                                $column,
                                $this->rowGateway->offsetGet(
                                    $iTableConstraint['REF_COLUMNS'][$colPos]
                                )
                            );
                        }
                    } else {
                        throw new \RuntimeException('Parent row must be saved first');
                    }
                    $iRow->save();
                }
                if ($iRowInfo['action'] == 'unlink') {
                    $this->saveMRow($iRow, $iRowInfo['m']);
                    $iTable = $this->resolveTableArgument($iRow);
                    $nTable = $this->resolveTableArgument($this->rowGateway);
                    $iTableConstraint = $this->getManyToManyTableConstraint(
                        $nTable,
                        $iTable,
                        $iConstraintName
                    );
                    foreach ($iTableConstraint['COLUMNS'] as $colPos => $column) {
                        $iRow->offsetSet(
                            $column,
                            null
                        );
                    }
                    $iRow->save();
                }
                if ($iRowInfo['action'] == 'delete') {
                    $iRow->delete();
                    $this->saveMRow($iRow, $iRowInfo['m']);
                }
            }
        }
    }
    
    
    
    /**
     * Before this (parent) row is updated, save all intersection (child) and match (intersection's parent) rows.
     *
     * @return void
     * @throws \Exception
     */
    public function preUpdate()
    {
        \Ruga\Log::functionHead($this);
        $this->saveIntersectionRow();
    }
    
    
    
    /**
     * After this (parent) row is inserted, save all intersection (child) and match (intersection's parent) rows.
     *
     * @return void
     * @throws \Exception
     */
    public function postInsert()
    {
        \Ruga\Log::functionHead($this);
        $this->saveIntersectionRow();
    }
    
    
    
    public function postSave()
    {
        // Successfully saved => delete dependent row list
        $this->manyToManyRows = [];
    }
    
    
    
    /**
     * Save the relation to the internal row store.
     *
     * @param RowInterface $mRow
     * @param RowInterface $iRow
     * @param string       $mConstraintName
     * @param string       $nConstraintName
     * @param string       $action
     *
     * @return void
     * @throws \Exception
     */
    private function manyToManyRowListAdd(
        RowInterface $mRow,
        RowInterface $iRow,
        string $mConstraintName,
        string $nConstraintName,
        string $action = 'save'
    ) {
        $nTable = $this->rowGateway->getTableGateway();
        $iTable = $this->resolveTableArgument($iRow);
        $iTableConstraint = $this->getManyToManyTableConstraint($nTable, $iTable, $nConstraintName);
        $nConstraintName = $iTableConstraint['NAME'];
        
        $iUniqueid = implode('-', $iRow->primaryKeyData ?? []);
        if (empty($iUniqueid)) {
            $iUniqueid = '?' . date('U') . '?' . sprintf('%05u', count($this->manyToManyRows));
        }
        $iUniqueid .= '@' . get_class($iRow);
        
        $this->manyToManyRows[$nConstraintName][$iUniqueid]['uniqueid'] = $iUniqueid;
        $this->manyToManyRows[$nConstraintName][$iUniqueid]['action'] = $action;
        $this->manyToManyRows[$nConstraintName][$iUniqueid]['iRow'] = $iRow;
        $this->manyToManyRows[$nConstraintName][$iUniqueid]['m'] = [];
        
        
        $mTable = $this->resolveTableArgument($mRow);
        $mTableConstraint = $this->getManyToManyTableConstraint($mTable, $iTable, $mConstraintName);
        $mConstraintName = $mTableConstraint['NAME'];
        
        $mUniqueid = implode('-', $mRow->primaryKeyData ?? []);
        if (empty($mUniqueid)) {
            $mUniqueid = '?' . date('U') . '?' . sprintf('%05u', count($this->manyToManyRows));
        }
        $mUniqueid .= '@' . get_class($mRow);
        $this->manyToManyRows[$nConstraintName][$iUniqueid]['m'][$mConstraintName][$mUniqueid]['uniqueid'] = $mUniqueid;
        $this->manyToManyRows[$nConstraintName][$iUniqueid]['m'][$mConstraintName][$mUniqueid]['action'] = $action;
        $this->manyToManyRows[$nConstraintName][$iUniqueid]['m'][$mConstraintName][$mUniqueid]['mRow'] = $mRow;
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
     * @param TableInterface $dependentTable
     * @param string|null    $ruleKey
     *
     * @return array
     */
    private function resolveManyToManyTableConstraints(
        TableInterface $parentTable,
        TableInterface $dependentTable,
        ?string $ruleKey = null
    ): array {
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
     * @param TableInterface $dependentTable
     * @param string|null    $ruleKey
     *
     * @return array
     */
    private function getManyToManyTableConstraint(
        TableInterface $parentTable,
        TableInterface $dependentTable,
        ?string $ruleKey = null
    ): array {
        $manyToManyTableConstraints = $this->resolveManyToManyTableConstraints($parentTable, $dependentTable, $ruleKey);
        if (count($manyToManyTableConstraints) > 1) {
            throw new TooManyConstraintsException(
                "More than 1 constraints found for relation {$parentTable->getTable()} ||--o{ {$dependentTable->getTableGateway()->getTable()}: "
                . implode(', ', array_map(static fn($item) => $item['NAME'], $manyToManyTableConstraints))
            );
        }
        if (count($manyToManyTableConstraints) < 1) {
            throw new NoConstraintsException(
                "No constraints found for relation {$parentTable->getTable()} ||--o{ {$dependentTable->getTableGateway()->getTable()}"
            );
        }
        
        return array_shift($manyToManyTableConstraints);
    }
    
    
    
    /**
     * If parent row implements ParentFeature, store a reference to this dependent row in parent.
     *
     * @param RowInterface $parentRow
     * @param string       $constraintName
     *
     * @return void
     */
//    private function addChildToParent(RowInterface $parentRow, string $constraintName, string $action = 'save')
//    {
//        if ($parentRow instanceof ParentFeatureAttributesInterface) {
//            $parentRow->dependentRowListAdd($this->rowGateway, $constraintName, $action);
//        }
//    }
    
    
    /**
     * Find rows via many-to-many relation.
     * n:m Beziehung.
     *
     * @param mixed       $mTable
     * @param mixed       $iTable
     * @param string|null $nRuleKey
     * @param string|null $mRuleKey
     * @param Select|null $select
     *
     * @return ResultSetInterface
     * @throws \Exception
     */
    public function findManyToManyRowset(
        $mTable,
        $iTable,
        ?string $nRuleKey = null,
        ?string $mRuleKey = null,
        ?Select $select = null
    ): ResultSetInterface {
        $nTable = $this->rowGateway->getTableGateway();
        $mTable = $this->resolveTableArgument($mTable);
        $iTable = $this->resolveTableArgument($iTable);
        $mTableConstraint = $this->getManyToManyTableConstraint($mTable, $iTable, $mRuleKey);
        $nTableConstraint = $this->getManyToManyTableConstraint($nTable, $iTable, $nRuleKey);
        
        
        if ($select === null) {
            $select = $mTable->getSql()->select();
        } else {
            // Set table
            $select->from($mTable->getTable());
        }
        
        // save existing where
        $existingWhere = $select->where;
        $select->reset(Select::WHERE);
        
        
        // add the dependent where
        $row = $this->rowGateway;
        
        // Create join from $mTable to $intersectionTable
        $aOn = [];
        foreach ($mTableConstraint['COLUMNS'] as $colPos => $column) {
            $aOn[] = "{$mTableConstraint['TABLE']}.{$column}={$mTableConstraint['REF_TABLE']}.{$mTableConstraint['REF_COLUMNS'][$colPos]}";
        }
        $select->join($iTable->getTable(), implode(' AND ', $aOn), [], Select::JOIN_INNER);
        
        // Create where statement for $intersectionTable
        $select->where(
            function (Where $where) use ($nTableConstraint, $row) {
                $n = $where->NEST;
                foreach ($nTableConstraint['COLUMNS'] as $colPos => $column) {
                    $n->and->equalTo(
                        "{$nTableConstraint['TABLE']}.{$column}",
                        $row->offsetGet($nTableConstraint['REF_COLUMNS'][$colPos])
                    );
                }
            }
        );
        
        // add existing where at the end in parentheses
        if ($existingWhere->count() > 0) {
            $select->where->addPredicate($existingWhere);
        }
        
        \Ruga\Log::addLog("SQL={$select->getSqlString($mTable->getAdapter()->getPlatform())}");
        $mRowset = $mTable->selectWith($select);
        
        $a = [];
        /** @var RowInterface $mRow */
        foreach ($mRowset as $mRow) {
            $a[] = $mRow;
            $iRowset=$this->findIntersectionRows($mRow, $iTable, $nRuleKey, $mRuleKey);
            /** @var RowInterface $iRow */
            foreach($iRowset as $iRow) {
                $this->manyToManyRowListAdd($mRow, $iRow, $mTableConstraint['NAME'], $nTableConstraint['NAME']);
            }
        }
        
        // Must re-initialize ResultSet to keep reference to the rows
        $mRowset->initialize($a);
        
        return $mRowset;
    }
    
    
    
    /**
     * Find intersection rows from many-to-many relation.
     *
     * @param RowInterface $mRow
     * @param mixed        $iTable
     * @param string|null  $nRuleKey
     * @param string|null  $mRuleKey
     * @param Select|null  $select
     *
     * @return ResultSetInterface
     * @throws \Exception
     */
    public function findIntersectionRows(
        RowInterface $mRow,
        $iTable,
        ?string $nRuleKey = null,
        ?string $mRuleKey = null,
        ?Select $select = null
    ): ResultSetInterface {
        $nTable = $this->rowGateway->getTableGateway();
        $mTable = $this->resolveTableArgument($mRow);
        $iTable = $this->resolveTableArgument($iTable);
        $mTableConstraint = $this->getManyToManyTableConstraint($mTable, $iTable, $mRuleKey);
        $nTableConstraint = $this->getManyToManyTableConstraint($nTable, $iTable, $nRuleKey);
        
        if ($select === null) {
            $select = $iTable->getSql()->select();
        } else {
            // Set table
            $select->from($iTable->getTable());
        }
        
        // save existing where
        $existingWhere = $select->where;
        $select->reset(Select::WHERE);
        
        
        // add the dependent where
        $nRow = $this->rowGateway;
        
        // Create where statement for $intersectionTable
        $select->where(
            function (Where $where) use ($nTableConstraint, $mTableConstraint, $nRow, $mRow) {
                $n = $where->NEST;
                foreach ($nTableConstraint['COLUMNS'] as $colPos => $column) {
                    $n->and->equalTo(
                        "{$nTableConstraint['TABLE']}.{$column}",
                        $nRow->offsetGet($nTableConstraint['REF_COLUMNS'][$colPos])
                    );
                }
                foreach ($mTableConstraint['COLUMNS'] as $colPos => $column) {
                    $n->and->equalTo(
                        "{$mTableConstraint['TABLE']}.{$column}",
                        $mRow->offsetGet($mTableConstraint['REF_COLUMNS'][$colPos])
                    );
                }
            }
        );
        
        // add existing where at the end in parentheses
        if ($existingWhere->count() > 0) {
            $select->where->addPredicate($existingWhere);
        }
        
        \Ruga\Log::addLog("SQL={$select->getSqlString($iTable->getAdapter()->getPlatform())}");
        $iRowset = $iTable->selectWith($select);
        
        return $iRowset;
    }
    
    
    
    /**
     * Create a new row in the $mTable, linked via $intersectionTable.
     *
     * @param mixed       $mTable
     * @param mixed       $iTable
     * @param array       $mRowData
     * @param array       $iRowData
     * @param string|null $mRuleKey
     * @param string|null $nRuleKey
     *
     * @return RowInterface
     * @throws \ReflectionException
     */
    public function createManyToManyRow(
        $mTable,
        $iTable,
        array $mRowData = [],
        array $iRowData = [],
        ?string $mRuleKey = null,
        ?string $nRuleKey = null
    ): RowInterface {
        $nTable = $this->rowGateway->getTableGateway();
        $mTable = $this->resolveTableArgument($mTable);
        $iTable = $this->resolveTableArgument($iTable);
        $mTableConstraint = $this->getManyToManyTableConstraint($mTable, $iTable, $mRuleKey);
        $nTableConstraint = $this->getManyToManyTableConstraint($nTable, $iTable, $nRuleKey);
        
        if (!$this->rowGateway->isNew()) {
            // If this row is already saved, set foreign key values in dependent row
            foreach ($nTableConstraint['COLUMNS'] as $colPos => $column) {
                $iRowData[$column] = $this->rowGateway->offsetGet($nTableConstraint['REF_COLUMNS'][$colPos]);
            }
        }
        $iRow = $iTable->createRow($iRowData);
        $mRow = $mTable->createRow($mRowData);
        // No foreign key yet!
        
        $this->manyToManyRowListAdd($mRow, $iRow, $mTableConstraint['NAME'], $nTableConstraint['NAME']);
        
        return $mRow;
    }
    
    
    
    /**
     * Link an existing $mRow to the $nRow using $iTable.
     *
     * @param RowInterface $mRow
     * @param mixed        $iTable
     * @param array        $iRowData
     * @param string|null  $mRuleKey
     * @param string|null  $nRuleKey
     *
     * @return RowInterface
     * @throws \ReflectionException
     */
    public function linkManyToManyRow(
        RowInterface $mRow,
        $iTable,
        array $iRowData = [],
        ?string $mRuleKey = null,
        ?string $nRuleKey = null
    ): RowInterface {
        $nTable = $this->rowGateway->getTableGateway();
        $mTable = $this->resolveTableArgument($mRow);
        $iTable = $this->resolveTableArgument($iTable);
        $mTableConstraint = $this->getManyToManyTableConstraint($mTable, $iTable, $mRuleKey);
        $nTableConstraint = $this->getManyToManyTableConstraint($nTable, $iTable, $nRuleKey);
        
        $iRow = $iTable->createRow($iRowData);
        
        if (!$this->rowGateway->isNew()) {
            // If parent row is already saved, set foreign key values in dependent row
            foreach ($mTableConstraint['COLUMNS'] as $colPos => $column) {
                $iRow->offsetSet(
                    $column,
                    $this->rowGateway->offsetGet($mTableConstraint['REF_COLUMNS'][$colPos])
                );
            }
        }
        
        if (!$mRow->isNew()) {
            // If parent row is already saved, set foreign key values in dependent row
            foreach ($nTableConstraint['COLUMNS'] as $colPos => $column) {
                $iRow->offsetSet(
                    $column,
                    $mRow->offsetGet($nTableConstraint['REF_COLUMNS'][$colPos])
                );
            }
        }
        
        // Add dependent row to list for later saving
        $this->manyToManyRowListAdd($mRow, $iRow, $mTableConstraint['NAME'], $nTableConstraint['NAME']);
        
        return $mRow;
    }
    
    
    
    /**
     * Unlink intersection and match row. Unlinking is done, when this row is saved. This does not delete the
     * intersection row(s), but sets the foreign keys to NULL. If intersection row does not allow NULL values for the
     * foreign keys this will likely throw an error.
     *
     * @param RowInterface $mRow
     * @param              $iTable
     * @param string|null  $mRuleKey
     * @param string|null  $nRuleKey
     * @param string       $action
     *
     * @return RowInterface
     * @throws \Exception
     */
    public function unlinkManyToManyRow(
        RowInterface $mRow,
        $iTable,
        ?string $mRuleKey = null,
        ?string $nRuleKey = null,
        string $action = 'unlink'
    ): RowInterface {
        $nTable = $this->rowGateway->getTableGateway();
        $mTable = $this->resolveTableArgument($mRow);
        $iTable = $this->resolveTableArgument($iTable);
        $mTableConstraint = $this->getManyToManyTableConstraint($mTable, $iTable, $mRuleKey);
        $nTableConstraint = $this->getManyToManyTableConstraint($nTable, $iTable, $nRuleKey);
        
        $iRows = $this->findIntersectionRows($mRow, $iTable);
        /** @var RowInterface $iRow */
        foreach ($iRows as $iRow) {
            foreach ($mTableConstraint['COLUMNS'] as $colPos => $column) {
                $iRow->offsetSet(
                    $column,
                    null
                );
            }
            
            foreach ($nTableConstraint['COLUMNS'] as $colPos => $column) {
                $iRow->offsetSet(
                    $column,
                    null
                );
            }
            
            $this->manyToManyRowListAdd($mRow, $iRow, $mTableConstraint['NAME'], $nTableConstraint['NAME'], $action);
        }
        
        return $mRow;
    }
    
    
    
    /**
     * Delete intersection and match row. Deletion is done, when this row is saved.
     *
     * @param RowInterface $mRow
     * @param mixed        $iTable
     * @param string|null  $mRuleKey
     * @param string|null  $nRuleKey
     *
     * @return RowInterface
     * @throws \Exception
     */
    public function deleteManyToManyRow(
        RowInterface $mRow,
        $iTable,
        ?string $mRuleKey = null,
        ?string $nRuleKey = null
    ): RowInterface {
//        $nTable = $this->rowGateway->getTableGateway();
//        $mTable = $this->resolveTableArgument($mRow);
//        $iTable = $this->resolveTableArgument($iTable);
//        $mTableConstraint = $this->getManyToManyTableConstraint($mTable, $iTable, $mRuleKey);
//        $nTableConstraint = $this->getManyToManyTableConstraint($nTable, $iTable, $nRuleKey);
        
        $this->unlinkManyToManyRow($mRow, $iTable, $mRuleKey, $nRuleKey, 'delete');
        
        return $mRow;
    }
    
}