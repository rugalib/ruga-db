<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Test\Model\Organization;
use Ruga\Db\Test\Model\OrganizationTable;
use Ruga\Db\Test\Model\PartyHasOrganization;
use Ruga\Db\Test\Model\PartyHasOrganizationTable;
use Ruga\Db\Test\Model\PartyTable;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class ManyToManyFeatureTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanFindManyToManyRow()
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
    
    
    
    public function testCanFindIntersectionRow()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(4)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        /** @var Organization $mRow */
        $mRow = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class)->current();
        $this->assertInstanceOf(Organization::class, $mRow);
        
        /** @var PartyHasOrganization $iRow */
        $iRow = $nRow->findIntersectionRows($mRow, PartyHasOrganizationTable::class)->current();
        print_r($iRow->idname);
        echo PHP_EOL;
        $this->assertInstanceOf(PartyHasOrganization::class, $iRow);
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
        
        $mRow = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class)->current();
        $this->assertSame('Kaufmann', $mRow->name);
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
    
    
    
    public function testCanNotUnlinkManyToManyRow()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(4)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        /** @var Organization $mRow */
        $mRow = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class)->current();
        $this->assertInstanceOf(Organization::class, $mRow);
        
        $nRow->unlinkManyToManyRow($mRow, PartyHasOrganizationTable::class);
        $this->expectException(InvalidQueryException::class);
        $nRow->save();
    }
    
    
    
    public function testCanDeleteManyToManyRow()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(4)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        /** @var Organization $mRow */
        $mRow = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class)->current();
        $this->assertInstanceOf(Organization::class, $mRow);
        
        $nRow->deleteManyToManyRow($mRow, PartyHasOrganizationTable::class);
        $nRow->save();
        
        $mRowset = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class);
        $this->assertCount(0, $mRowset);
    }
    
    
    
    public function testCanDeleteManyToManyRowUnsaved()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(4)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        /** @var Organization $mRow */
        $mRow = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class)->current();
        $this->assertInstanceOf(Organization::class, $mRow);
        
        $nRow->deleteManyToManyRow($mRow, PartyHasOrganizationTable::class);
