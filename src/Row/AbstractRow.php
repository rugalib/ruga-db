<?php

namespace Ruga\Db\Row;

use Laminas\Db\RowGateway\RowGateway;
use Laminas\Db\Sql\Expression;
use Ruga\Db\Row\Feature\FeatureSet;
use Ruga\Db\Row\Feature\FullnameFeature;
use Ruga\Db\Row\Feature\FullnameFeatureRowInterface;
use Ruga\Db\Table\AbstractTable;
use Ruga\Db\Table\Feature\MetadataFeature;
use Ruga\Db\Table\TableInterface;

/**
 * Class AbstractRow.
 */
abstract class AbstractRow extends RowGateway implements RowAttributesInterface,
                                                         RowInterface /*, ArraySerializableInterface */
{
    private TableInterface $tableGateway;
    
    
    
    /**
     * Construct the row object. Calls initFeatures() before giving control to parent.
     *
     * @param                $primaryKeyColumn
     * @param                $tableName
     * @param                $adapterOrSql
     * @param TableInterface $tableGatewayObject
     *
     * @throws \Exception
     */
    final public function __construct(
        $primaryKeyColumn,
        $tableName,
        $adapterOrSql,
        TableInterface $tableGatewayObject
    ) {
        $this->tableGateway = $tableGatewayObject;
        $this->featureSet = $this->initFeatures(new FeatureSet());
        parent::__construct($primaryKeyColumn, $tableName, $adapterOrSql);
    }
    
    
    
    /**
     * Add features to the row class before it is initialized by the parent.
     *
     * @param FeatureSet $featureSet
     *
     * @return FeatureSet
     */
    protected function initFeatures(FeatureSet $featureSet): FeatureSet
    {
        return $featureSet;
    }
    
    
    
    /**
     * Persist the row.
     * Actually only applies preSave and postSave to the registered features and call parents save().
     *
     * @return int Affected Rows
     * @throws \Exception
     */
    public function save()
    {
        \Ruga\Log::functionHead($this);
        $rowExistsInDatabase = null;
        try {
            $this->featureSet->apply('startSave', []);
            
            $rowExistsInDatabase = $this->rowExistsInDatabase();
            
            $this->featureSet->apply('preSave', []);
            if ($rowExistsInDatabase) {
                $this->featureSet->apply('preUpdate', []);
            } else {
                $this->featureSet->apply('preInsert', []);
            }
            
            // Save and return affected rows
            $retval=parent::save();
            
            if ($rowExistsInDatabase) {
                $this->featureSet->apply('postUpdate', []);
            } else {
                $this->featureSet->apply('postInsert', []);
            }
            $this->featureSet->apply('postSave', []);
            
            return $retval;
        } catch (\Exception $e) {
            $this->featureSet->apply('catchSaveException', [$e]);
            // Throw exception, if something went wrong
            throw $e;
        } finally {
            $this->featureSet->apply('endSave', []);
        }
    }
    
    
    
    /**
     * Returns the associated table gateway object.
     *
     * @return AbstractTable
     */
    final public function getTableGateway(): AbstractTable
    {
        return $this->tableGateway;
    }
    
    
    
    /**
     * Populate Data and call the row initializer functions.
     *
     * @param array $rowData
     * @param bool  $rowExistsInDatabase
     *
     * @return self Provides a fluent interface
     * @throws \Exception
     * @todo Naming of this functions is not ideal, as there are too many "init" functions serving different
     *       purposes.
     *
     */
    public function populate(array $rowData, $rowExistsInDatabase = false)
    {
        $this->featureSet->apply('prePopulate', [&$rowData, &$rowExistsInDatabase]);
        
        
        $metadataFeature = $this->getTableGateway()->getFeatureSet()->getFeatureByClassName(MetadataFeature::class);
        if ($metadataFeature) {
            // We do not call parent::populate() because it does not handle data properly
            $this->initialize();
            
            // Instead of just copying the array to $this->data,
            // call the proper setters.
            foreach ($rowData as $key => $val) {
                if ($this->offsetExists($key)) {
                    $this->offsetSet($key, $val);
                }
            }
            
            if ($rowExistsInDatabase == true) {
                $this->processPrimaryKeyData();
            } else {
                $this->primaryKeyData = null;
            }
        } else {
            // Use parent::populate() as we do not have information about the data types
            parent::populate($rowData, $rowExistsInDatabase);
        }
        
        
        $this->featureSet->setRowGateway($this);
        $this->featureSet->apply('postPopulate', []);
        
        // Call the old init functions
        $this->init();
        if ($this->isNew()) {
            $this->initNewRow();
        }
        
        return $this;
    }
    
    
    
    /**
     * Initialize the data store with default values from database meta data.
     *
     * @todo We should provide this functionality with a feature. See #8.
     *
     * @throws \Exception
     */
    /*    final private function initDefaultValues()
        {
            if (!$this->getTableGateway()->isInitialized()) {
                return;
            }
            $metadata = $this->getTableGateway()->getMetadata();
            foreach ($metadata['columns'] as $col) {
                if (!isset($this->data[$col['NAME']])) {
                    $this->data[$col['NAME']] = $col['DEFAULT'];
                }
            }
        }*/
    
    
    /**
     * This function is called upon initialization of the row object,
     * after the data was populated. It is also called after safe and
     * refresh.
     *
     * @todo Naming of this functions is not ideal, as there are too many "init" functions serving different
     *       purposes.
     * @deprecated
     */
    protected function init()
    {
    }
    
    
    
    /**
     * This function is called upon initialization of the row object,
     * after the data was populated. Only for new created objects.
     *
     * @todo Naming of this functions is not ideal, as there are too many "init" functions serving different
     *       purposes.
     * @deprecated
     */
    protected function initNewRow()
    {
    }
    
    
    
    /**
     * Returns true, if the row is not yet saved to the data base.
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return !$this->rowExistsInDatabase();
    }
    
    
    
    /**
     * Returns true, if the row is disabled.
     *
     * @return bool
     * @todo More detail
     *
     */
    public function isDisabled(): bool
    {
        return false;
    }
    
    
    
    /**
     * Returns true, if the row is read-only.
     *
     * @return bool
     * @todo More detail
     *
     */
    public function isReadOnly(): bool
    {
        return false;
    }
    
    
    
    /**
     * Create an array representation of the data in the row.
     *
     * @inheritDoc
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        $dataarray = [];
        $this->featureSet->apply('preToArray', [&$dataarray]);
        
        // Get the native row data
        // Not using the values here, because parent::toArray() simply copies the $data array.
        // We want to retrieve data using self::offsetGet().
        $aB = parent::toArray();
        foreach ($aB as $name => $val) {
            $aB[$name] = $this->offsetGet($name);
        }
        $dataarray = array_merge($dataarray, $aB);
        
        $this->featureSet->apply('postToArray', [&$dataarray]);
        
        return $dataarray;
    }
    
    
    
    /**
     * Returns the data from self::toArray() as JSON.
     *
     * @param int $options The json_encode options @see https://www.php.net/manual/en/function.json-encode.php
     *
     * @return string
     * @throws \Exception
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
    
    
    
    /**
     * @return array
     * @throws \Exception
     * @todo Why do we need this?
     */
    public function getArrayCopy()
    {
        return $this->toArray();
    }
    
    
    
    /**
     * @param mixed $array
     *
     * @return \Laminas\Db\RowGateway\AbstractRowGateway
     * @todo Why do we need this?
     */
    public function exchangeArray($array)
    {
        return parent::exchangeArray($array);
    }
    
    
    
    /**
     * Get the value of the attribute $name. If attribute is not valid, throw InvalidColumnException.
     *
     * @param string $name
     *
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if ($this->featureSet->canCallMagicGet($name)) {
            return $this->featureSet->callMagicGet($name);
        }
        
        // We don't call parents __get because it requests data directly from the data array.
        // We want to retrieve data via static::offsetGet() to make use of the data type functions.
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        } else {
            throw new Exception\InvalidColumnException($name, get_called_class());
        }
    }
    
    
    
    public function __call($name, $arguments)
    {
        if ($this->featureSet && $this->featureSet->canCallMagicCall($name)) {
            return $this->featureSet->callMagicCall($name, $arguments);
        }
        trigger_error(
            'Call to undefined method ' . __CLASS__ . '::' . $name . '(). Missing feature in ' . get_called_class(
            ) . '?',
            E_USER_ERROR
        );
    }
    
    
    
    /**
     * Constructs a display name from the given fields.
     *
     * @return string
     * @throws \Exception
     * @see FullnameFeature
     * @see FullnameFeatureRowInterface
     */
    public function getFullname(): string
    {
        $str = $this->offsetExists('fullname')
            ? $this->offsetGet('fullname')
            : ($this->offsetExists('name') ? $this->offsetGet('name') : $this->uniqueid);
        
        return "{$str}";
    }
    
    
    
    /**
     * Convert bool to database value.
     *
     * @param $offset string Name of the column
     * @param $value  mixed Value to set the column to
     *
     * @return bool
     */
    private function offsetSet_bit($offset, $value): bool
    {
        return boolval($value) ? '1' : '0';
    }
    
    
    
    /**
     * Convert various date/time objects to database string.
     *
     * @param $offset string Name of the column
     * @param $value  mixed Value to set the column to
     *
     * @return string
     */
    private function offsetSet_datetime($offset, $value): string
    {
        if (is_a($value, \DateTime::class)) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif (is_a($value, \DateTimeImmutable::class)) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif (is_a($value, "Zend_Date")) {
            $value = $value->toString('YYYY-MM-dd HH:mm:ss');
        }
        return $value;
    }
    
    
    
    /**
     * Convert various date/time objects to database string.
     *
     * @param $offset string Name of the column
     * @param $value  mixed Value to set the column to
     *
     * @return string
     */
    private function offsetSet_timestamp($offset, $value): string
    {
        if (is_a($value, \DateTime::class)) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif (is_a($value, \DateTimeImmutable::class)) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif (is_a($value, "Zend_Date")) {
            $value = $value->toString('YYYY-MM-dd HH:mm:ss');
        }
        return $value;
    }
    
    
    
    /**
     * Convert various date/time objects to database string.
     *
     * @param $offset string Name of the column
     * @param $value  mixed Value to set the column to
     *
     * @return string
     */
    private function offsetSet_date($offset, $value): string
    {
        if (is_a($value, \DateTime::class)) {
            $value = $value->format('Y-m-d');
        } elseif (is_a($value, \DateTimeImmutable::class)) {
            $value = $value->format('Y-m-d');
        } elseif (is_a($value, "Zend_Date")) {
            $value = $value->toString('YYYY-MM-dd');
        }
        return $value;
    }
    
    
    
    /**
     * Convert various date/time objects to database string.
     *
     * @param $offset string Name of the column
     * @param $value  mixed Value to set the column to
     *
     * @return string
     */
    private function offsetSet_time($offset, $value): string
    {
        if (is_a($value, \DateTime::class)) {
            $value = $value->format('H:i:s');
        } elseif (is_a($value, \DateTimeImmutable::class)) {
            $value = $value->format('H:i:s');
        } elseif (is_a($value, "Zend_Date")) {
            $value = $value->toString('HH:mm:ss');
        }
        return $value;
    }
    
    
    
    /**
     * Convert array to database string.
     *
     * @param $offset string Name of the column
     * @param $value  mixed Value to set the column to
     *
     * @return string
     */
    private function offsetSet_set($offset, $value): string
    {
        if (is_array($value)) {
            $value = implode(
                ',',
                array_map(
                    function ($val) {
                        return $val;
                    },
                    $value
                )
            );
        }
        return $value;
    }
    
    
    
    /**
     * Set a column value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value to set the column to
     *
     * @return \Laminas\Db\RowGateway\AbstractRowGateway
     * @throws \Exception
     */
    
    public function offsetSet($offset, $value)
    {
//        \Ruga\Log::functionHead($this);
        
        if (!$this->offsetExists($offset)) {
            throw new Exception\InvalidColumnException($offset, get_called_class());
        }
        
        $this->featureSet->apply('preOffsetSet', [$offset, &$value]);
        
        if ($value instanceof Expression) {
            return parent::offsetSet($offset, $value);
        }
        
        $metadataFeature = $this->getTableGateway()->getFeatureSet()->getFeatureByClassName(MetadataFeature::class);
        if ($metadataFeature instanceof MetadataFeature) {
            $data_type = $metadataFeature->getMetadata()['columns'][$offset]['DATA_TYPE'] ?? 'unknown';
            $isNullable = $metadataFeature->getMetadata()['columns'][$offset]['ISNULLABLE'] ?? null;
        } else {
            $data_type = 'unknown';
            $isNullable = null;
        }
        $subMethodName = "offsetSet_{$data_type}";
        
        if ($isNullable && (($value === null) || ($value === ''))) {
            $value = null;
        } elseif (method_exists($this, $subMethodName) && is_callable([$this, $subMethodName])) {
            $value = call_user_func([$this, $subMethodName], $offset, $value);
        }
        
        return parent::offsetSet($offset, $value);
    }
    
    
    
    /**
     * Checks, if the field exists. First checks the table columns, but this is only filled if the table
     * uses the {@link MetadataFeature}. Falls back to the old (unreliable) method of checking the data
     * attribute.
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (in_array($offset, $this->getTableGateway()->getColumns()) || (count(
                    $this->getTableGateway()->getColumns()
                ) == 0)) {
            return true;
        }
        return parent::offsetExists($offset);
    }
    
    
    
    /**
     * Returns int value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return int
     */
    private function offsetGet_tinyint($offset, $value): int
    {
        return intval($value);
    }
    
    
    
    /**
     * Returns int value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return int
     */
    private function offsetGet_smallint($offset, $value): int
    {
        return intval($value);
    }
    
    
    
    /**
     * Returns int value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return int
     */
    private function offsetGet_mediumint($offset, $value): int
    {
        return intval($value);
    }
    
    
    
    /**
     * Returns int value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return int
     */
    private function offsetGet_int($offset, $value): int
    {
        return intval($value);
    }
    
    
    
    /**
     * Returns int value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return int
     */
    private function offsetGet_bigint($offset, $value): int
    {
        return intval($value);
    }
    
    
    
    /**
     * Returns bool value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return bool
     * @throws \Exception
     */
    private function offsetGet_bit($offset, $value): bool
    {
        $this->offsetSet($offset, $value);
        return boolval($value);
    }
    
    
    
    /**
     * Returns float value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return float
     */
    private function offsetGet_float($offset, $value): float
    {
        return floatval($value);
    }
    
    
    
    /**
     * Returns float value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return float
     */
    private function offsetGet_double($offset, $value): float
    {
        return floatval($value);
    }
    
    
    
    /**
     * Returns float value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return float
     */
    private function offsetGet_decimal($offset, $value): float
    {
        return floatval($value);
    }
    
    
    
    /**
     * Returns json object value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return mixed
     */
    private function offsetGet_json($offset, $value)
    {
        return json_decode($value);
    }
    
    
    
    /**
     * Returns DateTime object value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return mixed
     */
    private function offsetGet_timestamp($offset, $value): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $value,
            new \DateTimeZone(
                'Europe/Zurich'
            )
        );
    }
    
    
    
    /**
     * Returns DateTime object value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return mixed
     */
    private function offsetGet_datetime($offset, $value): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $value,
            new \DateTimeZone(
                'Europe/Zurich'
            )
        );
    }
    
    
    
    /**
     * Returns DateTime object value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return mixed
     */
    private function offsetGet_date($offset, $value): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $value,
            new \DateTimeZone(
                'Europe/Zurich'
            )
        );
    }
    
    
    
    /**
     * Returns DateTime object value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return mixed
     */
    private function offsetGet_time($offset, $value): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat(
            'H:i:s',
            $value,
            new \DateTimeZone(
                'Europe/Zurich'
            )
        );
    }
    
    
    
    /**
     * Returns array value.
     *
     * @param string $offset Name of the column
     * @param mixed  $value  Value of the column
     *
     * @return mixed
     */
    private function offsetGet_set($offset, $value): array
    {
        if ($value === '') {
            return [];
        }
        return explode(',', $value);
    }
    
    
    
    /**
     * Return a value from the data row.
     *
     * @param string $offset
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
//        \Ruga\Log::functionHead($this);
//        \Ruga\Log::log_msg($this->getTableGateway()->getMetadata()['columns'][$offset]['DATA_TYPE']);
        
        $metadataFeature = $this->getTableGateway()->getFeatureSet()->getFeatureByClassName(MetadataFeature::class);
        if ($metadataFeature instanceof MetadataFeature) {
            $data_type = $metadataFeature->getMetadata()['columns'][$offset]['DATA_TYPE'] ?? 'unknown';
            $isNullable = $metadataFeature->getMetadata()['columns'][$offset]['ISNULLABLE'] ?? null;
            
            // Throw an exception if the user tries to request data for rows that have not been set
            // and have no default value
            if ($this->offsetExists($offset) && !array_key_exists($offset, $this->data) && !array_key_exists(
                    $offset,
                    $metadataFeature->getMetadata()['columns'][$offset]
                )) {
                throw new Exception\NoDefaultValueException(
                    "Column '{$offset}' has no default value in '" . get_class(
                        $this
                    ) . "' (Is DefaultValueFeature loaded in row?)."
                );
            }
        } else {
            $data_type = 'unknown';
            $isNullable = null;
        }
        
        // Throw an exception if the user tries to request data for rows that have not been set
        if (!$this->offsetExists($offset) || !array_key_exists($offset, $this->data)) {
            throw new Exception\InvalidColumnException($offset, get_called_class());
        }
        
        // Columns without default value throw a "Undefined index" exception
        $value = parent::offsetGet($offset);
        
        if ($isNullable && ($value === null)) {
            return null;
        }
        
        $subMethodName = "offsetGet_{$data_type}";
        if (method_exists($this, $subMethodName) && is_callable([$this, $subMethodName])) {
            return call_user_func([$this, $subMethodName], $offset, $value);
        }
        
        return $value;
    }
    
    
    
    /**
     * Offset unset
     *
     * @param string $offset
     *
     * @return self Provides a fluent interface
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        // If MetadataFeature exists and there is a default value, reset offset to the default value
        $metadataFeature = $this->getTableGateway()->getFeatureSet()->getFeatureByClassName(MetadataFeature::class);
        if ($metadataFeature instanceof MetadataFeature) {
            if (array_key_exists($offset, $metadataFeature->getMetadata()['columns'])) {
                if (array_key_exists('DEFAULT', $metadataFeature->getMetadata()['columns'][$offset])) {
                    $default = $metadataFeature->getMetadata()['columns'][$offset]['DEFAULT'];
                    $this->offsetSet($offset, $default);
                    return $this;
                }
            } else {
                throw new Exception\InvalidColumnException($offset, get_called_class());
            }
        }
        
        // Not calling parent's offsetUnset(), because it does assign null to the offset.
        // We want to remove the offset instead.
//        return parent::offsetUnset($offset);
        if (array_key_exists($offset, $this->data)) {
            unset($this->data[$offset]);
        } else {
            throw new Exception\InvalidColumnException($offset, get_called_class());
        }
        
        return $this;
    }
    
    
    
    /**
     * @param string $name Name of the column
     * @param mixed  $value Value of the column
     *
     * @return void
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        // call feature's magic __set(), if available
        if ($this->featureSet->canCallMagicSet($name)) {
            $this->featureSet->callMagicSet($name, $value);
            return;
        }
        
        // We don't call parents __set for consistency reason. @see self::__get()
        if ($this->offsetExists($name)) {
            $this->offsetSet($name, $value);
            return;
        } else {
            throw new Exception\InvalidColumnException($name, get_called_class());
        }
        
        // Parent's __set method uses offsetSet()
//        parent::__set($name, $value);
    }
    
    
    
    /**
     * __isset
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
//        if (parent::__isset($name)) {
//            return true;
//        }
        
        try {
            $this->__get($name);
            return true;
        } catch (\Exception $e) {
        }
        
        return false;
    }
    
    
    
    /**
     * @return Feature\FeatureSet
     */
    public function getFeatureSet()
    {
        return $this->featureSet;
    }
    
    
    
    /**
     * Clone the object and create new instances to related objects.
     */
    public function __clone()
    {
        $this->featureSet = clone $this->featureSet;
//        $this->featureSet->setRowGateway($this);
    }
    
    
    
    public function getPrimaryKeyData()
    {
        return $this->primaryKeyData;
    }
}