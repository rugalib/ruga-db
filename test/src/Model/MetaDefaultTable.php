<?php

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Laminas\Db\TableGateway\Feature\FeatureSet;
use Ruga\Db\Table\AbstractTable;
use Ruga\Db\Table\Feature\MetadataFeature;

class MetaDefaultTable extends AbstractTable
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Simple';
    const ROWCLASS = MetaDefault::class;
    
    
    
    /**
     * Add features to the row class before it is initialized by the parent.
     *
     * @param FeatureSet $featureSet
     *
     * @return FeatureSet
     */
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        $featureSet->addFeature(new MetadataFeature());
        return parent::initFeatures($featureSet);
    }
    
    
}