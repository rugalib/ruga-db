<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\RowAttributesInterface;

/**
 * Interface SimpleAttributesInterface
 *
 * @property int                $id         Primary key
 * @property int                $fullname   Display name
 * @property string             $first_name First name
 * @property string             $last_name  Last name
 * @property \DateTimeImmutable $created    Date/time the row was created
 * @property int                $createdBy  User id of the creator
 * @property \DateTimeImmutable $changed    Date/time the row was last changed
 * @property int                $changedBy  User id of the editor
 */
interface MemberAttributesInterface extends RowAttributesInterface
{
    
}