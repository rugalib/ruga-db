<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractTable;

class MemberFullnameTable extends AbstractTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Member';
    const ROWCLASS = MemberFullname::class;
}