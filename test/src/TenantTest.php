<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Ruga\Db\Adapter\Adapter;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class TenantTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanReadRow()
    {
        $t = (new \Ruga\Db\Test\Model\SimpleTable(
            $this->getAdapter(),
            (new \Ruga\Db\Table\Feature\FeatureSet())->addFeature(
                new \Ruga\Db\Table\Feature\TenantFeature(1)
            )
        ));
        /** @var \Ruga\Db\Test\Model\Simple $row */
        $row = $t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        $this->assertSame(1, intval($row->id));
        $this->assertSame('data 1', $row->data);
        
        $row = $t->findById(4)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        $this->assertSame(4, intval($row->id));
        $this->assertSame('data 4', $row->data);
        
        $row = $t->findById(6)->current();
        $this->assertSame(null, $row);
    }
    
    
    
    public function testCanReadAllRowsIfTenantNull()
    {
        $t = (new \Ruga\Db\Test\Model\SimpleTable(
            $this->getAdapter(),
            (new \Ruga\Db\Table\Feature\FeatureSet())->addFeature(
                new \Ruga\Db\Table\Feature\TenantFeature(null)
            )
        ));
        /** @var \Ruga\Db\Test\Model\Simple $row */
        $row = $t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        $this->assertSame(1, intval($row->id));
        $this->assertSame('data 1', $row->data);
        
        $row = $t->findById(4)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        $this->assertSame(4, intval($row->id));
        $this->assertSame('data 4', $row->data);
        
        $row = $t->findById(6)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        $this->assertSame(6, intval($row->id));
        $this->assertSame('data 6', $row->data);
    }
    
    
    
    public function testCanCreateRow()
    {
        $t = (new \Ruga\Db\Test\Model\SimpleTable(
            $this->getAdapter(),
            (new \Ruga\Db\Table\Feature\FeatureSet())->addFeature(
                new \Ruga\Db\Table\Feature\TenantFeature(1)
            )
        ));
        /** @var \Ruga\Db\Test\Model\Simple $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        
        // FullnameTable has no MetadataFeature and thus knows nothing about existing
        // table columns.
        var_dump($row->toArray());
    }
    
    
    
    public function testCanSaveRow()
    {
        $t = (new \Ruga\Db\Test\Model\SimpleTable(
            $this->getAdapter(),
            (new \Ruga\Db\Table\Feature\FeatureSet())->addFeature(
                new \Ruga\Db\Table\Feature\TenantFeature(1)
            )
        ));
        /** @var \Ruga\Db\Test\Model\Simple $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        
        $row->data = 'new data 8';
        $this->assertSame('new data 8', $row->data);
        $row->save();
        $this->assertSame('new data 8', $row->data);
        $this->assertSame(1, intval($row->Tenant_id));
    }
    
    
    
    public function testCanSaveRow2()
    {
        $t = new \Ruga\Db\Test\Model\SimpleTenantTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\SimpleTenant $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\SimpleTenant::class, $row);
        
        $row->data = 'new data 8';
        $this->assertSame('new data 8', $row->data);
        $row->save();
        $this->assertSame('new data 8', $row->data);
        $this->assertSame(1, intval($row->Tenant_id));
    }
    
    
    
    public function testCanSaveRowForNeutralTenant()
    {
        $t = (new \Ruga\Db\Test\Model\SimpleTable(
            $this->getAdapter(),
            (new \Ruga\Db\Table\Feature\FeatureSet())->addFeature(
                new \Ruga\Db\Table\Feature\TenantFeature(1)
            )
        ));
        /** @var \Ruga\Db\Test\Model\Simple $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        
        // Must be my default tenant id
        $this->assertSame(1, $row->Tenant_id);
        
        $row->data = 'new data 8';
        // Setting tenant id to 'no tenant'
        $row->Tenant_id = null;
        $this->assertSame(null, $row->Tenant_id);
        
        $this->assertSame('new data 8', $row->data);
        $row->save();
        $this->assertSame('new data 8', $row->data);
        $this->assertSame(null, $row->Tenant_id);
    }
    
    
    
    public function testCanSaveRowForNeutralTenant2()
    {
        $t = new \Ruga\Db\Test\Model\SimpleTenantTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\SimpleTenant $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\SimpleTenant::class, $row);
        
        // Must be my default tenant id
        $this->assertSame(1, $row->Tenant_id);
        
        $row->data = 'new data 8';
        // Setting tenant id to 'no tenant'
        $row->Tenant_id = null;
        $this->assertSame(null, $row->Tenant_id);
        
        $this->assertSame('new data 8', $row->data);
        $row->save();
        $this->assertSame('new data 8', $row->data);
        $this->assertSame(null, $row->Tenant_id);
    }
    
    
    
    public function testCanNotSaveRowForOtherTenant()
    {
        $t = (new \Ruga\Db\Test\Model\SimpleTable(
            $this->getAdapter(),
            (new \Ruga\Db\Table\Feature\FeatureSet())->addFeature(
                new \Ruga\Db\Table\Feature\TenantFeature(1)
            )
        ));
        /** @var \Ruga\Db\Test\Model\Simple $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        $row->data = 'new data 8';
        
        // Must be my default tenant id
        $this->assertSame(1, $row->Tenant_id);
        
        $this->expectException(\Ruga\Db\Row\Exception\InvalidTenantException::class);
        // Setting tenant id to illegal value
        $row->Tenant_id = 2;
    }
    
    
    
    public function testCanNotSaveRowForOtherTenant2()
    {
        $t = new \Ruga\Db\Test\Model\SimpleTenantTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\SimpleTenant $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\SimpleTenant::class, $row);
        $row->data = 'new data 8';
        
        // Must be my default tenant id
        $this->assertSame(1, $row->Tenant_id);
        
        $this->expectException(\Ruga\Db\Row\Exception\InvalidTenantException::class);
        // Setting tenant id to illegal value
        $row->Tenant_id = 2;
    }
    
    
}
