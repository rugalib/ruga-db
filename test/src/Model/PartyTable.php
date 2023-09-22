<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Laminas\Db\TableGateway\Feature\FeatureSet;
use Ruga\Db\Table\AbstractRugaTable;
use Ruga\Db\Table\Feature\MetadataFeature;

/**
 * The party table.
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
class PartyTable extends AbstractRugaTable
{
    const TABLENAME = 'Party';
    const PRIMARYKEY = ['id'];
    const ROWCLASS = Party::class;
    
    
    
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        $featureSet = parent::initFeatures($featureSet);
        $featureSet->addFeature(new MetadataFeature());
        return $featureSet;
    }
    
}
