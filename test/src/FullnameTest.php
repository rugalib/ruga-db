<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Test\Model\User;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class FullnameTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanReadRow()
    {
        $t = new \Ruga\Db\Test\Model\FullnameTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Fullname $row */
        $row = $t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Fullname::class, $row);
        $this->assertSame('1', $row->id);
        $this->assertSame('data 1', $row->data);
    }
    
    
    
    public function testCanCreateRow()
    {
        $t = new \Ruga\Db\Test\Model\FullnameTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Fullname $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Fullname::class, $row);
        
        // FullnameTable has no MetadataFeature and thus knows nothing about existing
        // table columns.
        $this->expectException(\Ruga\Db\Row\Exception\InvalidColumnException::class);
        var_dump($row->data);
    }
    
    
    
    public function testCanSaveRow()
    {
        $t = new \Ruga\Db\Test\Model\FullnameTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Fullname $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Fullname::class, $row);
        
        $row->data = 'new data 4';
        $this->assertSame('new data 4', $row->data);
        $row->save();
        var_export($row->idname);
    }
    
    
    
    public function testCanSaveRowInMemberFullname()
    {
        $t = new \Ruga\Db\Test\Model\MemberFullnameTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MemberFullname $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MemberFullname::class, $row);
        
        $row->first_name = 'Kurt';
        $row->last_name = 'Hugentobler';
        $row->created = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $row->createdBy = 1;
        $row->changed = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $row->changedBy = 1;
        $this->assertSame('Kurt Hugentobler', $row->fullname);
        $row->save();
        var_export($row->idname);
    }
    
    
    
    public function testCanSaveRowInMemberRuga()
    {
        $t = new \Ruga\Db\Test\Model\MemberRugaTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MemberRuga $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MemberRuga::class, $row);
        $row->first_name = 'Kurt';
        $row->last_name = 'Hugentobler';
        $this->assertSame('Kurt Hugentobler', $row->fullname);
        $row->save();
        sleep(10);
        $row->save();
        
        // Check if fullname is really saved
        $result = $this->getAdapter()->query("SELECT * FROM Member WHERE id=3", Adapter::QUERY_MODE_EXECUTE);
        $aRow = $result->current();
        $this->assertSame('Kurt Hugentobler', $aRow['fullname']);
        
        // Check if changed is 10s later than created
        $created = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $aRow['created'],
            new \DateTimeZone('Europe/Zurich')
        );
        $changed = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $aRow['changed'],
            new \DateTimeZone('Europe/Zurich')
        );
        $i = $created->diff($changed);
        $this->assertGreaterThanOrEqual(9, $i->s);
    }
    
    
    
    public function testNullableFullnameWorks()
    {
        require(__DIR__ . '/Model/UserTable.php');
        require(__DIR__ . '/Model/User.php');
        $t = new \Ruga\DB\Test\Model\UserTable($this->getAdapter());
        /** @var User $row */
        $row = $t->createRow(['username' => 'username']);
        
        $row->save();
        
        $this->assertIsString($row->getFullname());
    }
    
    
}
