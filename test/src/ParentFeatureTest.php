<?php

declare(strict_types=1);

namespace Ruga\Db\Test;


use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Ruga\Db\Row\Exception\FeatureMissingException;
use Ruga\Db\Row\Exception\NoConstraintsException;
use Ruga\Db\Row\Exception\TooManyConstraintsException;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Test\Model\CartItemTable;
use Ruga\Db\Test\Model\CartTable;
use Ruga\Db\Test\Model\MetaDefaultTable;
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
    
}
