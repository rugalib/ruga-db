<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Laminas\Db\RowGateway\Feature\AbstractFeature;
use Ruga\Db\Row\Exception\InvalidArgumentException;

class FeatureSet extends \Laminas\Db\RowGateway\Feature\FeatureSet
{
    
    
    public function canCallMagicGet($property)
    {
        if (!empty($this->features)) {
            foreach ($this->features as $feature) {
                if ($feature->__isset($property)) {
                    return true;
                }
            }
        }
        return parent::canCallMagicGet($property);
    }
    
    
    
    public function callMagicGet($property)
    {
        if (!empty($this->features)) {
            foreach ($this->features as $feature) {
                try {
                    return $feature->__get($property);
                } catch (\Exception $e) {
                    if (!($e instanceof InvalidArgumentException)) {
                        \Ruga\Log::addLog($e);
                    }
                }
            }
        }
        return parent::callMagicGet($property);
    }
    
    
    
    /**
     * @param string $property
     *
     * @return bool
     */
    public function canCallMagicSet($property)
    {
        if (!empty($this->features)) {
            foreach ($this->features as $feature) {
                if ($feature->__isset($property)) {
                    return true;
                }
            }
        }
        return parent::canCallMagicSet($property);
    }
    
    
    
    /**
     * @param $property
     * @param $value
     *
     * @return void
     * @throws \Exception
     */
    public function callMagicSet($property, $value)
    {
        if (!empty($this->features)) {
            foreach ($this->features as $feature) {
                try {
                    $feature->__set($property, $value);
                    return;
                } catch (\Exception $e) {
                    if (!($e instanceof InvalidArgumentException)) {
                        throw $e;
                    }
                }
            }
        }
        parent::callMagicSet($property, $value);
    }
    
    
    
    public function canCallMagicCall($method)
    {
        if (!empty($this->features)) {
            foreach ($this->features as $feature) {
                if (method_exists($feature, $method) && is_callable([$feature, $method])) {
                    return true;
                }
            }
        }
        return parent::canCallMagicCall($method);
    }
    
    
    
    public function callMagicCall($method, $arguments)
    {
        if (!empty($this->features)) {
            foreach ($this->features as $feature) {
                try {
                    if (method_exists($feature, $method) && is_callable([$feature, $method])) {
                        return call_user_func([$feature, $method], ...$arguments);
                    }
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }
        return parent::callMagicCall($method, $arguments);
    }
    
    
    
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
     * @return $this|FeatureSet
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