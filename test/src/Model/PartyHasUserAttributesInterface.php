<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);
namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\RowAttributesInterface;


/**
 * Interface PartyHasUserAttributesInterface
 *
 * @property int    $Party_id                        Primary key / foreign key to Party
 * @property int    $User_id                         Primary key / foreign key to User
 * @property string $remark                          Remark
 */
interface PartyHasUserAttributesInterface extends RowAttributesInterface
{
    
}