<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Adapter\Exception;

class QueryRuntimeException extends \RuntimeException
{
    private string $query;
    
    
    
    public function __construct($message = "", $code = 0, \Throwable $previous = null, string $query = '')
    {
        $this->query = $query;
        parent::__construct($message, $code, $previous);
    }
    
    
    
    public function getQuery(): string
    {
        return $this->query;
    }
    
}