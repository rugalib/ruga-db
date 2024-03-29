<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

/**
 * Interface FullnameFeatureAttributesInterface
 *
 * @property string $PK       Primary key (dash-separated if multi-key)
 * @property string $row_id   Primary key (dash-separated if multi-key)
 * @property string $idname   Display name and primary key
 * @property string $uniqueid Unique id of the row in schema-scope
 * @property string $type     Entity type
 * @property string $fullname Display name
 */
interface FullnameFeatureAttributesInterface
{
    
}