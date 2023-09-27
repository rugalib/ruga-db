<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

/**
 * Class AlltypesEnumType.
 *
 * @method static self QUESTION()
 * @method static self PROBLEM()
 * @method static self REQUEST()
 * @method static self OTHER()
 */
class AlltypesEnumType extends \Ruga\Std\Enum\AbstractEnum
{
    const QUESTION = 'QUESTION';
    const PROBLEM = 'PROBLEM';
    const REQUEST = 'REQUEST';
    const OTHER = 'OTHER';
}