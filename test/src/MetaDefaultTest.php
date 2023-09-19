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
class MetaDefaultTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanReadRow()
    {
        $t=new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row=$t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame(1, $row->id);
        $this->assertSame('data 1', $row->data);
    }
    
    
    public function testCanCreateRow()
    {
        $t=new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row=$t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        
        var_dump($row->data);
        
        $this->expectException(\Ruga\Db\Row\Exception\InvalidColumnException::class);
        var_dump($row->nodata);
    }
    
    
    public function testCanSaveRow()
    {
        $t=new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row=$t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        
        $row->data='new data 4';
        $this->assertSame('new data 4', $row->data);
        
        $row->save();
    }
    
}
