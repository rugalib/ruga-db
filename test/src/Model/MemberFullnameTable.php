<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractTable;

class MemberFullnameTable extends AbstractTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Member';
    const ROWCLASS = MemberFullname::class;
}