<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Ruga\Db\Row\RowInterface;

/**
 * Interface ManyToManyFeatureAttributesInterface
 * @see ManyToManyFeature
 *
 * @method ResultSetInterface findManyToManyRowset($mTable, $intersectionTable, ?string $nRuleKey = null, ?string $mRuleKey = null, ?Select $select = null) Find rows via many-to-many relation.
 * @method RowInterface createManyToManyRow($mTable, $intersectionTable, array $mRowData = [], array $iRowData = [], ?string $mRuleKey = null, ?string $nRuleKey = null) Create a new row in the $mTable, linked via $intersectionTable.
 *
 */
interface ManyToManyFeatureAttributesInterface
{

}