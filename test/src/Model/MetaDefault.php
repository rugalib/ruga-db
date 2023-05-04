<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\Feature\DefaultValueFeature;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\ParentFeature;
use Ruga\Db\Row\Feature\ParentFeatureAttributesInterface;
use Ruga\Db\Row\Feature\TransactionFeature;

class MetaDefault extends AbstractRow implements MetaDefaultAttributesInterface, ParentFeatureAttributesInterface
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
        $featureSet=parent::initFeatures($featureSet);
        $featureSet->addFeature(new DefaultValueFeature());
        $featureSet->addFeature(new ParentFeature());
        $featureSet->addFeature(new TransactionFeature());
        return $featureSet;
    }
    
}