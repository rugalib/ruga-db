<?php

declare(strict_types=1);

namespace Ruga\Db\Test;


use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Test\Model\CartItemTable;

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
        
        
        
        $items=$row->findDependentRowset(CartItemTable::class);
        /** @var RowInterface $item */
        foreach($items as $item) {
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
        
        $items=$row->findDependentRowset(CartItemTable::class, null, (new Select())->where(function(Where $where){
            $where->like('fullname', '%item 2');
            $where->or->like('fullname', '%item 3');
        }));
        /** @var RowInterface $item */
        foreach($items as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        
        $this->assertCount(2, $items);
    }
    
}
