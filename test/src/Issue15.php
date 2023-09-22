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
use Ruga\Db\Schema\Updater;
use Ruga\Db\Test\Model\Party;
use Ruga\Db\Test\Model\PartyHasOrganizationTable;
use Ruga\Db\Test\Model\PartyHasPersonTable;
use Ruga\Db\Test\Model\PartyHasUserTable;
use Ruga\Db\Test\Model\PartyTable;
use Ruga\Db\Test\Model\Person;
use Ruga\Db\Test\Model\PersonTable;
use Ruga\Db\Test\Model\User;

/**
 *
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class Issue15 extends \Ruga\Db\Test\PHPUnit\AbstractTestSetUp
{
    
    public function testCanLinkManyToManyRow()
    {
        // Get existing user
        $userTable = new \Ruga\Db\Test\Model\UserTable($this->getAdapter());
        /** @var User $user */
        $user = $userTable->findById(4)->current();
        
        // Create new party
        $partyTable = new \Ruga\Db\Test\Model\PartyTable($this->getAdapter());
        /** @var Party $party */
        $party = $partyTable->createRow(['party_subtype' => 'PERSON']);
        
        // Create new person
        $personTable = new \Ruga\Db\Test\Model\PersonTable($this->getAdapter());
        /** @var Person $person */
        $person = $personTable->createRow();
        $person->first_name = 'Prisca';
        $person->last_name = 'Kaufmann';
        
        // Link the person to the party
        $party->linkManyToManyRow($person, PartyHasPersonTable::class);
        
        // Link the party to the existing user
        $link = $user->linkManyToManyRow($party, PartyHasUserTable::class, [], null, 'fk_Party_has_User_User_id');
        
        // Save everything
        $link->save();
        
        unset($party);
        unset($person);
        unset($link);
        
        
        // find party from user
        /** @var Party $party */
        $party = $user->findManyToManyRowset(PartyTable::class, PartyHasUserTable::class, 'User_id')->current();
        $this->assertInstanceOf(Party::class, $party);
        
        // find person from party
        /** @var Person $person */
        $person = $party->findManyToManyRowset(PersonTable::class, PartyHasPersonTable::class)->current();
        $this->assertInstanceOf(Person::class, $person);
    }
    
    
}
