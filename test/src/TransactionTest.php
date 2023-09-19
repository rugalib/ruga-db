<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Ruga\Db\Test\Model\CartTable;
use Ruga\Db\Test\Model\MusterTable;
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
        $mRow->name = 'This row has been changed';
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
            if (!$e instanceof InvalidQueryException) {
                throw $e;
            }
            \Ruga\Log::addLog($e);
        }
        
        $mTable = new \Ruga\Db\Test\Model\OrganizationTable($this->getAdapter());
        $mRowset = $mTable->select();
        $this->assertCount(1, $mRowset);
    }
    
    
    
    public function testCanCreateNewDependentRowWithNewParent()
    {
        $t = new \Ruga\Db\Test\Model\MetaDefaultTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\MetaDefault $row */
        $row = $t->createRow(['data' => 'data 8 (new)']);
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaDefault::class, $row);
        
        $row->createDependentRow(
            MusterTable::class,
            ['fullname' => 'Hallo Welt testCanCreateNewDependentRowWithNewParent 1']
        );
        $row->createDependentRow(
            MusterTable::class,
            ['fullname' => 'Hallo Welt testCanCreateNewDependentRowWithNewParent 2', 'Tenant_id' => 'A']
        );
        $row->createDependentRow(
            MusterTable::class,
            ['fullname' => 'Hallo Welt testCanCreateNewDependentRowWithNewParent 3']
        );
        
        try {
            $row->save();
            $id = $row->id;
        } catch (\Throwable $e) {
            if (!$e instanceof InvalidQueryException) {
                throw $e;
            }
            \Ruga\Log::addLog($e);
        }
        
        // Because of transaction roll back, the parent row should not be found
        $items = $t->select(['data' => 'data 8 (new)']);
        $this->assertCount(0, $items);
    }
    
    
    
    public function testCanCreateNewParentRow()
    {
        $dependentTable = new \Ruga\Db\Test\Model\CartItemTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\CartItem $dependentRow */
        $dependentRow = $dependentTable->createRow(['fullname' => 'cart 3, item 1', 'seq' => null]);
        
        $parentRow = $dependentRow->createParentRow(CartTable::class, ['fullname' => 'cart 3']);
        
        try {
            $parentRow->save();
        } catch (\Throwable $e) {
            if (!$e instanceof InvalidQueryException) {
                throw $e;
            }
            \Ruga\Log::addLog($e);
        }
        
        $parentTable = new \Ruga\Db\Test\Model\CartTable($this->getAdapter());
        $items = $parentTable->select(['fullname' => 'cart 3']);
        $this->assertCount(0, $items);
    }
    
}
