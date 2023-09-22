<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

/**
 * The user - party link table.
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class PartyHasUserTable extends AbstractRugaTable
{
    const TABLENAME = 'Party_has_User';
    const PRIMARYKEY = ['Party_id', 'User_id'];
    const ROWCLASS = PartyHasUser::class;
}