<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Adapter\AdapterFactory;
use Ruga\Db\Adapter\AdapterInterface;
use Ruga\Db\Adapter\Exception\WrongDbVersionException;
use Ruga\Db\Schema\Updater;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class Adapter2Test extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    public function testCanCreateTableFromFQCN(): void
    {
        $adapter = $this->getAdapter();
        
        $table = $adapter->tableFactory(\Ruga\Db\Test\Model\MemberTable::class);
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MemberTable::class, $table);
        
        $table = $adapter->tableFactory('MemberTable');
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MemberTable::class, $table);
        
        $table = $adapter->tableFactory('MetaTable');
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaTable::class, $table);
        
        $table = $adapter->tableFactory('Simple');
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MetaTable::class, $table);
        
        $table = $adapter->tableFactory('Mem');
        $this->assertInstanceOf(\Ruga\Db\Test\Model\MemberTable::class, $table);
    }
    
    
    
    public function testCanCreateRowWithFactory(): void
    {
        $adapter = $this->getAdapter();
        
        $row = $adapter->rowFactory(5, 'Simple');
        echo ($row->data) . PHP_EOL;
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Meta::class, $row);
        
        $row = $adapter->rowFactory(2, \Ruga\Db\Test\Model\MemberTable::class);
        echo ($row->getFullname()) . PHP_EOL;
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Member::class, $row);
        
        $row = $adapter->rowFactory('2@MemberTable');
        echo ($row->getFullname()) . PHP_EOL;
        $this->assertInstanceOf(\Ruga\Db\Test\Model\Member::class, $row);
    }
    
    
}
