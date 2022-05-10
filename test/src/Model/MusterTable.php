<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

class MusterTable extends AbstractRugaTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Muster';
    const ROWCLASS = Muster::class;
}