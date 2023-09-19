<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Row\Exception;

class InvalidColumnException extends InvalidArgumentException
{
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @link https://php.net/manual/en/exception.construct.php
     *
     * @param string $column
     * @param string $class
     */
    public function __construct(string $column, string $class)
    {
        $message = "'{$column}' is not a valid column in '{$class}'";
        parent::__construct($message);
    }
    
}