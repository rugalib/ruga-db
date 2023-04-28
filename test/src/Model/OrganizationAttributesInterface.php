<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\RowAttributesInterface;

/**
 * Interface OrganizationAttributesInterface
 *
 * @property string             $name
 * @property array              $org_type
 * @property string             $org_subtype
 * @property \DateTimeImmutable $date_of_establishment
 * @property \DateTimeImmutable $date_of_dissolution
 * @property string             $remark
 */
interface OrganizationAttributesInterface extends RowAttributesInterface
{
    
}