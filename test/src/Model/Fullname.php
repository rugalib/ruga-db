<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\FullnameFeature;
use Ruga\Db\Row\Feature\FullnameFeatureRowInterface;

class Fullname extends AbstractRow implements FullnameAttributesInterface, FullnameFeatureRowInterface
{
    /**
     * Add features to the row class before it is initialized by the parent.
     *
     * @param FeatureSet $featureSet
     *
     * @return FeatureSet
     */
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        $featureSet->addFeature(new FullnameFeature(false));
        return parent::initFeatures($featureSet);
    }
    
    
    
    /**
     * Constructs a display name from the given fields.
     *
     * @return string
     * @throws \Exception
     * @see FullnameFeature
     * @see FullnameFeatureRowInterface
     *
     */
    public function getFullname(): string
    {
        return $this->offsetGet('data');
    }
    
    
}