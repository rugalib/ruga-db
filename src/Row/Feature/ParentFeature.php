<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Row\Exception\InvalidArgumentException;
use Ruga\Db\Row\Exception\ReadonlyArgumentException;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Table\AbstractTable;
use Ruga\Db\Table\Feature\MetadataFeature;
use Ruga\Db\Table\TableInterface;

/**
 * The parent feature adds the ability to find, add and remove children
 */
class ParentFeature extends AbstractFeature implements ParentFeatureAttributesInterface
{
    // Stores the dependent (child) table class names
    private array $dependentTables=[];
    
    
    private ?MetadataFeature $metadataFeature=null;
    
    private function getMetadataFeature(): MetadataFeature
    {
        if($this->metadataFeature === null) {
            $this->metadataFeature=$this->rowGateway->getTableGateway()->getFeatureSet()->getFeatureByClassName(MetadataFeature::class);
            if (!$this->metadataFeature || !($this->metadataFeature instanceof MetadataFeature)) {
                throw new \Exception(get_class($this) . " requires " . MetadataFeature::class . " in " . get_class($this->getTableGateway()));
            }
        }
        return $this->metadataFeature;
    }
    
    
    
    public function postInitialize()
    {
        /*
        $metadataFeature=$this->getMetadataFeature();
        foreach($metadataFeature->getMetadata()['constraint_references'] as $constraint_name => $constraint_reference) {
            $this->dependentTables[]=$constraint_reference['TABLE_CLASS'] ?? $constraint_reference['TABLE'];
//            $otherTable=$adapter->tableFactory($constraint_reference['TABLE']);
        }
        */
    }
    
    
    /**
     * Constructs a display name from the given fields.
     * Fullname is saved in the row to speed up queries.
     *
     * @return string
     */
    public function dumpChildren($a, $b, $c): array
    {
        \Ruga\Log::functionHead($this);
        \Ruga\Log::addLog("\$a=$a | \$b=$b | \$c=$c");
        
        $metadataFeature=$this->getMetadataFeature();
        /** @var Adapter $adapter */
        $adapter=$this->rowGateway->getTableGateway()->getAdapter();
        
        
        return $this->dependentTables;
    }
    
    
    public function findDependentRowset($dependentTable, $ruleKey = null, ?Select $select = null): ResultSetInterface
    {
        /** @var Adapter $adapter */
        $adapter=$this->rowGateway->getTableGateway()->getAdapter();
        
        if (is_string($dependentTable)) {
            $dependentTable = $adapter->tableFactory($dependentTable);
        } elseif ($dependentTable instanceof RowInterface) {
            $dependentTable = $dependentTable->getTableGateway();
        }
        
        if(!$dependentTable instanceof TableInterface) {
            throw new \InvalidArgumentException("\$dependentTable must be string, RowInterface or TableInterface");
        }
        
        /** @var TableInterface $dependentTable */
        if($select === null) {
            $select = $dependentTable->getSql()->select();
        } else {
            // Set table
            $select->from($dependentTable->getTable());
        }
        
        // save existing where
        $existingWhere=$select->where;
        $select->reset(Select::WHERE);
        
        $row=$this->rowGateway;
        foreach(($dependentTable->getMetadata()['constraints'] ?? []) as $constraint) {
            if($constraint['REF_TABLE'] == $this->rowGateway->getTableGateway()->getTable()) {
                $select->where(
                    function (Where $where) use ($constraint, $row) {
                        $n = $where->NEST;
                        foreach($constraint['COLUMNS'] as $colPos => $column) {
                            $n->and->equalTo($column, $row->offsetGet($constraint['REF_COLUMNS'][$colPos]));
                        }
                    }
                );
            }
        }
        
        // add existing where at the end in parentheses
        if($existingWhere->count() > 0) {
            $select->where->addPredicate($existingWhere);
        }
        
        \Ruga\Log::addLog("SQL={$select->getSqlString($dependentTable->getAdapter()->getPlatform())}");
        return $dependentTable->selectWith($select);
    }
    
}