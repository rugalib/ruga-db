<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Ruga\Db\Row\RowInterface;

/**
 * Interface ParentFeatureAttributesInterface
 * @see ChildFeature
 *
 * @method RowInterface findParentRow($parentTable, ?string $ruleKey = null, ?Select $select = null) Find the parent row.
 *
 */
interface ChildFeatureAttributesInterface
{

}