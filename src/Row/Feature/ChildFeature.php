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
class ChildFeature extends AbstractFeature implements ChildFeatureAttributesInterface
{
    private ?MetadataFeature $metadataFeature = null;
    
    private $parentRows = [];
    
    
    
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
        if (!$this->rowGateway instanceof ChildFeatureAttributesInterface) {
            throw new \RuntimeException(
                get_class($this->rowGateway) . " must implement " . ChildFeatureAttributesInterface::class
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
                        $dependentTableConstraint = $this->getParentTableConstraint($this->resolveTableArgument($dependentRow), $constraintName);
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
//        $this->saveDependentRows();
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
//        $this->saveDependentRows();
    }
    
    
    public function postSave()
    {
        // Successfully saved => delete dependent row list
//        $this->dependentRows=[];
    }
    
    
    private function parentRowListAdd(RowInterface $parentRow, string $constraintName, string $action='save')
    {
        $uniqueid=implode('-', $parentRow->primaryKeyData ?? []);
        if(empty($uniqueid)) {
            $uniqueid='?' . date('U') . '?' . sprintf('%05u', count($this->parentRows));
        }
        
        $uniqueid.='@' . get_class($parentRow);
        
        $this->parentRows[$constraintName][$uniqueid]['uniqueid']=$uniqueid;
        $this->parentRows[$constraintName][$uniqueid]['action']=$action;
        $this->parentRows[$constraintName][$uniqueid]['dependentRow']=$parentRow;
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
        $dependentTable=$this->rowGateway->getTableGateway();
        
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
    public function getParentTableConstraint(TableInterface $parentTable, ?string $ruleKey = null): array
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
     * Find the parent row.
     *
     * @param $parentTable
     * @param string|null $ruleKey
     * @param Select|null $select
     * @return RowInterface
     * @throws \Exception
     */
    public function findParentRow($parentTable, ?string $ruleKey = null, ?Select $select = null): RowInterface
    {
        $parentTable = $this->resolveTableArgument($parentTable);
        
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
        $parentTableConstraint = $this->getParentTableConstraint($parentTable, $ruleKey);
        $select->where(
            function (Where $where) use ($parentTableConstraint, $row) {
                $n = $where->NEST;
                foreach ($parentTableConstraint['REF_COLUMNS'] as $colPos => $column) {
                    $n->and->equalTo($column, $row->offsetGet($parentTableConstraint['COLUMNS'][$colPos]));
                }
            }
        );
        
        // add existing where at the end in parentheses
        if ($existingWhere->count() > 0) {
            $select->where->addPredicate($existingWhere);
        }
        
        \Ruga\Log::addLog("SQL={$select->getSqlString($parentTable->getAdapter()->getPlatform())}");
        $parentRow=$parentTable->selectWith($select)->current();
        
        $this->parentRowListAdd($parentRow, $parentTableConstraint['NAME']);
        return $parentRow;
    }
    
    
}