<?php

declare(strict_types=1);

namespace Ruga\Db\Table\Feature;

use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;

class TenantFeature extends AbstractFeature
{
    /** @var mixed|null */
    private $tenant_id;
    
    /** @var string */
    private $tenant_id_column;
    
    /** @var string */
    private $tenant_id_column_fullname;
    
    
    
    public function __construct($tenant_id, string $tenant_id_column = 'Tenant_id')
    {
        $this->tenant_id = $tenant_id;
        $this->tenant_id_column = $tenant_id_column;
    }
    
    
    
    public function preInitialize()
    {
//        $this->tenant_id_column="{$this->tableGateway->getTable()}.{$this->tenant_id_column}";
        
        /** @var RowGatewayFeature $rowGatewayFeature */
        $rowGatewayFeature = $this->tableGateway->getFeatureSet()->getFeatureByClassName(RowGatewayFeature::class);
        if ($rowGatewayFeature instanceof RowGatewayFeature) {
            if (!$rowGatewayFeature->getRowGatewayPrototype()->getFeatureSet()->getFeatureByClassName(
                \Ruga\Db\Row\Feature\TenantFeature::class
            )) {
                $rowGatewayFeature->getRowGatewayPrototype()->getFeatureSet()->addFeature(
                    new \Ruga\Db\Row\Feature\TenantFeature($this->tenant_id, $this->tenant_id_column)
                );
            }
        }
    }
    
    
    
    private function getTenantIdColumnFullname(): string
    {
        if (!$this->tenant_id_column_fullname) {
            $this->tenant_id_column_fullname = "{$this->tableGateway->getTable()}.{$this->tenant_id_column}";
        }
        return $this->tenant_id_column_fullname;
    }
    
    
    
    public function preSelect(Select $select)
    {
        if ($this->tenant_id === null) {
            return;
        }
        $select->where(
            function (Where $where) {
                $where->NEST
                    ->equalTo($this->getTenantIdColumnFullname(), $this->tenant_id)
                    ->or
                    ->isNull($this->getTenantIdColumnFullname());
            }
        );
    }

//    public function preInsert(Insert $insert)
//    {
//        $this->__set($this->tenant_id_column, $this->tenant_id);
//    }

//    public function preUpdate(Update $update)
//    {
//        $this->__set($this->tenant_id_column, $this->tenant_id);
//    }
    
    public function preDelete(Delete $delete)
    {
        $delete->where(
            function (Where $where) {
                $where->NEST
                    ->equalTo($this->getTenantIdColumnFullname(), $this->tenant_id)
                    ->or
                    ->isNull($this->getTenantIdColumnFullname());
            }
        );
    }
}