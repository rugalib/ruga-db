<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\Exception\InvalidArgumentException;

class AbstractFeature extends \Laminas\Db\RowGateway\Feature\AbstractFeature
{
    /**
     * @var AbstractRow
     */
    protected $rowGateway = null;
    
    
    
    /**
     * Get the value of the attribute $name. If attribute is not valid, throw InvalidArgumentException.
     *
     * @param string $name
     *
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        // We don't call parents (\Laminas\Db\RowGateway\AbstractRowGateway) __get because it requests data directly from the data array.
        // We want to retrieve data via static::offsetGet() to make use of the data type functions.
//        if ($this->rowGateway->offsetExists($name)) {
//            return $this->rowGateway->offsetGet($name);
//        } else {
        throw new InvalidArgumentException(
            "Attribute '{$name}' is unknown in '" . get_called_class() . "' for row '" . get_class(
                $this->rowGateway
            ) . "'."
        );
//        }
    }
    
    
    
    /**
     * __set
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        throw new InvalidArgumentException(
            "Attribute '{$name}' is unknown in '" . get_called_class() . "'."
        );
    }
    
    
    
    /**
     * Check, if attribute $name is valid for the feature.
     *
     * @param string $name
     *
     * @return bool
     * @throws \Exception
     */
    public function __isset($name)
    {
        try {
            $this->__get($name);
            return true;
        } catch (\Exception $e) {
            if (!($e instanceof InvalidArgumentException)) {
                \Ruga\Log::addLog($e);
            }
        }
        return false;
    }
    
    
    /*
    
    public function preInitialize();
    public function postInitialize();

    public function prePopulate(array &$rowData, bool &$rowExistsInDatabase);
    public function postPopulate();
    
    public function preSave();
    public function postSave();
    
    public function preInsert();
    public function postInsert();

    public function preUpdate();
    public function postUpdate();

    public function preOffsetSet($offset, &$value);

    public function preToArray(array &$dataarray);
    public function postToArray(array &$dataarray);
    
    */
}