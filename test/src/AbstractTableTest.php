<?php

declare(strict_types=1);

namespace Ruga\Db\Test;

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
    
    
    
    public function testCanCreateRow()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Alltypes::class, $row);
    }
    
    
    
    public function testCanCreateRowAndSave()
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
    
    
    
    public function testCanFindRowByUniqueid()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        $rowset = $t->findById("1@AlltypesTable");
        $this->assertCount(1, $rowset);
        $row = $rowset->current();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Alltypes::class, $row);
        
        echo $row->uniqueid;
    }
    
}
