<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Laminas\Db\TableGateway\Feature\FeatureSet;
use Ruga\Db\Table\AbstractTable;
use Ruga\Db\Table\Feature\TenantFeature;

class SimpleTenantTable extends AbstractTable implements SimpleAttributesInterface
{
    const PRIMARYKEY = ['id'];
    const TABLENAME = 'Simple';
    const ROWCLASS = SimpleTenant::class;
    
    
    
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