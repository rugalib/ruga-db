<?php
declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

class CartTable extends AbstractRugaTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Cart';
    const ROWCLASS = Cart::class;
}