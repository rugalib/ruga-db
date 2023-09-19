<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;
use Laminas\Db\Sql\Update;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use PHPUnit\Framework\TestCase;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Adapter\AdapterInterface;
use Ruga\Db\Schema\Resolver;
use Ruga\Db\Schema\Updater;
use Ruga\Db\Test\Model\MemberTable;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class ResolverTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    public function testCanCreateResolver(): void
    {
        $resolver = Updater::getResolver($this->getAdapter(), $this->getConfig());
        $this->assertInstanceOf(Resolver::class, $resolver);
    }
    
    
    
    public function testCanResolveTableName(): void
    {
        $resolver = Updater::getResolver($this->getAdapter(), $this->getConfig());
        $this->assertInstanceOf(Resolver::class, $resolver);
        
        $tableName = $resolver->getTableName('Member');
        echo "Table name: {$tableName}" . PHP_EOL;
        $this->assertEquals(MemberTable::TABLENAME, $tableName);
    }
    
    
    
    public function testCanResolveShortClassName(): void
    {
        $resolver = Updater::getResolver($this->getAdapter(), $this->getConfig());
        $this->assertInstanceOf(Resolver::class, $resolver);
        
        $tableName = $resolver->getTableName('MemberTable');
        echo "Table name: {$tableName}" . PHP_EOL;
        $this->assertEquals(MemberTable::TABLENAME, $tableName);
    }
    
    
    
    public function testCanResolveFQCN(): void
    {
        $resolver = Updater::getResolver($this->getAdapter(), $this->getConfig());
        $this->assertInstanceOf(Resolver::class, $resolver);
        
        $tableName = $resolver->getTableName(MemberTable::class);
        echo "Table name: {$tableName}" . PHP_EOL;
        $this->assertEquals(MemberTable::TABLENAME, $tableName);
    }
    
    
    
    public function testCanResolveAlias(): void
    {
        $resolver = Updater::getResolver($this->getAdapter(), $this->getConfig());
        $this->assertInstanceOf(Resolver::class, $resolver);
        
        $tableName = $resolver->getTableName('Mem');
        echo "Table name: {$tableName}" . PHP_EOL;
        $this->assertEquals(MemberTable::TABLENAME, $tableName);
    }
    
}
