<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractTable;

class SimpleTable extends AbstractTable implements SimpleAttributesInterface
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Simple';
    const ROWCLASS = Simple::class;
}