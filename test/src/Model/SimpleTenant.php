<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\TenantFeature;

class SimpleTenant extends AbstractRow implements SimpleAttributesInterface
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
        $featureSet->addFeature(new TenantFeature(1));
        return parent::initFeatures($featureSet);
    }
    
}