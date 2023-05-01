<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\Exception\InvalidArgumentException;

/**
 * @method preInitialize()
 * @method postInitialize()
 * @method prePopulate(array &$rowData, bool &$rowExistsInDatabase)
 * @method postPopulate()
 * @method void startSave() First event if save() is called.
 * @method void preSave() Event is called before saving, but after startSave().
 * @method void preUpdate() Called before UPDATE command is issued.
 * @method void preInsert() Called before INSERT command is issued.
 * @method void catchSaveException(\Throwable $exception) Called immediately after exception has been thrown.
 * @method void postUpdate() Called after UPDATE command was issued.
// * @method void postUpdateException(\Throwable $exception) Called after UPDATE command, when exception occurred.
 * @method void postInsert() Called after INSERT command was issued.
// * @method void postInsertException(\Throwable $exception) Called after INSERT command, when exception occurred.
 * @method void postSave() Called after saving.
// * @method void postSaveException(\Throwable $exception) Called after saving, when exception occurred.
 * @method void endSave() Called after saving and after postSave().
// * @method void endSaveException(\Throwable $exception) Called after saving and after postSave(), when exception occurred.
 * @method preOffsetSet($offset, &$value)
 * @method preToArray(array &$dataarray)
 * @method postToArray(array &$dataarray)
 */
class AbstractFeature extends \Laminas\Db\RowGateway\Feature\AbstractFeature
{
    /**
     * @var AbstractRow
     */
//    protected $rowGateway = null;
    
    
    
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
    
    
    
    */
}