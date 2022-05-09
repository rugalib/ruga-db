<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\Feature\CreateChangeFeature;
use Ruga\Db\Row\Feature\DefaultValueFeature;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\FullnameFeature;
use Ruga\Db\Row\Feature\FullnameFeatureRowInterface;

/**
 * @property int $id Primary key
 */
class Alltypes extends AbstractRow implements AlltypesAttributesInterface, FullnameFeatureRowInterface
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
        $featureSet->addFeature(new FullnameFeature());
        $featureSet->addFeature(new DefaultValueFeature());
        $featureSet->addFeature(new CreateChangeFeature(1));
        return parent::initFeatures($featureSet);
    }
    
    
}
