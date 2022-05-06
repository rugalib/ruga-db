<?php

declare(strict_types=1);

namespace Ruga\Db\Table;

use Laminas\Db\TableGateway\Feature\FeatureSet;
use Ruga\Db\Table\Feature\MetadataFeature;

abstract class AbstractRugaTable extends AbstractTable
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
//        $featureSet->addFeature(new GlobalAdapterFeature());
        $featureSet->addFeature(new MetadataFeature());
        return parent::initFeatures($featureSet);
    }
    
}