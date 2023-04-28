<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

/**
 * The party table.
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class PartyTable extends AbstractRugaTable
{
    const TABLENAME = 'Party';
    const PRIMARYKEY = ['id'];
    const ROWCLASS = Party::class;
}
