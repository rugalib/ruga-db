<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Ruga\Db\Row\RowInterface;

/**
 * Interface ParentFeatureAttributesInterface
 * @see ParentFeature
 *
 * @method ResultSetInterface findDependentRowset($dependentTable, ?string $ruleKey = null, ?Select $select = null) Find dependent rows (children) in table $dependentTable
 * @method RowInterface createDependentRow($dependentTable, array $rowData = [], ?string $ruleKey = null) Create a new dependent row.
 * @method RowInterface linkDependentRow(RowInterface $dependentRow, ?string $ruleKey = null) Link a dependent row to this parent.
 * @method RowInterface unlinkDependentRow(RowInterface $dependentRow, ?string $ruleKey = null)  Unlink a dependent row from this parent.
 * @method void deleteDependentRow(RowInterface $dependentRow, ?string $ruleKey = null) Delete a dependent row. The delete is done, when the parent row is saved.
 * @method void dependentRowListAdd(RowInterface $dependentRow, string $constraintName, string $action = 'save') Add $dependentRow to the internal list of children.
 *
 */
interface ParentFeatureAttributesInterface
{

}