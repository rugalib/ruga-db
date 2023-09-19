<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class AbstractTableTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanCreateTable(): void
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        $this->assertInstanceOf(\Ruga\Db\Test\Model\AlltypesTable::class, $t);
    }
    
    
    
    public function testCanFetchRow(): void
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        $rowset = $t->findById(1);
        $this->assertCount(1, $rowset);
        $row = $rowset->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Alltypes::class, $row);
    }
    
    
    
    public function testCanCreateRow(): void
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Alltypes::class, $row);
    }
    
    
    
    public function testCanCreateRowAndSave(): void
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Alltypes::class, $row);
        
        $row->tinyint_nn = 5;
        $row->smallint_nn = 6;
        $row->mediumint_nn = 55;
        $row->int_nn = 555;
        $row->bigint_nn = 5555;
        $row->bit_nn = 1;
        $row->float_nn = 3.1415;
        $row->double_nn = 3.1415;
        $row->decimal_nn = 3.1415;
        $row->char_nn = 'a';
        $row->varchar_nn = 'abcd';
        $row->tinytext_nn = 'abcdefghijklmnopqrstuvwxyz';
        $row->text_nn = 'abcdefghijklmnopqrstuvwxyz';
        $row->mediumtext_nn = 'abcdefghijklmnopqrstuvwxyz';
        $row->longtext_nn = 'abcdefghijklmnopqrstuvwxyz';
        $row->json_nn = json_encode(['hello' => 'world']);
        $row->binary_nn = 'z';
        $row->varbinary_nn = 'z';
        $row->tinyblob_nn = 'abcdefghijklmnopqrstuvwxyz';
        $row->blob_nn = 'abcdefghijklmnopqrstuvwxyz';
        $row->mediumblob_nn = 'abcdefghijklmnopqrstuvwxyz';
        $row->longblob_nn = 'abcdefghijklmnopqrstuvwxyz';
        $row->date_nn = new \DateTimeImmutable("2022-05-09 16:41");
        $row->time_nn = new \DateTimeImmutable("2022-05-09 16:44");
        $row->datetime_nn = new \DateTimeImmutable("2022-05-09 16:44");
        $row->timestamp_nn = new \DateTimeImmutable("2022-05-09 16:44");
        $row->set_nn = ['OTHER', 'PROBLEM'];
        
        $row->save();
    }
    
    
    
    public function testCanFindRowByUniqueid(): void
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        $rowset = $t->findById("1@AlltypesTable");
        $this->assertCount(1, $rowset);
        $row = $rowset->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Alltypes::class, $row);
        
        echo $row->uniqueid;
    }
    
    
    
    public function testCanCreateRowWithFactory(): void
    {
        GlobalAdapterFeature::setStaticAdapter($this->getAdapter());
        
        $row = \Ruga\Db\Test\Model\MetaTable::factory(6);
        echo "{$row->data}" . PHP_EOL;
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Meta::class, $row);
        
        $row = \Ruga\Db\Test\Model\MetaTable::factory('2@MemberTable');
        echo "{$row->fullname}" . PHP_EOL;
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Member::class, $row);
    }
    
    
    
    public function testCannotCreateNonexistentTable(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Table "Nonexistent" does not exist');
        $t = new \Ruga\Db\Test\Model\MetaNonexistentTable($this->getAdapter());
    }
    
    
    
    public function testCanReadSchemaNameFromTable(): void
    {
        $t = new \Ruga\Db\Test\Model\MetaTable($this->getAdapter());
        $this->assertSame($this->getConfig()['db']['database'], $t->getMetadata()['schema']);
    }
    
    
    
    public function testCanFindItemById(): void
    {
        $t = new \Ruga\Db\Test\Model\FullnameTable($this->getAdapter());
        
        $result = $t->findById(2);
        
        foreach ($result as $row) {
            echo $row->fullname . PHP_EOL;
            $this->assertInstanceOf(\Ruga\Db\Test\Model\Fullname::class, $row);
        }
    }
    
    
    
    public function testCanFindItemByUniqueid()
    {
        $t = new \Ruga\Db\Test\Model\MemberTable($this->getAdapter());
        
        $result = $t->findById('2@MemberTable');
        
        foreach ($result as $row) {
            echo $row->fullname . PHP_EOL;
            $this->assertInstanceOf(\Ruga\Db\Test\Model\Member::class, $row);
        }
    }
    
    
    
    public function testCanFindItemByRow()
    {
        $t = new \Ruga\Db\Test\Model\MemberTable($this->getAdapter());
        
        $searchRow = $t->findById('2@MemberTable')->current();
        
        $result = $t->findById($searchRow);
        foreach ($result as $row) {
            echo $row->fullname . PHP_EOL;
            $this->assertInstanceOf(\Ruga\Db\Test\Model\Member::class, $row);
        }
    }
    
    
    
    public function testCanFindItemsById()
    {
        $t = new \Ruga\Db\Test\Model\FullnameTable($this->getAdapter());
        
        $result = $t->findById([1, 3, 5, 7]);
        
        foreach ($result as $row) {
            echo $row->fullname . PHP_EOL;
            $this->assertInstanceOf(\Ruga\Db\Test\Model\Fullname::class, $row);
        }
    }
    
    
    
    public function testCanFindItemsByUniqueid()
    {
        $t = new \Ruga\Db\Test\Model\MemberTable($this->getAdapter());
        
        $result = $t->findById(['2@MemberTable', '1@MemberTable', '3@Hallo']);
        
        foreach ($result as $row) {
            echo $row->fullname . PHP_EOL;
            $this->assertInstanceOf(\Ruga\Db\Test\Model\Member::class, $row);
        }
    }
    
    
    
    public function findItems()
    {
        $t = new \Ruga\Db\Test\Model\FullnameTable($this->getAdapter());
        
        $ids = [1, 3, 5, 7];
        return $t->findById($ids);
    }
    
    
    
    public function testFullnameFeatureReturnsWrongData()
    {
        $item_uniqueids1 = array_map(
            function (\Ruga\Db\Test\Model\Fullname $item) {
                return "{$item->id}@{$item->type}";
            },
            iterator_to_array($this->findItems())
        );
        
        $item_uniqueids2 = array_map(
            function (\Ruga\Db\Test\Model\Fullname $item) {
                return "{$item->uniqueid}";
            },
            iterator_to_array($this->findItems())
        );
        
        $a = $this->findItems();
        $b = iterator_to_array($this->findItems());
        $c = $this->findItems()->toArray();
        
        $d = [];
        $items = $this->findItems();
        while ($item = $items->current()) {
            $d[] = $item->uniqueid;
            $items->next();
        }
        
        
        print_r($item_uniqueids1);
        print_r($item_uniqueids2);
        
        
        foreach ($item_uniqueids1 as $key => $item) {
            $this->assertSame($item, $item_uniqueids2[$key]);
        }
    }
    
    
    
    public function testIteratorToArray()
    {
        $b = iterator_to_array($this->findItems());
        echo $b[0]->uniqueid;
        $this->assertSame('1@FullnameTable', $b[0]->uniqueid);
    }
    
    
    
    public function testCustomSqlSelectWithJoin()
    {
        $table = new \Ruga\Db\Test\Model\SimpleTenantTable($this->getAdapter());
        /** @var \Laminas\Db\Sql\Select $select */
        $select = $table->getSql()->select();
        $select->join(['m' => \Ruga\Db\Test\Model\MusterTable::TABLENAME], "m.Simple_id={$table->getTable()}.id");
        
        echo PHP_EOL;
        echo $select->getSqlString($this->getAdapter()->getPlatform());
        echo PHP_EOL;
        
        $rowset = $table->selectWith($select);
        
        $this->assertInstanceOf(\Ruga\Db\ResultSet\ResultSet::class, $rowset);
    }
    
}
