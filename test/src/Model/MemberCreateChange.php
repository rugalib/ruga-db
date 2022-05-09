<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\Feature\CreateChangeFeature;
use Ruga\Db\Row\Feature\CreateChangeFeatureAttributesInterface;
use Ruga\Db\Row\Feature\FeatureSet;

class MemberCreateChange extends AbstractRow implements MemberCreateChangeAttributesInterface,
                                                        CreateChangeFeatureAttributesInterface
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
        $featureSet->addFeature(new CreateChangeFeature(1));
        return parent::initFeatures($featureSet);
    }
    
}