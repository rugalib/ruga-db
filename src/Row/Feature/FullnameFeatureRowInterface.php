<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

/**
 * The fullname feature adds the following attributes to the row:
 */
interface FullnameFeatureRowInterface extends FullnameFeatureAttributesInterface
{
    /**
     * Constructs a display name from the given fields.
     * Fullname is saved in the row to speed up queries.
     *
     * @return string
     */
    public function getFullname(): string;
    
}