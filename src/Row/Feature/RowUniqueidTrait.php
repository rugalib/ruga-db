<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace Ruga\Db\Row\Feature;

use Laminas\Db\RowGateway\AbstractRowGateway;

trait RowUniqueidTrait
{
    /**
     * Return uniqueid of a row, even if FullnameFeature is not implemented.
     *
     * @param AbstractRowGateway $row
     *
     * @return string
     * @throws \ReflectionException
     */
    private function rowUniqueid(AbstractRowGateway $row)
    {
        $uniqueid = implode('-', $row->primaryKeyData ?? []);
        $uniqueid = empty($uniqueid) ? '?' . spl_object_hash($row) : $uniqueid;
        $uniqueid .= '@' . (new \ReflectionClass($row->getTableGateway()))->getShortName();
        return $uniqueid;
    }
    
}