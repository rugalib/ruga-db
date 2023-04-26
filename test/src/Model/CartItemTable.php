<?php
declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Table\AbstractRugaTable;

class CartItemTable extends AbstractRugaTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'CartItem';
    const ROWCLASS = CartItem::class;
    const DEPENDENTTABLES = [];
}