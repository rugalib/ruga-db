<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

/**
 * The organization - party link table.
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class PartyHasOrganizationTable extends AbstractRugaTable
{
    const TABLENAME = 'Party_has_Organization';
    const PRIMARYKEY = ['Party_id', 'Organization_id'];
    const ROWCLASS = PartyHasOrganization::class;
}