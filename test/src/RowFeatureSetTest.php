<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;
use PHPUnit\Framework\TestCase;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Adapter\AdapterInterface;
use Ruga\Db\Row\Exception\NoConstraintsException;
use Ruga\Db\Row\Exception\TooManyConstraintsException;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Schema\Updater;
use Ruga\Db\Test\Model\MetaDefaultTable;
use Ruga\Db\Test\Model\Party;
use Ruga\Db\Test\Model\PartyHasOrganizationTable;
use Ruga\Db\Test\Model\PartyHasPersonTable;
use Ruga\Db\Test\Model\PartyHasUserTable;
use Ruga\Db\Test\Model\PartyTable;
use Ruga\Db\Test\Model\Person;
use Ruga\Db\Test\Model\User;

/**
 *
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class RowFeatureSetTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    public function testCanNotAddSameFeatureMultipleTimes()
    {
        $partyTable = new PartyTable($this->getAdapter());
        /** @var Party $party */
        $party = $partyTable->createRow();
        
        /** @var FeatureSet $fs */
        $fs = $party->getFeatureSet();
        
        
        $a = $fs->applyArray('getName', []);
        $b = array_count_values($a);
        
        foreach ($b as $count) {
            $this->assertEquals(1, $count);
        }
    }
    
    
}
