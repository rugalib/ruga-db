<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRugaRow;

/**
 * Links a user to a party.
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class PartyHasUser extends AbstractRugaRow implements PartyHasUserAttributesInterface
{
    
}