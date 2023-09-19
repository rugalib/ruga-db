<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Laminas\Db\TableGateway\Feature\FeatureSet;
use Ruga\Db\Table\AbstractTable;
use Ruga\Db\Table\Feature\MetadataFeature;


class AlltypesTable extends AbstractTable
{
    const TABLENAME = 'Alltypes';
    const PRIMARYKEY = ['id'];
    const ROWCLASS = Alltypes::class;
    
    
    
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