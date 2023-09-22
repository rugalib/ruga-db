<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

/**
 * The person - party link table.
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class PartyHasPersonTable extends AbstractRugaTable
{
    const TABLENAME = 'Party_has_Person';
    const PRIMARYKEY = ['Party_id', 'Person_id'];
    const ROWCLASS = PartyHasPerson::class;
}