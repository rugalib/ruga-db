<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRugaRow;
use Ruga\Db\Row\Feature\FullnameFeature;
use Ruga\Db\Row\Feature\FullnameFeatureRowInterface;

class MemberRuga extends AbstractRugaRow implements MemberRugaAttributesInterface
{
    /**
     * Constructs a display name from the given fields.
     *
     * @return string
     * @see FullnameFeatureRowInterface
     *
     * @see FullnameFeature
     */
    public function getFullname(): string
    {
        return implode(' ', array_filter([$this->first_name, $this->last_name]));
    }
}