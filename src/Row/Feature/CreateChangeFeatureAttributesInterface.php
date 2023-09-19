<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

/**
 * Interface CreateChangeFeatureAttributesInterface
 *
 * @property \DateTimeImmutable $created   Date and time the row was created
 * @property int                $createdBy Creators user id
 * @property \DateTimeImmutable $changed   Date and time the row was last changed
 * @property int                $changedBy Editors user id
 */
interface CreateChangeFeatureAttributesInterface
{
    
}