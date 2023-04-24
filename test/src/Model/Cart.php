<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRugaRow;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\ParentFeature;
use Ruga\Db\Row\Feature\ParentFeatureAttributesInterface;

class Cart extends AbstractRugaRow implements CartAttributesInterface, ParentFeatureAttributesInterface
{
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        $featureSet->addFeature(new ParentFeature());
        return parent::initFeatures($featureSet);
    }
    
}