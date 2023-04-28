<?php

declare(strict_types=1);

namespace Ruga\Db\Test;


use Ruga\Db\Row\Exception\InvalidForeignKeyException;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Test\Model\CartTable;
use Ruga\Db\Test\Model\OrganizationTable;
use Ruga\Db\Test\Model\PartyHasOrganizationTable;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class ManyToManyFeatureTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanFindParentRow()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(4)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        $mRowset = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class);
        
        /** @var RowInterface $item */
        foreach ($mRowset as $item) {
            print_r($item->idname);
            echo PHP_EOL;
        }
        $this->assertCount(1, $mRowset);
    }
    
    
    
    public function testCanCreateNewManyToManyRow()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        $mRow = $nRow->createManyToManyRow(
            OrganizationTable::class,
            PartyHasOrganizationTable::class,
            ['name' => 'Kaufmann']
        );
        
        $nRow->save();
    }
    
    
    
    public function testCanLinkManyToManyRow()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        $mTable = new \Ruga\Db\Test\Model\OrganizationTable($this->getAdapter());
        $mRow = $mTable->createRow(['name' => 'Kaufmann']);
        
        $nRow->linkManyToManyRow($mRow, PartyHasOrganizationTable::class);
        $nRow->save();
    }
    
    
}
