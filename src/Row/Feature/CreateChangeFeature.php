<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Ruga\Db\Row\Exception\NoDefaultValueException;
use Ruga\Db\Row\Exception\ReadonlyArgumentException;

class CreateChangeFeature extends AbstractFeature implements CreateChangeFeatureAttributesInterface
{
    /**
     * @var int
     */
    private $user_id;
    
    
    
    public function __construct(int $user_id = 1)
    {
        $this->user_id = $user_id;
    }
    
    public function prePopulate(array &$rowData, bool &$rowExistsInDatabase)
    {
        $now=(new \DateTimeImmutable())->format('Y-m-d H:i:s');
        if(!array_key_exists('created', $rowData)) $rowData['created']=$now;
        if(!array_key_exists('createdBy', $rowData)) $rowData['createdBy']=$this->user_id;
        if(!array_key_exists('changed', $rowData)) $rowData['changed']=$now;
        if(!array_key_exists('changedBy', $rowData)) $rowData['changedBy']=$this->user_id;
    }
    
    
    public function preSave()
    {
//        \Ruga\Log::functionHead();
        
        $changed = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->rowGateway->offsetSet('changed', $changed);
        $this->rowGateway->offsetSet('changedBy', $this->user_id);
        if ($this->rowGateway->isNew()) {
            $this->rowGateway->offsetSet('created', $changed);
            $this->rowGateway->offsetSet('createdBy', $this->user_id);
        }
    }
    
    
    
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
        switch ($name) {
            case 'changedBy':
            case 'createdBy':
                try {
                    return $this->rowGateway->offsetGet($name);
                } catch (NoDefaultValueException $e) {
                    return $this->user_id;
                }
                break;
            
            case 'changed':
            case 'created':
                try {
                    return $this->rowGateway->offsetGet($name);
                } catch (NoDefaultValueException $e) {
                    return new \DateTimeImmutable();
                }
                break;
        }
        
        return parent::__get($name);
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
        switch ($name) {
            case 'changed':
            case 'changedBy':
            case 'created':
            case 'createdBy':
                throw new ReadonlyArgumentException(
                    "Attribute '{$name}' is read-only in '" . get_called_class() . "'."
                );
                break;
        }
        
        parent::__set($name, $value);
    }
    
}