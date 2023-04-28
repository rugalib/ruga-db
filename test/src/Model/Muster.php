<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRugaRow;
use Ruga\Db\Row\Feature\ChildFeature;
use Ruga\Db\Row\Feature\ChildFeatureAttributesInterface;
use Ruga\Db\Row\Feature\FeatureSet;

class Muster extends AbstractRugaRow implements MusterAttributesInterface, ChildFeatureAttributesInterface
{
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        $featureSet=parent::initFeatures($featureSet);
        $featureSet->addFeature(new ChildFeature());
        return $featureSet;
    }
}