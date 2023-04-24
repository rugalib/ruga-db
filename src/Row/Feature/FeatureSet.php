<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

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
                    return call_user_func([$feature, $method], ...$arguments);
                } catch (\Exception $e) {
                    if (!($e instanceof InvalidArgumentException)) {
                        throw $e;
                    }
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
    
    
}