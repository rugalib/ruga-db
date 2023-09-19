<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Table\Feature;

class FeatureSet extends \Laminas\Db\TableGateway\Feature\FeatureSet
{
    /**
     * Clone all the features.
     */
    public function __clone()
    {
        array_walk($this->features, function (AbstractFeature &$feature, $key) {
            $feature=clone $feature;
        });
    }
    
}