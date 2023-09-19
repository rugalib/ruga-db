<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Adapter;

/**
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
interface AdapterInterface extends \Laminas\Db\Adapter\AdapterInterface
{
    public function tableFactory($table);
    public function rowFactory($id, $ref_table=null);
    
}