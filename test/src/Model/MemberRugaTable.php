<?php
declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

class MemberRugaTable extends AbstractRugaTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Member';
    const ROWCLASS = MemberRuga::class;
}