<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractTable;

class MemberCreateChangeTable extends AbstractTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Member';
    const ROWCLASS = MemberCreateChange::class;
}