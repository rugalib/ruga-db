<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRugaRow;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\ParentFeature;
use Ruga\Db\Row\Feature\ParentFeatureAttributesInterface;
use Ruga\Db\Row\Feature\TransactionFeature;

class Cart extends AbstractRugaRow implements CartAttributesInterface, ParentFeatureAttributesInterface
{
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        $featureSet=parent::initFeatures($featureSet);
//        $featureSet->addFeature(new ParentFeature());
//        $featureSet->addFeature(new TransactionFeature());
        return $featureSet;
    }
    
}