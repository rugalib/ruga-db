<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test;

use Ruga\Db\Table\Feature\FeatureSet;
use Ruga\Db\Test\Model\PartyTable;

/**
 *
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class TableFeatureSetTest extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    public function testCanNotAddSameFeatureMultipleTimes()
    {
        $partyTable = new PartyTable($this->getAdapter());
        
        /** @var FeatureSet $fs */
        $fs = $partyTable->getFeatureSet();
        
        
        $a = $fs->applyArray('getName', []);
        $b = array_count_values($a);
        
        foreach ($b as $count) {
            $this->assertEquals(1, $count);
        }
    }
    
    
}
