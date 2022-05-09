<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractTable;

class FullnameTable extends AbstractTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Simple';
    const ROWCLASS = Fullname::class;
}