//        $nRow->save();
        
        $mRowset = $nRow->findManyToManyRowset(OrganizationTable::class, PartyHasOrganizationTable::class);
        $this->assertCount(0, $mRowset);
    }
    
    
    
    public function testCanFindMRowBeforeSaving()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        $mTable = new \Ruga\Db\Test\Model\OrganizationTable($this->getAdapter());
        $mRow = $mTable->createRow(['name' => 'Kaufmann']);
        
        $nRow->linkManyToManyRow($mRow, PartyHasOrganizationTable::class);
        
        $mRows = $nRow->findManyToManyRowset($mTable, PartyHasOrganizationTable::class);
        
        /** @var Organization $mRow */
        foreach ($mRows as $mRow) {
            echo "{$mRow->idname} {$mRow->name}";
            echo $mRow->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        
        $this->assertCount(1, $mRows);
        //$nRow->save();
    }
    
    
    
    public function testCanFindMRowWhenNothingSaved()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->createRow(['fullname' => 'This is a new Party']);
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        $mTable = new \Ruga\Db\Test\Model\OrganizationTable($this->getAdapter());
        $mRow = $mTable->createRow(['name' => 'This is a new Organization']);
        
        $nRow->linkManyToManyRow($mRow, PartyHasOrganizationTable::class);
        
        $mRows = $nRow->findManyToManyRowset($mTable, PartyHasOrganizationTable::class);
        
        /** @var Organization $mRow */
        foreach ($mRows as $mRow) {
            echo "{$mRow->idname} {$mRow->name}";
            echo $mRow->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        
        $this->assertCount(1, $mRows);
        //$nRow->save();
    }
    
    
    
    public function testCanFindIRowBeforeSaving()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        $mTable = new \Ruga\Db\Test\Model\OrganizationTable($this->getAdapter());
        $mRow = $mTable->createRow(['name' => 'Kaufmann']);
        
        $nRow->linkManyToManyRow($mRow, PartyHasOrganizationTable::class);
        
        $iRows = $nRow->findIntersectionRows($mRow, PartyHasOrganizationTable::class);
        /** @var PartyHasOrganization $iRow */
        foreach ($iRows as $iRow) {
            echo "\$iRow->Party_id={$iRow->Party_id}";
            echo $iRow->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        $this->assertCount(1, $iRows);
        
        
        $nRow->save();
        $iRows = $nRow->findIntersectionRows($mRow, PartyHasOrganizationTable::class);
        /** @var PartyHasOrganization $iRow */
        foreach ($iRows as $iRow) {
            echo "\$iRow->Party_id={$iRow->Party_id}";
            echo $iRow->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        $this->assertCount(1, $iRows);
    }
    
    
    
    public function testCanFindIRowWhenNothingSaved()
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->createRow(['fullname' => 'This is a new Party']);
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        $mTable = new \Ruga\Db\Test\Model\OrganizationTable($this->getAdapter());
        $mRow = $mTable->createRow(['name' => 'This is a new Organization']);
        
        $nRow->linkManyToManyRow($mRow, PartyHasOrganizationTable::class, ['organization_role' => 'PARTNER']);
        
        $iRows = $nRow->findIntersectionRows($mRow, PartyHasOrganizationTable::class);
        /** @var PartyHasOrganization $iRow */
        foreach ($iRows as $iRow) {
            echo "\$iRow->organization_role=" . implode(',', $iRow->organization_role);
            echo $iRow->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        
        $this->assertCount(1, $iRows);
        
        
        $nRow->save();
        $iRows = $nRow->findIntersectionRows($mRow, PartyHasOrganizationTable::class);
        /** @var PartyHasOrganization $iRow */
        foreach ($iRows as $iRow) {
            echo "\$iRow->organization_role=" . implode(',', $iRow->organization_role);
            echo $iRow->isNew() ? " (NEW)" : "";
            echo PHP_EOL;
        }
        
        $this->assertCount(1, $iRows);
    }
    
    
    
    /**
     * @dataProvider linkParamProvider
     * @return void
     * @throws \Exception
     */
    public function testCanLinkManyToManyRowWithLinkParam($linkParamName, $linkParamValue, $count)
    {
        $nTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Party $nRow */
        $nRow = $nTable->findById(1)->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Party::class, $nRow);
        
        $mTable = new \Ruga\Db\Test\Model\OrganizationTable($this->getAdapter());
        $mRow = $mTable->createRow(
            [
                'name' => 'This is a new organization',
                $linkParamName => $linkParamValue,
                PartyTable::class => [
                    'fullname' => 'This is a new supplier',
                    'party_role' => 'SUPPLIER',
                ],
                PartyHasOrganizationTable::class => [
                    'remark' => 'new created link row',
                ]
            ]
        );
        
        $mRow->save();
        
        $rowset = $mRow->findManyToManyRowset($nTable, PartyHasOrganizationTable::class);
        $this->assertCount($count, $rowset);
    }
    
    
    
    public function linkParamProvider(): array
    {
        return [
            '(FQCN,FQCN)' => [
                'linkManyToManyRow(\Ruga\Db\Test\Model\PartyTable, \Ruga\Db\Test\Model\PartyHasOrganizationTable)',
                1,
                1,
            ],
            '(Alias,Alias)' => ['linkManyToManyRow(PartyTable, PartyHasOrganizationTable)', 1, 1],
            '(uniqueid,Alias)' => ['linkManyToManyRow(1@PartyTable, PartyHasOrganizationTable)', 17, 1],
            '(Alias,Alias)=uniqueid' => ['linkManyToManyRow(PartyTable, PartyHasOrganizationTable)', '1@PartyTable', 1],
            '(,Alias)=uniqueid' => ['linkManyToManyRow(, PartyHasOrganizationTable)', '1@PartyTable', 1],
            
            '(FQCN:fullname,Alias)' => [
                'linkManyToManyRow(\Ruga\Db\Test\Model\PartyTable:fullname, PartyHasOrganizationTable)',
                'Kaufmann AG',
                1,
            ],
            '(Alias:fullname,Alias)' => [
                'linkManyToManyRow(PartyTable:fullname, PartyHasOrganizationTable)',
                'Kaufmann AG',
                1,
            ],
            '(FQCN:fullname=,Alias)' => [
                'linkManyToManyRow(\Ruga\Db\Test\Model\PartyTable:fullname=Kaufmann AG, PartyHasOrganizationTable)',
                17,
                1,
            ],
            '(Alias:fullname=,Alias)' => [
                'linkManyToManyRow(PartyTable:fullname=Kaufmann AG, PartyHasOrganizationTable)',
                17,
                1,
            ],
            
            '(FQCN,FQCN)=[]' => [
                'linkManyToManyRow(\Ruga\Db\Test\Model\PartyTable, \Ruga\Db\Test\Model\PartyHasOrganizationTable)',
                [1, 2],
                2,
            ],
            '(Alias,Alias)=[]' => [
                'linkManyToManyRow(\Ruga\Db\Test\Model\PartyTable, \Ruga\Db\Test\Model\PartyHasOrganizationTable)',
                [1, 2],
                2,
            ],
            '(Alias,Alias)=[uniqueid]' => [
                'linkManyToManyRow(PartyTable, PartyHasOrganizationTable)',
                ['1@PartyTable', '2@PartyTable'],
                2,
            ],
            
            '(FQCN,FQCN)=new' => [
                'linkManyToManyRow(\Ruga\Db\Test\Model\PartyTable, \Ruga\Db\Test\Model\PartyHasOrganizationTable)',
                'new',
                1,
            ],
            '(Alias,Alias)=new' => ['linkManyToManyRow(PartyTable, PartyHasOrganizationTable)', 'new', 1],
        ];
    }
    
    
}
