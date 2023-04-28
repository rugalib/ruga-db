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
 *
 * @method RowInterface createParentRow($parentTable, array $rowData = [], ?string $ruleKey = null) Create a new parent row.
 * @method RowInterface linkParentRow(RowInterface $parentRow, ?string $ruleKey = null) Link parent to this dependent row.
 * @method RowInterface unlinkParentRow($parentTable, ?string $ruleKey = null) Remove relation between this row and the given parent.
 * @method void deleteParentRow($parentTable, ?string $ruleKey = null) Delete the parent row. The delete is done, when the dependent row is saved.
 * @method void manyToManyRowListAdd(RowInterface $parentRow, string $constraintName, string $action = 'save') Add $parentRow to the internal list of parents.
 *
 */
interface ManyToManyFeatureAttributesInterface
{

}