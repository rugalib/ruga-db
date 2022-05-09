<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\FullnameFeature;
use Ruga\Db\Row\Feature\FullnameFeatureAttributesInterface;
use Ruga\Db\Row\Feature\FullnameFeatureRowInterface;

class MemberFullname extends AbstractRow implements MemberFullnameAttributesInterface,
                                                    FullnameFeatureAttributesInterface,
                                                    FullnameFeatureRowInterface
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
        $featureSet->addFeature(new FullnameFeature(true));
        return parent::initFeatures($featureSet);
    }
    
    
    
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