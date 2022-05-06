<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Ruga\Db\Row\Exception\InvalidTenantException;

class TenantFeature extends AbstractFeature
{
    /** @var mixed|null */
    private $tenant_id;
    
    /** @var string */
    private $tenant_id_column;
    
    
    
    public function __construct($tenant_id, string $tenant_id_column = 'Tenant_id')
    {
        $this->tenant_id = $tenant_id;
        $this->tenant_id_column = $tenant_id_column;
    }
    
    
    
    public function postPopulate()
    {
        if (!array_key_exists($this->tenant_id_column, $this->rowGateway->data)) {
            $this->rowGateway->__set($this->tenant_id_column, $this->tenant_id);
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
            case $this->tenant_id_column:
                return $this->rowGateway->offsetGet($name);
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
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        if ($name == $this->tenant_id_column) {
            if (($this->tenant_id !== null) && ($value !== null) && ($value != $this->tenant_id)) {
                throw new InvalidTenantException(
                    "You are not allowed to save a row for another tenant. Only your own or the default tenant are allowed."
                );
            }
            $this->rowGateway->offsetSet($name, $value);
        }
        
        parent::__set($name, $value);
    }
    
    
}