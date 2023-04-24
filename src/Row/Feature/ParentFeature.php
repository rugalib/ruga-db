<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Ruga\Db\Row\Exception\InvalidArgumentException;
use Ruga\Db\Row\Exception\ReadonlyArgumentException;
use Ruga\Db\Table\Feature\MetadataFeature;

/**
 * The parent feature adds the ability to find, add and remove children
 */
class ParentFeature extends AbstractFeature implements ParentFeatureAttributesInterface
{
    
    
    /**
     * Constructs a display name from the given fields.
     * Fullname is saved in the row to speed up queries.
     *
     * @return string
     */
    public function dumpChildren($a, $b, $c): array
    {
        \Ruga\Log::functionHead($this);
        
        \Ruga\Log::addLog("\$a=$a | \$b=$b | \$c=$c");
        
        return [];
    }
    
    
}