<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\Feature\DefaultValueFeature;
use Ruga\Db\Row\Feature\FeatureSet;

class MetaDefault extends AbstractRow implements MetaDefaultAttributesInterface
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
        $featureSet->addFeature(new DefaultValueFeature());
        return parent::initFeatures($featureSet);
    }
    
}