<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Laminas\Db\TableGateway\Feature\FeatureSet;
use Ruga\Db\Row\Feature\FullnameFeature;
use Ruga\Db\Table\AbstractTable;
use Ruga\Db\Table\Feature\MetadataFeature;

class MemberTable extends AbstractTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Member';
    const ROWCLASS = Member::class;
}