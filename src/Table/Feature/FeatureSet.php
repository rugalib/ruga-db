<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Table\Feature;

use Laminas\Db\TableGateway\Feature\AbstractFeature;

class FeatureSet extends \Laminas\Db\TableGateway\Feature\FeatureSet
{
    /**
     * Clone all the features.
     */
    public function __clone()
    {
        array_walk($this->features, function (AbstractFeature &$feature, $key) {
            $feature = clone $feature;
        });
    }
    
    
    
    /**
     * Run a method in all features and return the results as an array.
     *
     * @param $method
     * @param $args
     *
     * @return array
     */
    public function applyArray($method, $args): array
    {
        $a = [];
        foreach ($this->features as $feature) {
            if (method_exists($feature, $method)) {
                $return = call_user_func_array([$feature, $method], $args);
                $a[] = $return;
                if ($return === self::APPLY_HALT) {
                    return $a;
                }
            }
        }
        return $a;
    }
    
    
    
    /**
     * Add feature to the list if it does not already exist.
     *
     * @param AbstractFeature $feature
     *
     * @return $this|\Ruga\Db\Row\Feature\FeatureSet
     * @throws \Exception
     */
    public function addFeature(AbstractFeature $feature)
    {
        if ($this->getFeatureByClassName(get_class($feature))) {
            $caller = debug_backtrace()[1]['class'] ?? '';
            \Ruga\Log::addLog("Feature " . get_class($feature) . " already exists in $caller");
            return $this;
        }
        return parent::addFeature($feature);
    }
    
    
}