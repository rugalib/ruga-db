<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);
namespace Ruga\Db\Schema\Exception;

class SchemaUpdateFailedException extends \RuntimeException
{
    private string $sql;
    
    
    
    public function __construct($message = "", $code = 0, \Throwable $previous = null, string $sql = "")
    {
        $this->sql = $sql;
        parent::__construct($message, $code, $previous);
    }
    
    
    
    public function getSql(): string
    {
        return $this->sql;
    }
    
    
    
    public function __toString()
    {
        return parent::__toString() . PHP_EOL . $this->getSql();
    }
    
    
}