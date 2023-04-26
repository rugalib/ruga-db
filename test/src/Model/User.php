<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRugaRow;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\ParentFeature;

class User extends AbstractRugaRow
{
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        $featureSet=parent::initFeatures($featureSet);
        $featureSet->addFeature(new ParentFeature());
        return $featureSet;
    }
    
}