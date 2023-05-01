<?php

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Ruga\Db\Test\Model\Organization;
use Ruga\Db\Test\Model\OrganizationTable;
use Ruga\Db\Test\Model\PartyHasOrganizationTable;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class TransactionTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanFindManyToManyRowAndEditAndSaveThruNRow()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(4)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        /** @var Organization $mRow */
        $mRow = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class)->current();
        $mRow->name='This row has been changed';
        $nRow->save();
        
        $mRow = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class)->current();
        $this->assertSame('This row has been changed', $mRow->name);
    }
    
    
    public function testCanNotCreateNewManyToManyRow()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        $mRow = $nRow->createManyToManyRow(
            OrganizationTable::class,
            PartyHasOrganizationTable::class,
            ['name' => 'Kaufmann'],
            ['organization_role' => 'THIS_DOES_NOT_EXIST']
        );
        
        try {
            $nRow->save();
        } catch (\Throwable $e) {
            if(!$e instanceof InvalidQueryException) {
                throw $e;
            }
            \Ruga\Log::addLog($e);
        }
        
        $mTable = new \Ruga\Db\Test\Model\OrganizationTable($this->getAdapter());
        $mRowset = $mTable->select();
        $this->assertCount(1, $mRowset);
    }
    
    
    
    
}
