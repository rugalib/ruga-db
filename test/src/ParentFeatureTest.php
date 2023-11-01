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
use Ruga\Db\Row\Exception\NoConstraintsException;
use Ruga\Db\Row\Exception\TooManyConstraintsException;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Test\Model\Cart;
use Ruga\Db\Test\Model\CartItem;
use Ruga\Db\Test\Model\CartItemTable;
use Ruga\Db\Test\Model\CartTable;
use Ruga\Db\Test\Model\MetaDefaultTable;
use Ruga\Db\Test\Model\Muster;
use Ruga\Db\Test\Model\MusterTable;
use Ruga\Db\Test\Model\SimpleTable;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class ParentFeatureTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanFindDependentRows()
    {
        $t = new \Ruga\Db\Test\Model\CartTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Cart $row */
        $row = $t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Cart::class, $row);
        $this->assertSame('1', "{$row->id}");
        $this->assertSame('cart 1', $row->fullname);
        
        
        $items = $row->findDependentRowset(CartItemTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        
        $this->assertCount(4, $items);
    }
    
    
    
    public function testCanFindDependentRowsWithQuery()
    {
        $t = new \Ruga\Db\Test\Model\CartTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Cart $row */
        $row = $t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Cart::class, $row);
        $this->assertSame('1', "{$row->id}");
        $this->assertSame('cart 1', $row->fullname);
        
        $items = $row->findDependentRowset(
            CartItemTable::class,
            null,
            (new Select())->where(function (Where $where) {
                $where->like('fullname', '%item 2');
                $where->or->like('fullname', '%item 3');
            })
        );
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        
        $this->assertCount(2, $items);
    }
    
    
    
    public function testThrowsExceptionWhenConstraintIsNotUnique()
    {
        $t = new \Ruga\Db\Test\Model\UserTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\User $row */
        $row = $t->findById(3)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\User::class, $row);
        $this->assertSame('3', "{$row->id}");
        $this->assertSame('admin', $row->fullname);
        
        $this->expectException(TooManyConstraintsException::class);
        $items = $row->findDependentRowset(CartTable::class);
    }
    
    
    
    public function testThrowsExceptionWhenConstraintIsNotFound()
    {
        $t = new \Ruga\Db\Test\Model\UserTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\User $row */
        $row = $t->findById(3)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\User::class, $row);
        $this->assertSame('3', "{$row->id}");
        $this->assertSame('admin', $row->fullname);
        
        $this->expectException(NoConstraintsException::class);
        $items = $row->findDependentRowset(MetaDefaultTable::class);
    }
    
    
    
    public function testThrowsExceptionWhenDependentTableHasNoMetadata()
    {
        $t = new \Ruga\Db\Test\Model\UserTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\User $row */
        $row = $t->findById(3)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\User::class, $row);
        $this->assertSame('3', "{$row->id}");
        $this->assertSame('admin', $row->fullname);
        
        $this->expectException(FeatureMissingException::class);
        $items = $row->findDependentRowset(SimpleTable::class);
    }
    
    
    
    public function testCanFindDependentRowsWithRuleKeyConstraintName()
    {
        $t = new \Ruga\Db\Test\Model\UserTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\User $row */
        $row = $t->findById(3)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\User::class, $row);
        $this->assertSame('3', "{$row->id}");
        $this->assertSame('admin', $row->fullname);
        
        
        $items = $row->findDependentRowset(CartTable::class, 'fk_Cart_createdBy');
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
    }
    
    
    
    public function testCanFindDependentRowsWithRuleKeyColumnName()
    {
        $t = new \Ruga\Db\Test\Model\UserTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\User $row */
        $row = $t->findById(3)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\User::class, $row);
        $this->assertSame('3', "{$row->id}");
        $this->assertSame('admin', $row->fullname);
        
        
        $items = $row->findDependentRowset(CartTable::class, 'createdBy');
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
    }
    
    
    
    public function testCanFindDependentRowsWithManualConstraint()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame('5', "{$row->id}");
        $this->assertSame('data 5', $row->data);
        
        $items = $row->findDependentRowset(MusterTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
    }
    
    
    
    public function testCanCreateNewDependentRow()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame('5', "{$row->id}");
        $this->assertSame('data 5', $row->data);
        
        
        $item = $row->createDependentRow(MusterTable::class, ['fullname' => 'Hallo Welt']);
        $item->save();
        
        $items = $row->findDependentRowset(MusterTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
    }
    
    
    
    public function testCanLinkDependentRow()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame('5', "{$row->id}");
        $this->assertSame('data 5', $row->data);
        
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'Hallo Welt']);
        
        $row->linkDependentRow($dependentRow);
        $dependentRow->save();
        
        $items = $row->findDependentRowset(MusterTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount(2, $items);
    }
    
    
    
    public function testCanUnlinkDependentRow()
    {
        $parentTable = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $parentRow */
        $parentRow = $parentTable->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $parentRow);
        $this->assertSame('5', "{$parentRow->id}");
        $this->assertSame('data 5', $parentRow->data);
        
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'Hallo Welt']);
        
        $parentRow->linkDependentRow($dependentRow);
        $dependentRow->save();
        unset($dependentRow);
        
        $items = $parentRow->findDependentRowset(MusterTable::class);
        $this->assertCount(2, $items);
        
        $dependentRow = $parentRow->findDependentRowset(
            MusterTable::class,
            null,
            (new Select())->where("`id`=1")
        )->current();
        $this->assertInstanceOf(Muster::class, $dependentRow);
        
        $parentRow->unlinkDependentRow($dependentRow);
        $dependentRow->save();
        
        $items = $parentRow->findDependentRowset(MusterTable::class);
        $this->assertCount(1, $items);
    }
    
    
    
    public function testCanUnlinkDependentRowUnsaved()
    {
        $parentTable = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $parentRow */
        $parentRow = $parentTable->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $parentRow);
        $this->assertSame('5', "{$parentRow->id}");
        $this->assertSame('data 5', $parentRow->data);
        
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'Hallo Welt']);
        
        $parentRow->linkDependentRow($dependentRow);
