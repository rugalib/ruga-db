<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\RowAttributesInterface;

/**
 * Interface SimpleAttributesInterface
 *
 * @property int    $id         Primary key
 * @property string $fullname   Display name
 * @property int    $Simple_id  Foreign key table Simple
 * @property int    $Tenant_id  Foreign key table Tenant
 */
interface MusterAttributesInterface extends RowAttributesInterface
{
    
}