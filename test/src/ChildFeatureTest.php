<?php

declare(strict_types=1);

namespace Ruga\Db\Test;


use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Ruga\Db\Row\Exception\FeatureMissingException;
use Ruga\Db\Row\Exception\NoConstraintsException;
use Ruga\Db\Row\Exception\TooManyConstraintsException;
use Ruga\Db\Row\RowInterface;
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
    
    
}
