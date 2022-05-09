<?php

declare(strict_types=1);

namespace Ruga\Db\Test;

use Ruga\Db\Adapter\Adapter;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class MetaTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanReadRow()
    {
        $t = new \Ruga\Db\Test\Model\MetaTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Meta $row */
        $row = $t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Meta::class, $row);
        $this->assertSame(1, $row->id);
        $this->assertSame('data 1', $row->data);
    }
    
    
    
    public function testCanCreateRow()
    {
        $t = new \Ruga\Db\Test\Model\MetaTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Meta $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Meta::class, $row);
        
        $this->expectException(\Ruga\Db\Row\Exception\NoDefaultValueException::class);
        var_dump($row->data);
    }
    
    
    
    public function testCanSaveRow()
    {
        $t = new \Ruga\Db\Test\Model\MetaTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Meta $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Meta::class, $row);
        
        $row->data = 'new data 4';
        $this->assertSame('new data 4', $row->data);
        
        $row->save();
    }
    
}
