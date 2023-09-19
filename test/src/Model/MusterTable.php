<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

class MusterTable extends AbstractRugaTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Muster';
    const ROWCLASS = Muster::class;
    const REFERENCEMAP = [
        'SimpleTableLink' => [
            'COLUMNS' => ['Simple_id'],
            'REF_TABLE_CLASS' => MetaDefaultTable::class,
            'REF_COLUMNS' => ['id']
        ]
    ];
}