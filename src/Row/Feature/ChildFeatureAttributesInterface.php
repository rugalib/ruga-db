<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Ruga\Db\Row\RowInterface;

/**
 * Interface ChildFeatureAttributesInterface
 * @see ChildFeature
 *
 * @method RowInterface|null findParentRow($parentTable, ?string $ruleKey = null, ?Select $select = null) Find the parent row.
 * @method RowInterface createParentRow($parentTable, array $rowData = [], ?string $ruleKey = null) Create a new parent row.
 * @method RowInterface linkParentRow(RowInterface $parentRow, ?string $ruleKey = null) Link parent to this dependent row.
 * @method RowInterface unlinkParentRow($parentTable, ?string $ruleKey = null) Remove relation between this row and the given parent.
 * @method void deleteParentRow($parentTable, ?string $ruleKey = null) Delete the parent row. The delete is done, when the dependent row is saved.
 * @method void parentRowListAdd(RowInterface $parentRow, string $constraintName, string $action = 'save') Add $parentRow to the internal list of parents.
 *
 */
interface ChildFeatureAttributesInterface
{

}