//        $dependentRow->save();
        unset($dependentRow);
        
        $items = $parentRow->findDependentRowset(MusterTable::class);
        $this->assertCount(2, $items);
        
        $dependentRow = $parentRow->findDependentRowset(
            MusterTable::class,
            null,
            (new Select())->where("`id`=1")
        )->current();
        $this->assertInstanceOf(Muster::class, $dependentRow);
        
        $parentRow->unlinkDependentRow($dependentRow);
//        $dependentRow->save();
        
        $items = $parentRow->findDependentRowset(MusterTable::class);
        $this->assertCount(1, $items);
    }
    
    
    
    public function testCanNotUnlinkDependentRow()
    {
        $t = new \Ruga\Db\Test\Model\CartTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Cart $row */
        $row = $t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Cart::class, $row);
        $this->assertSame('1', "{$row->id}");
        $this->assertSame('cart 1', $row->fullname);
        
        $dependentRow = $row->findDependentRowset(
            CartItemTable::class,
            null,
            (new Select())->where(function (Where $where) {
                $where->like('fullname', '%item 2');
            })
        )->current();
        $this->assertInstanceOf(CartItem::class, $dependentRow);
        
        $row->unlinkDependentRow($dependentRow);
        
        $this->expectException(InvalidQueryException::class);
        $dependentRow->save();
    }
    
    
    
    public function testCanCreateNewDependentRowAndSaveThruParent()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame('5', "{$row->id}");
        $this->assertSame('data 5', $row->data);
        
        
        $row->createDependentRow(MusterTable::class, ['fullname' => 'Hallo Welt']);
        $row->save();
        
        $items = $row->findDependentRowset(MusterTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount(2, $items);
    }
    
    
    
    public function testCanLinkDependentRowAndSaveThruParent()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame('5', "{$row->id}");
        $this->assertSame('data 5', $row->data);
        
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'Hallo Welt']);
        
        $row->linkDependentRow($dependentRow);
        $row->save();
        
        $items = $row->findDependentRowset(MusterTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount(2, $items);
    }
    
    
    
    public function testCanUnlinkDependentRowAndSaveThruParent()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame('5', "{$row->id}");
        $this->assertSame('data 5', $row->data);
        
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'Hallo Welt']);
        
        $row->linkDependentRow($dependentRow);
        $row->save();
        unset($dependentRow);
        
        $items = $row->findDependentRowset(MusterTable::class);
        $this->assertCount(2, $items);
        
        $dependentRow = $row->findDependentRowset(MusterTable::class, null, (new Select())->where("`id`=1"))->current();
        $this->assertInstanceOf(Muster::class, $dependentRow);
        
        $row->unlinkDependentRow($dependentRow);
        $row->save();
        
        $items = $row->findDependentRowset(MusterTable::class);
        $this->assertCount(1, $items);
    }
    
    
    
    public function testCanNotUnlinkDependentRowAndSaveThruParent()
    {
        $t = new \Ruga\Db\Test\Model\CartTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Cart $row */
        $row = $t->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Cart::class, $row);
        $this->assertSame('1', "{$row->id}");
        $this->assertSame('cart 1', $row->fullname);
        
        $dependentRow = $row->findDependentRowset(
            CartItemTable::class,
            null,
            (new Select())->where(function (Where $where) {
                $where->like('fullname', '%item 2');
            })
        )->current();
        $this->assertInstanceOf(CartItem::class, $dependentRow);
        
        $row->unlinkDependentRow($dependentRow);
        
        $this->expectException(InvalidQueryException::class);
        $row->save();
    }
    
    
    
    public function testCanDeleteDependentRowAndSaveThruParent()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame('5', "{$row->id}");
        $this->assertSame('data 5', $row->data);
        
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'Hallo Welt']);
        
        $row->linkDependentRow($dependentRow);
        $row->save();
        unset($dependentRow);
        
        $items = $row->findDependentRowset(MusterTable::class);
        $this->assertCount(2, $items);
        
        $dependentRow = $row->findDependentRowset(MusterTable::class, null, (new Select())->where("`id`=1"))->current();
        $this->assertInstanceOf(Muster::class, $dependentRow);
        
        $row->deleteDependentRow($dependentRow);
        $row->save();
        
        $items = $row->findDependentRowset(MusterTable::class);
        $this->assertCount(1, $items);
    }
    
    
    
    public function testCanEditAndSaveThruParent()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->findById(5)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame('5', "{$row->id}");
        $this->assertSame('data 5', $row->data);
        
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'Hallo Welt']);
        
        $row->linkDependentRow($dependentRow);
        $row->save();
        unset($dependentRow);
        
        $items = $row->findDependentRowset(MusterTable::class);
        $this->assertCount(2, $items);
        
        $dependentRow = $row->findDependentRowset(MusterTable::class, null, (new Select())->where("`id`=1"))->current();
        $this->assertInstanceOf(Muster::class, $dependentRow);
        
        
        $dependentRow->offsetSet('fullname', 'This text has changed');
        unset($dependentRow);
        $row->save();
        
        // re-read the row
        $dependentRow = $row->findDependentRowset(MusterTable::class, null, (new Select())->where("`id`=1"))->current();
        $this->assertInstanceOf(Muster::class, $dependentRow);
        $this->assertSame('This text has changed', $dependentRow->fullname);
    }
    
    
    
    public function testCanCreateNewDependentRowWithNewParent()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->createRow(['data' => 'data 8 (new)']);
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        
        $item = $row->createDependentRow(
            MusterTable::class,
            ['fullname' => 'Hallo Welt testCanCreateNewDependentRowWithNewParent']
        );
        $row->save();
        
        $items = $row->findDependentRowset(MusterTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount(1, $items);
    }
    
    
    
    public function testCanLinkNewDependentRowWithNewParent()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->createRow(['data' => 'data 8 (new)']);
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'Hallo Welt testCanLinkNewDependentRowWithNewParent']
        );
        
        $row->linkDependentRow($dependentRow);
        $row->save();
        
        $items = $row->findDependentRowset(MusterTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount(1, $items);
    }
    
    
    
    public function testCanLinkAndEditNewDependentRowWithNewParent()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->createRow(['data' => 'data 8 (new)']);
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'Hallo Welt testCanLinkNewDependentRowWithNewParent']
        );
        
        $row->linkDependentRow($dependentRow);
        $dependentRow->offsetSet('fullname', 'This value has been changed by testCanLinkNewDependentRowWithNewParent');
        $row->save();
        
        $items = $row->findDependentRowset(MusterTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount(1, $items);
    }
    
    
    
    public function testCanFindParentUnsaved()
    {
        $parentTable = new \Ruga\Db\Test\Model\CartTable($this->getAdapter());
        /** @var Cart $parentRow */
        $parentRow = $parentTable->findById(2)->current(2);
        
        $dependentTable = new \Ruga\Db\Test\Model\CartItemTable($this->getAdapter());
        /** @var CartItem $dependentRow */
        $dependentRow = $dependentTable->createRow(['fullname' => 'cart 2, item 7', 'seq' => 7]);
        
        $dependentRow->linkParentRow($parentRow);
//        $dependentRow->save();
        
        $items = $parentRow->findDependentRowset(CartItemTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo $item->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        $this->assertCount(7, $items);
        echo PHP_EOL;
        
        
        $dependentRow->save();
        $items = $parentRow->findDependentRowset(CartItemTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo $item->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        $this->assertCount(7, $items);
    }
    
    
    
    public function testCanFindParentEverythingUnsaved()
    {
        $parentTable = new \Ruga\Db\Test\Model\CartTable($this->getAdapter());
        /** @var Cart $parentRow */
        $parentRow = $parentTable->createRow(['fullname' => 'This is a new cart']);
//        $parentRow->save();
        
        $dependentTable = new \Ruga\Db\Test\Model\CartItemTable($this->getAdapter());
        for ($i = 1; $i < 5; $i++) {
            /** @var CartItem $dependentRow */
            $dependentRow = $dependentTable->createRow(['fullname' => "new cart 3, item {$i}", 'seq' => $i]);
            $dependentRow->linkParentRow($parentRow);
//            $dependentRow->save();
        }
        
        $items = $parentRow->findDependentRowset(CartItemTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo $item->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        $this->assertCount(4, $items);
        echo PHP_EOL;
        
        
        $parentRow->save();
        $items = $parentRow->findDependentRowset(CartItemTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo $item->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        $this->assertCount(4, $items);
    }
    
    
    
    /**
     * @dataProvider linkParamProvider
     *
     * @param $linkParamName
     * @param $linkParamValue
     * @param $count
     *
     * @return void
     * @throws \Exception
     */
    public function testCanCreateRowWithLinkParam($linkParamName, $linkParamValue, $count)
    {
        $dependentTable = new \Ruga\Db\Test\Model\MusterTable($this->getAdapter());
        $dependentRow = $dependentTable->createRow(['fullname' => 'This is a new row']);
        $dependentRow->save();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Muster::class, $dependentRow);
        
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->createRow([
                                 'data' => 'This is a new row',
                                 $linkParamName => $linkParamValue,
                                 MusterTable::class => [
                                     'fullname' => 'Created by \Ruga\Db\Row\Feature\ParentFeature::createDependentRow()',
                                 ],
                             ]);
        $row->save();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        $this->assertSame('8', "{$row->id}");
        $this->assertSame('This is a new row', $row->data);
        
        $items = $row->findDependentRowset(MusterTable::class);
        /** @var RowInterface $item */
        foreach ($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount($count, $items);
    }
    
    
    
    public function linkParamProvider(): array
    {
        return [
            '(FQCN)' => ['linkDependentRow(\Ruga\Db\Test\Model\MusterTable)', 2, 1],
            '(Alias)' => ['linkDependentRow(MusterTable)', 2, 1],
            '()=uniqueid' => ['linkDependentRow()', '2@MusterTable', 1],
            '(Alias)=[uniqueid]' => ['linkDependentRow(MusterTable)', ['2@MusterTable', '1@MusterTable'], 2],
            '(FQCN)=[id]' => ['linkDependentRow(\Ruga\Db\Test\Model\MusterTable)', [1, 2], 2],
            '(FQCN:fullname)' => ['linkDependentRow(\Ruga\Db\Test\Model\MusterTable:fullname)', 'This is a new row', 1],
            '(FQCN:fullname)=[]' => ['linkDependentRow(\Ruga\Db\Test\Model\MusterTable:fullname)', ['This is a new row', 'Linked to table Simple'], 2,],
            '(FQCN)=new' => ['linkDependentRow(\Ruga\Db\Test\Model\MusterTable)', 'new', 1],
        ];
    }
    
    
}
