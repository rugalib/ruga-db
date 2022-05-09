<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\RowAttributesInterface;

/**
 *
 * @property int    $id         Primary key
 * @property string $first_name First name
 * @property string $last_name  Last name
 */
interface MemberRugaAttributesInterface extends RowAttributesInterface
{
    
}