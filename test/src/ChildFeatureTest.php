<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Ruga\Db\Row\Exception\FeatureMissingException;
use Ruga\Db\Row\Exception\InvalidForeignKeyException;
use Ruga\Db\Row\Exception\NoConstraintsException;
use Ruga\Db\Row\Exception\TooManyConstraintsException;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Test\Model\Cart;
use Ruga\Db\Test\Model\CartItem;
use Ruga\Db\Test\Model\CartItemTable;
use Ruga\Db\Test\Model\CartTable;
use Ruga\Db\Test\Model\MetaDefault;
use Ruga\Db\Test\Model\MetaDefaultTable;
use Ruga\Db\Test\Model\Muster;
use Ruga\Db\Test\Model\MusterTable;
use Ruga\Db\Test\Model\SimpleTable;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class ChildFeatureTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanFindParentRow()
    {
        $t = new \Ruga\Db\Test\Model\CartItemTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\CartItem $row */
        $row = $t->findById(8)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\CartItem::class, $row);
        $this->assertSame('8', "{$row->id}");
        $this->assertSame('cart 2, item 4', $row->fullname);
        
        
        $item = $row->findParentRow(CartTable::class);
        print_r($item->idname);
        echo PHP_EOL;
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Cart::class, $item);
    }
    
    
    
    public function testCanCreateNewParentRowButNotSave()
    {
        $t = new \Ruga\Db\Test\Model\CartItemTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\CartItem $row */
        $row = $t->createRow(['fullname' => 'cart 3, item 1', 'seq' => 1]);
        
        $row->createParentRow(CartTable::class, ['fullname' => 'cart 3']);
        
        $this->expectException(InvalidForeignKeyException::class);
        $row->save();
    }
    
    
    
    public function testCanCreateNewParentRow()
    {
        $t = new \Ruga\Db\Test\Model\CartItemTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\CartItem $row */
        $row = $t->createRow(['fullname' => 'cart 3, item 1', 'seq' => 1]);
        
        $parentRow = $row->createParentRow(CartTable::class, ['fullname' => 'cart 3']);
        
        $parentRow->save();
        
        $item = $row->findParentRow(CartTable::class);
        print_r($item->idname);
        echo PHP_EOL;
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Cart::class, $item);
    }
    
    
    
    public function testCanLinkParent()
    {
        $parentTable = new \Ruga\Db\Test\Model\CartTable($this->getAdapter());
        /** @var Cart $parentRow */
        $parentRow = $parentTable->findById(2)->current(2);
        
        $dependentTable = new \Ruga\Db\Test\Model\CartItemTable($this->getAdapter());
        /** @var CartItem $dependentRow */
        $dependentRow = $dependentTable->createRow(['fullname' => 'cart 2, item 7', 'seq' => 7]);
        
        $dependentRow->linkParentRow($parentRow);
        $dependentRow->save();
        
        $items = $parentRow->findDependentRowset(CartItemTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount(7, $items);
    }
    
    
    
    public function testCanLinkParentAndSaveThruParent()
    {
        $parentTable = new \Ruga\Db\Test\Model\CartTable($this->getAdapter());
        /** @var Cart $parentRow */
        $parentRow = $parentTable->findById(2)->current(2);
        
        $dependentTable = new \Ruga\Db\Test\Model\CartItemTable($this->getAdapter());
        /** @var CartItem $dependentRow */
        $dependentRow = $dependentTable->createRow(['fullname' => 'cart 2, item 7', 'seq' => 7]);
        $dependentRow->linkParentRow($parentRow);
        
        $dependentRow = $dependentTable->createRow(['fullname' => 'cart 2, item 8', 'seq' => 8]);
        $dependentRow->linkParentRow($parentRow);
        
        $parentRow->save();
        
        $items = $parentRow->findDependentRowset(CartItemTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount(8, $items);
    }
    
    
    
    public function testCanUnlinkParentRow()
    {
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        /** @var Muster $dependentRow */
        $dependentRow = $dependentTable->findById(1)->current();
        
        $dependentRow->unlinkParentRow(MetaDefaultTable::class);
        $dependentRow->save();
        
        $parentTable = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var MetaDefault $parentRow */
        $parentRow = $parentTable->findById(5)->current();
        $items = $parentRow->findDependentRowset(MusterTable::class);
        $this->assertCount(0, $items);
        
        $this->assertNull($dependentRow->Simple_id);
    }
    
    
    
    public function testCanDeleteParentRow()
    {
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        /** @var Muster $dependentRow */
        $dependentRow = $dependentTable->findById(1)->current();
        
        $dependentRow->deleteParentRow(MetaDefaultTable::class);
        $dependentRow->save();
        
        $parentTable = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var MetaDefault $parentRow */
        $parentRow = $parentTable->findById(5)->current();
        $this->assertNull($parentRow);
        
        $this->assertNull($dependentRow->Simple_id);
    }
    
    
    
    
}
