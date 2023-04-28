<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

class OrganizationTable extends AbstractRugaTable
{
    const TABLENAME = 'Organization';
    const PRIMARYKEY = ['id'];
    const ROWCLASS = Organization::class;
}