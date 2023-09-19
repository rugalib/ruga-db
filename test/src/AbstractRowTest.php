<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

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
    
    
    
    public function testCannotWriteReadonlyAttributeWithSet(): void
    {
        $t = new \Ruga\Db\Test\Model\MemberRugaTable($this->getAdapter());
        $row = $t->createRow();
        $this->expectException(\Ruga\Db\Row\Exception\ReadonlyArgumentException::class);
        $row->fullname = 'Hallo Welt';
    }
    
    
    
    public function testCanReadAndWriteColumnWithDefaultValue()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Alltypes $row */
        $row = $t->createRow();
        
        // ****************************************
        // Für eine nicht-gespeicherte, leere Spalte mit Default-Wert ...
        
        // ... soll offsetExists() true zurückgeben
        $this->assertSame(true, $row->offsetExists('int_nnd'));
        
        // ... soll isset() true zurückgeben
        $this->assertSame(true, isset($row->int_nnd));
        
        // ... soll offsetGet() den Default-Wert zurückgeben
        $this->assertSame(6, $row->offsetGet('int_nnd'));
        
        // ... soll __get() den Default-Wert zurückgeben
        $this->assertSame(6, $row->int_nnd);
        
        // ... soll offsetSet() den Wert setzen
        $row->offsetSet('int_nnd', 61);
        $this->assertSame(61, $row->int_nnd);
        
        // ... soll __set() den Wert setzen
        $row->int_nnd = 62;
        $this->assertSame(62, $row->int_nnd);
        
        // ... soll unset() auf den Default-Wert zurücksetzen
        unset($row->int_nnd);
        $this->assertSame(6, $row->int_nnd);
        
        
        // ****************************************
        // Für eine nicht-gespeicherte, bearbeitete Spalte mit Default-Wert ...
        $row->int_nnd = 5000;
        
        // ... soll offsetExists() true zurückgeben
        $this->assertSame(true, $row->offsetExists('int_nnd'));
        
        // ... soll isset() true zurückgeben
        $this->assertSame(true, isset($row->int_nnd));
        
        // ... soll offsetGet() den gesetzten Wert zurückgeben
        $this->assertSame(5000, $row->offsetGet('int_nnd'));
        
        // ... soll __get() den gesetzten Wert zurückgeben
        $this->assertSame(5000, $row->int_nnd);
        
        // ... soll offsetSet() den Wert setzen
        $row->offsetSet('int_nnd', 5001);
        $this->assertSame(5001, $row->int_nnd);
        
        // ... soll __set() den Wert setzen
        $row->int_nnd = 5002;
        $this->assertSame(5002, $row->int_nnd);
        
        // ... soll unset() auf den Default-Wert zurücksetzen
        unset($row->int_nnd);
        $this->assertSame(6, $row->int_nnd);
        
        
        // ****************************************
        // Für eine gespeicherte, bearbeitete Spalte mit Default-Wert ...
        $row = $t->findById(2)->current();
        
        // ... soll offsetExists() true zurückgeben
        $this->assertSame(true, $row->offsetExists('int_nnd'));
        
        // ... soll isset() true zurückgeben
        $this->assertSame(true, isset($row->int_nnd));
        
        // ... soll offsetGet() den gesetzten Wert zurückgeben
        $this->assertSame(2147483647, $row->offsetGet('int_nnd'));
        
        // ... soll __get() den gesetzten Wert zurückgeben
        $this->assertSame(2147483647, $row->int_nnd);
        
        // ... soll offsetSet() den Wert setzen
        $row->offsetSet('int_nnd', 5003);
        $this->assertSame(5003, $row->int_nnd);
        
        // ... soll __set() den Wert setzen
        $row->int_nnd = 5004;
        $this->assertSame(5004, $row->int_nnd);
        
        // ... soll unset() auf den Default-Wert zurücksetzen
        unset($row->int_nnd);
        $this->assertSame(6, $row->int_nnd);
    }
    
    
    
    public function testCanReadAndWriteColumnWithoutDefaultValue()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Alltypes $row */
        $row = $t->createRow();
        
        // ****************************************
        // Für eine nicht-gespeicherte, leere Spalte ohne Default-Wert ...
        
        // ... soll offsetExists() true zurückgeben
        $this->assertSame(true, $row->offsetExists('int_nn'));
        
        // ... soll isset() false zurückgeben
        $this->assertSame(false, isset($row->int_nn));
        
        // ... soll offsetGet() eine Exception werfen
        try {
            $i1 = $row->offsetGet('int_nn');
        } catch (\Ruga\Db\Row\Exception\NoDefaultValueException $e) {
            $i1 = null;
        }
        $this->assertSame(null, $i1);
        
        // ... soll __get() eine Exception werfen
        try {
            $i2 = $row->int_nn;
        } catch (\Ruga\Db\Row\Exception\NoDefaultValueException $e) {
            $i2 = null;
        }
        $this->assertSame(null, $i2);
        
        // ... soll offsetSet() den Wert setzen
        $row->offsetSet('int_nn', 71);
        $this->assertSame(71, $row->int_nn);
        
        // ... soll __set() den Wert setzen
        $row->int_nn = 72;
        $this->assertSame(72, $row->int_nn);
        
        // ... soll unset() den Wert und den Offset löschen
        unset($row->int_nn);
        try {
            $i3 = $row->offsetGet('int_nn');
        } catch (\Ruga\Db\Row\Exception\NoDefaultValueException $e) {
            $i3 = null;
        }
        $this->assertSame(null, $i3);
        
        
        // ****************************************
        // Für eine nicht-gespeicherte, bearbeitete Spalte ohne Default-Wert ...
        $row->int_nn = 5000;
        
        // ... soll offsetExists() true zurückgeben
        $this->assertSame(true, $row->offsetExists('int_nn'));
        
        // ... soll isset() true zurückgeben
        $this->assertSame(true, isset($row->int_nn));
        
        // ... soll offsetGet() den gesetzten Wert zurückgeben
        $this->assertSame(5000, $row->offsetGet('int_nn'));
        
        // ... soll __get() den gesetzten Wert zurückgeben
        $this->assertSame(5000, $row->int_nn);
        
        // ... soll offsetSet() den Wert setzen
        $row->offsetSet('int_nn', 5001);
        $this->assertSame(5001, $row->int_nn);
        
        // ... soll __set() den Wert setzen
        $row->int_nn = 5002;
        $this->assertSame(5002, $row->int_nn);
        
        // ... soll unset() den Wert und den Offset löschen
        unset($row->int_nn);
        try {
            $i3 = $row->offsetGet('int_nn');
        } catch (\Ruga\Db\Row\Exception\NoDefaultValueException $e) {
            $i3 = null;
        }
        $this->assertSame(null, $i3);
        
        
        // ****************************************
        // Für eine gespeicherte, bearbeitete Spalte ohne Default-Wert ...
        $row = $t->findById(2)->current();
        
        // ... soll offsetExists() true zurückgeben
        $this->assertSame(true, $row->offsetExists('int_nn'));
        
        // ... soll isset() true zurückgeben
        $this->assertSame(true, isset($row->int_nn));
        
        // ... soll offsetGet() den gesetzten Wert zurückgeben
        $this->assertSame(2147483647, $row->offsetGet('int_nn'));
        
        // ... soll __get() den gesetzten Wert zurückgeben
        $this->assertSame(2147483647, $row->int_nn);
        
        // ... soll offsetSet() den Wert setzen
        $row->offsetSet('int_nn', 5003);
        $this->assertSame(5003, $row->int_nn);
        
        // ... soll __set() den Wert setzen
        $row->int_nn = 5004;
        $this->assertSame(5004, $row->int_nn);
        
        // ... soll unset() auf den Default-Wert zurücksetzen
        $row->offsetUnset('int_nn');
        try {
            $i3 = $row->offsetGet('int_nn');
        } catch (\Ruga\Db\Row\Exception\NoDefaultValueException $e) {
            $i3 = null;
        }
        $this->assertSame(null, $i3);
    }
    
    
    
    public function testCannotReadAndWriteNonexistentColumn()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Alltypes $row */
        $row = $t->createRow();
        
        // ****************************************
        // Für eine nicht-gespeicherte, nicht-existierende Spalte ...
        
        // ... soll offsetExists() false zurückgeben
        $this->assertSame(false, $row->offsetExists('nonexistent'));
        
        // ... soll isset() false zurückgeben
        $this->assertSame(false, isset($row->nonexistent));
        
        // ... soll offsetGet() eine Exception werfen
        try {
            $i1 = $row->offsetGet('nonexistent');
        } catch (\Ruga\Db\Row\Exception\InvalidColumnException $e) {
            $i1 = null;
        }
        $this->assertSame(null, $i1);
        
        // ... soll __get() eine Exception werfen
        try {
            $i2 = $row->nonexistent;
        } catch (\Ruga\Db\Row\Exception\InvalidColumnException $e) {
            $i2 = null;
        }
        $this->assertSame(null, $i2);
        
        // ... soll offsetSet() eine Exception werfen
        try {
            $r1 = $row->offsetSet('nonexistent', 71);
        } catch (\Ruga\Db\Row\Exception\InvalidColumnException $e) {
            $r1 = null;
        }
        $this->assertSame(null, $r1);
        
        // ... soll __set() eine Exception werfen
        try {
            $row->nonexistent = 72;
            $r2 = 2;
        } catch (\Ruga\Db\Row\Exception\InvalidColumnException $e) {
            $r2 = null;
        }
        $this->assertSame(null, $r2);
        
        
        // ... soll unset() eine Exception werfen
        try {
            unset($row->nonexistent);
            $r3 = 3;
        } catch (\Ruga\Db\Row\Exception\InvalidColumnException $e) {
            $r3 = null;
        }
        $this->assertSame(null, $r3);
        
        // ... soll offsetUnset() eine Exception werfen
        try {
            $row->offsetUnset('nonexistent');
            $r4 = 4;
        } catch (\Ruga\Db\Row\Exception\InvalidColumnException $e) {
            $r4 = null;
        }
        $this->assertSame(null, $r4);
    }
    
    
    
    public function testCannotReadAndWriteNonexistentColumnWithoutMetadataFeature()
    {
        $t = new \Ruga\Db\Test\Model\MemberTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Member $row */
        $row = $t->createRow();
        
        // ****************************************
        // Für eine nicht-gespeicherte, nicht-existierende Spalte ...
        
        // ... soll offsetExists() true zurückgeben
        $this->assertSame(true, $row->offsetExists('nonexistent'));
        
        // ... soll isset() false zurückgeben
        $this->assertSame(false, isset($row->nonexistent));
        
        // ... soll offsetGet() eine Exception werfen
        try {
            $i1 = $row->offsetGet('nonexistent');
        } catch (\Ruga\Db\Row\Exception\InvalidColumnException $e) {
            $i1 = null;
        }
        $this->assertSame(null, $i1);
        
        // ... soll __get() eine Exception werfen
        try {
            $i2 = $row->nonexistent;
        } catch (\Ruga\Db\Row\Exception\InvalidColumnException $e) {
            $i2 = null;
        }
        $this->assertSame(null, $i2);
        
        // ... soll offsetSet() den Wert setzen
        $row->offsetSet('nonexistent', 71);
        $this->assertSame(71, $row->nonexistent);
        
        // ... soll unset() den Wert und den Offset löschen
        unset($row->nonexistent);
        $this->assertSame(false, isset($row->nonexistent));
        
        // ... soll __set() den Wert setzen
        $row->nonexistent = 72;
        $this->assertSame(72, $row->offsetGet('nonexistent'));
        
        // ... soll offsetUnset() den Wert und den Offset löschen
        $row->offsetUnset('nonexistent');
        $this->assertSame(false, isset($row->nonexistent));
        
        // ... soll offsetUnset() eine Exception werfen
        try {
            $row->offsetUnset('nonexistent');
            $r4 = 4;
        } catch (\Ruga\Db\Row\Exception\InvalidColumnException $e) {
            $r4 = null;
            print_r($e->getMessage());
        }
        $this->assertSame(null, $r4);
    }
    
    
    
    public function testSaveReturnsInt()
    {
        $t = new \Ruga\Db\Test\Model\SimpleTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Simple $row */
        $row = $t->createRow();
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Simple::class, $row);
        
        $row->data = 'new data 4';
        $this->assertSame('new data 4', $row->data);
        
        $i = $row->save();
        $this->assertIsInt($i);
    }
}
