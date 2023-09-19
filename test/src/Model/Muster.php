<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\AbstractRugaRow;
use Ruga\Db\Row\Feature\ChildFeature;
use Ruga\Db\Row\Feature\ChildFeatureAttributesInterface;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\TransactionFeature;

class Muster extends AbstractRugaRow implements MusterAttributesInterface, ChildFeatureAttributesInterface
{
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        $featureSet=parent::initFeatures($featureSet);
        $featureSet->addFeature(new ChildFeature());
        $featureSet->addFeature(new TransactionFeature());
        return $featureSet;
    }
}