<?php

declare(strict_types=1);

namespace Ruga\Db\Test;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class AbstractRowTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanReadAllDatatypes()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Alltypes $row */
        $row = $t->findById(1)->current();
        $this->assertIsInt($row->tinyint_nn);
        $this->assertIsInt($row->smallint_nn);
        $this->assertIsInt($row->mediumint_nn);
        $this->assertIsInt($row->int_nn);
        $this->assertIsInt($row->int_nnd);
        $this->assertIsInt($row->int_n);
        $this->assertIsInt($row->int_nd);
        $this->assertIsInt($row->bigint_nn);
        $this->assertIsBool($row->bit_nn);
        $this->assertIsFloat($row->float_nn);
        $this->assertIsFloat($row->double_nn);
        $this->assertIsFloat($row->decimal_nn);
        $this->assertIsString($row->char_nn);
        $this->assertIsString($row->varchar_nn);
        $this->assertIsString($row->tinytext_nn);
        $this->assertIsString($row->text_nn);
        $this->assertIsString($row->mediumtext_nn);
        $this->assertIsString($row->longtext_nn);
        $this->assertIsArray($row->json_nn);
        $this->assertIsString($row->binary_nn);
        $this->assertIsString($row->varbinary_nn);
        $this->assertIsString($row->tinyblob_nn);
        $this->assertIsString($row->blob_nn);
        $this->assertIsString($row->mediumblob_nn);
        $this->assertIsString($row->longblob_nn);
        $this->assertInstanceOf(\DateTimeImmutable::class, $row->date_nn);
        $this->assertInstanceOf(\DateTimeImmutable::class, $row->time_nn);
        $this->assertInstanceOf(\DateTimeImmutable::class, $row->datetime_nn);
        $this->assertInstanceOf(\DateTimeImmutable::class, $row->timestamp_nn);
        $this->assertIsString($row->enum_nn);
        $this->assertIsString($row->enum_nnd);
        $this->assertIsString($row->enum_n);
        $this->assertIsString($row->enum_nd);
        $this->assertIsArray($row->set_nn);
        $this->assertIsArray($row->set_nnd);
        $this->assertIsArray($row->set_n);
        $this->assertIsArray($row->set_nd);
        $this->assertInstanceOf(\DateTimeImmutable::class, $row->created);
        $this->assertIsInt($row->createdBy);
        $this->assertInstanceOf(\DateTimeImmutable::class, $row->changed);
        $this->assertIsInt($row->changedBy);
        
        $this->assertSame(intval(bcpow("-2", "63")), $row->bigint_nn);
    }
    
    
}
