<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;

/**
 * Interface ParentFeatureAttributesInterface
 *
 * @method ResultSetInterface findDependentRowset($dependentTable, ?string $ruleKey = null, ?Select $select = null) Find dependent rows (children) in table $dependentTable
 *
 */
interface ParentFeatureAttributesInterface
{

}