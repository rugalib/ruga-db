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
 * @property int $id        Primary key
 * @property int $data      Data
 * @property int $Tenant_id Foreign key of the tenant
 */
interface SimpleAttributesInterface extends RowAttributesInterface
{
    
}