<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Laminas\Db\TableGateway\Feature\FeatureSet;
use Ruga\Db\Table\AbstractRugaTable;
use Ruga\Db\Table\Feature\MetadataFeature;

class UserTable extends AbstractRugaTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'User';
    const ROWCLASS = User::class;
}