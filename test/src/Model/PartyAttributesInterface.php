<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\RowAttributesInterface;

/**
 * Interface PartyAttributesInterface
 *
 * @property int              $id                  Primary Key
 * @property string           $fullname            Full name / display
 * @property array            $party_role          Assigned party roles
 * @property PartySubtypeType $party_subtype       Type of the subtype (PERSON/ORGANIZATION)
 * @property string           $remark              Remark
 */
interface PartyAttributesInterface extends RowAttributesInterface
{
    
}