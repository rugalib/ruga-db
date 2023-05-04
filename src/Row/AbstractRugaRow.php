<?php

declare(strict_types=1);

namespace Ruga\Db\Row;

use Ruga\Db\Row\Feature\ChildFeature;
use Ruga\Db\Row\Feature\ChildFeatureAttributesInterface;
use Ruga\Db\Row\Feature\CreateChangeFeature;
use Ruga\Db\Row\Feature\CreateChangeFeatureAttributesInterface;
use Ruga\Db\Row\Feature\DefaultValueFeature;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\FullnameFeature;
use Ruga\Db\Row\Feature\FullnameFeatureAttributesInterface;
use Ruga\Db\Row\Feature\FullnameFeatureRowInterface;
use Ruga\Db\Row\Feature\ManyToManyFeature;
use Ruga\Db\Row\Feature\ManyToManyFeatureAttributesInterface;
use Ruga\Db\Row\Feature\ParentFeature;
use Ruga\Db\Row\Feature\ParentFeatureAttributesInterface;
use Ruga\Db\Row\Feature\TransactionFeature;

abstract class AbstractRugaRow extends AbstractRow implements CreateChangeFeatureAttributesInterface,
                                                              FullnameFeatureAttributesInterface,
                                                              FullnameFeatureRowInterface,
                                                              ParentFeatureAttributesInterface,
                                                              ChildFeatureAttributesInterface,
                                                              ManyToManyFeatureAttributesInterface
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
        $featureSet->addFeature(new DefaultValueFeature());
        $featureSet->addFeature(new FullnameFeature());
        
        if (class_exists($authFacade = '\Ruga\Authentication\Facade\Auth', true)) {
            $user = $authFacade::getIdentityFromSession();
            $user_id = isset($user['details']['id']) ? (int)$user['details']['id'] : 1;
        } else {
            $user_id = 1;
        }
//        \Ruga\Log::log_msg(get_called_class() . '::initFeatures(): $user_id=' . $user_id);
        $featureSet->addFeature(new CreateChangeFeature($user_id));
        $featureSet->addFeature(new ParentFeature());
        $featureSet->addFeature(new ChildFeature());
        $featureSet->addFeature(new ManyToManyFeature());
        $featureSet->addFeature(new TransactionFeature());
        return parent::initFeatures($featureSet);
    }
    
}