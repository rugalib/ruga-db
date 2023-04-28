<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRugaRow;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\ManyToManyFeature;
use Ruga\Db\Row\Feature\ManyToManyFeatureAttributesInterface;

/**
 * Implements the party entity.
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class Party extends AbstractRugaRow implements ManyToManyFeatureAttributesInterface
{
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        $featureSet=parent::initFeatures($featureSet);
        $featureSet->addFeature(new ManyToManyFeature());
        return $featureSet;
    }
    
}
