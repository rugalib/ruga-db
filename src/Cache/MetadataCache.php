<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Cache;

/**
 * @author Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class MetadataCache implements MetadataCacheInterface
{
    public static function prepareCacheKey(array $a)
    {
        return str_replace(['\\', '/'], '_', implode('-', $a));
    }
}