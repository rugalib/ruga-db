<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Ruga\Db\Test\Model\AlltypesEnumType;

/**
 *
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class Issue28 extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    public function testCanAssignObjectToSetColumn()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Alltypes $row */
        $row = $t->findById(1)->current();
        
        $row->set_nn = AlltypesEnumType::REQUEST();
        $row->save();
        
        $ret = $row->set_nn;
        var_dump($ret);
        $this->assertIsArray($ret);
        $this->assertTrue(in_array(AlltypesEnumType::REQUEST(), $ret));
    }
    
    
    
    public function testCanAssignObjectArrayToSetColumn()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Alltypes $row */
        $row = $t->findById(1)->current();
        
        $row->set_nnd = [AlltypesEnumType::REQUEST(), AlltypesEnumType::PROBLEM()];
        $row->save();
        
        $ret = $row->set_nnd;
        var_dump($ret);
        $this->assertIsArray($ret);
        $this->assertTrue(in_array(AlltypesEnumType::REQUEST(), $ret));
    }
    
    
    
    public function testCanSetObjectToSetColumn()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Alltypes $row */
        $row = $t->findById(1)->current();
        
        $row->offsetSet('set_n', AlltypesEnumType::REQUEST());
        $row->save();
        
        $ret = $row->offsetGet('set_n');
        var_dump($ret);
        $this->assertIsArray($ret);
        $this->assertTrue(in_array(AlltypesEnumType::REQUEST(), $ret));
    }
    
    
    
    public function testCanSetObjectArrayToSetColumn()
    {
        $t = new \Ruga\Db\Test\Model\AlltypesTable($this->getAdapter());
        /** @var \Ruga\Db\Test\Model\Alltypes $row */
        $row = $t->findById(1)->current();
        
        $row->offsetSet('set_nd', [AlltypesEnumType::REQUEST(), AlltypesEnumType::PROBLEM()]);
        $row->save();
        
        $ret = $row->offsetGet('set_nd');
        var_dump($ret);
        $this->assertIsArray($ret);
        $this->assertTrue(in_array(AlltypesEnumType::REQUEST(), $ret));
        $this->assertTrue(in_array(AlltypesEnumType::PROBLEM(), $ret));
    }
    
    
}
