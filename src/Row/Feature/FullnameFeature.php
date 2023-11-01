<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Ruga\Db\Row\Exception\InvalidArgumentException;
use Ruga\Db\Row\Exception\ReadonlyArgumentException;
use Ruga\Db\Table\Feature\MetadataFeature;

/**
 * The fullname feature adds the following attributes to the row:
 */
class FullnameFeature extends AbstractFeature implements FullnameFeatureAttributesInterface
{
    /** @var bool */
    private $hasFullnameColumn;
    
    
    
    /**
     * Construct the FullnameFeature.
     *
     * @param bool|null $hasFullnameColumn
     */
    public function __construct(bool $hasFullnameColumn = null)
    {
        $this->hasFullnameColumn = $hasFullnameColumn;
    }
    
    
    public function postInitialize()
    {
        // Check if row class is an implementation of FullnameFeatureRowInterface
        if (!($this->rowGateway instanceof FullnameFeatureRowInterface)) {
            throw new \Exception(
                "Row gateway " . get_class($this->rowGateway) . " must implement " . FullnameFeatureRowInterface::class
            );
        }
    }
    
    
    
    public function postPopulate()
    {
        
        // Check if row class has information about the existence of the fullname column
        if ($this->hasFullnameColumn === null) {
            /** @var MetadataFeature $metadataFeature */
            $metadataFeature = $this->rowGateway->getTableGateway()->getFeatureSet()->getFeatureByClassName(
                MetadataFeature::class
            );
            if (!$metadataFeature) {
                throw new InvalidArgumentException(
                    self::class . " needs to know if column 'fullname' exists. Either give information to constructor or add '" . MetadataFeature::class . "' to the table gateway"
                );
            }
            $this->hasFullnameColumn=array_key_exists('fullname', $metadataFeature->getMetadata()['columns']);
        }
    
    }
    
    
    
    public function preSave()
    {
        
        // Save fullname to fullname column if it exists
        if ($this->hasFullnameColumn) {
            $this->rowGateway->offsetSet('fullname', $this->rowGateway->getFullname());
        }
    }
    
    
    
    public function preToArray(array &$dataarray)
    {
        
        $dataarray['PK'] = $this->PK;
        $dataarray['idname'] = $this->idname;
        $dataarray['uniqueid'] = $this->uniqueid;
        $dataarray['type'] = $this->type;
        $dataarray['fullname'] = $this->fullname;
    }
    
    
/*    public function preOffsetSet($offset, &$value)
    {
        switch ($offset) {
            case 'PK':
            case 'row_id':
            case 'idname':
            case 'uniqueid':
            case 'type':
            case 'fullname':
                throw new ReadonlyArgumentException(
                    "Attribute '{$offset}' is read-only in '" . get_called_class() . "'."
                );
                break;
        }
    }*/
    
    
    public function __get($name)
    {
        
        switch ($name) {
            case 'PK':
            case 'row_id':
                return implode('-', $this->rowGateway->primaryKeyData ?? []);
                break;
            
            case 'idname':
                return "[{$this->PK}] \"{$this->fullname}\"";
                break;
            
            case 'uniqueid':
                return (empty($this->PK) ? ('?'.spl_object_hash($this)) : $this->PK) . "@{$this->type}";
                break;
            
            case 'type':
                return (new \ReflectionClass($this->rowGateway->getTableGateway()))->getShortName();
                break;
            
            case 'fullname':
//                if ($this->rowGateway->offsetExists('fullname')) {
//                    return $this->rowGateway->offsetGet('fullname');
//                } else {
                return $this->rowGateway->getFullname();
//                }
                break;
        }
        
        return parent::__get($name);
    }
    
    
    
    public function __set($name, $value)
    {
        
        switch ($name) {
            case 'PK':
            case 'row_id':
            case 'idname':
            case 'uniqueid':
            case 'type':
            case 'fullname':
                throw new ReadonlyArgumentException(
                    "Attribute '{$name}' is read-only in '" . get_called_class() . "'."
                );
                break;
        }
        
        parent::__set($name, $value);
    }
    
    
    
}