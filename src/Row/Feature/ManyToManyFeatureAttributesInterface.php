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
 * Interface ManyToManyFeatureAttributesInterface
 * @see ManyToManyFeature
 *
 * @method ResultSetInterface findManyToManyRowset($mTable, $iTable, ?string $nRuleKey = null, ?string $mRuleKey = null, ?Select $select = null) Find rows via many-to-many relation.
 * @method ResultSetInterface findIntersectionRows(RowInterface $mRow, $iTable, ?string $nRuleKey = null, ?string $mRuleKey = null, ?Select $select = null) Find intersection rows from many-to-many relation.
 * @method RowInterface createManyToManyRow($mTable, $iTable, array $mRowData = [], array $iRowData = [], ?string $mRuleKey = null, ?string $nRuleKey = null) Create a new row in the $mTable, linked via $intersectionTable.
 * @method RowInterface linkManyToManyRow(RowInterface $mRow, $iTable, array $iRowData = [], ?string $mRuleKey = null, ?string $nRuleKey = null) Link an existing $mRow to the $nRow using $iTable.
 * @method RowInterface unlinkManyToManyRow(RowInterface $mRow, $iTable, ?string $mRuleKey = null, ?string $nRuleKey = null) Unlink intersection and match row.
 * @method RowInterface deleteManyToManyRow(RowInterface $mRow, $iTable, ?string $mRuleKey = null, ?string $nRuleKey = null) Delete intersection and match row. Deletion is done, when this row is saved.
 *
 */
interface ManyToManyFeatureAttributesInterface
{

}