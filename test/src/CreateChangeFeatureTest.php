<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class CreateChangeFeatureTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanReadRow(): void
    {
        $t = new \Ruga\Db\Test\Model\MemberCreateChangeTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MemberCreateChange $row */
        $row = $t->findById(2)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MemberCreateChange::class, $row);
        $this->assertSame(2, intval($row->id));
        $this->assertSame('Vreni Meier', $row->fullname);
    }
    
    
    
    public function testCanCreateRow(): void
    {
        $t = new \Ruga\Db\Test\Model\MemberCreateChangeTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MemberCreateChange $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MemberCreateChange::class, $row);
        
        $this->assertIsString($row->created);
        var_dump($row->created);
        
        $this->assertSame(1, $row->createdBy);
        var_dump($row->createdBy);
    }
    
    
    
    public function testCanSaveRow(): void
    {
        $t = new \Ruga\Db\Test\Model\MemberCreateChangeTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MemberCreateChange $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MemberCreateChange::class, $row);
        
        $row->first_name = 'Peter';
        $row->last_name = 'Müller';
        
        $row->save();
        
        unset($row);
        $row = $t->findById(3)->current();
        $this->assertIsString($row->created);
        $this->assertSame(1, intval($row->createdBy));
        $this->assertSame('Peter', $row->first_name);
        $this->assertSame('', $row->fullname);
    }
    
    
}
