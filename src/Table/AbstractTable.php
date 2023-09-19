<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Table;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Having;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use Ruga\Db\Adapter\Adapter;
use Ruga\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\Feature\FeatureSet;
use Laminas\Db\TableGateway\TableGateway;
use Ruga\Db\ResultSet\ResultSet;
use Ruga\Db\Row\AbstractRow;
use Ruga\Db\Row\RowInterface;
use Ruga\Db\Table\Feature\RowGatewayFeature;

/**
 * Class AbstractTable
 */
abstract class AbstractTable extends TableGateway implements TableAttributesInterface, TableInterface
{
    /**
     * Defines the table name in the data base.
     *
     * @var string
     */
    const TABLENAME = null;
    
    /**
     * Primary keys. Must be an array with all the primary keys.
     *
     * @var array
     */
    const PRIMARYKEY = null;
    
    /**
     * The class used as result set.
     */
    const RESULTSETCLASS = ResultSet::class;
    
    /**
     * The class used as row.
     */
    const ROWCLASS = AbstractRow::class;
    
    /**
     * This map describes the relations to other tables.
     */
    const REFERENCEMAP = [];
    
    /**
     * Tables, which hold relations to this table.
     */
    const DEPENDENTTABLES = [];
    
    /**
     * Stores the name of the schema.
     *
     * @var string
     */
    private $schema;
    
    /**
     * @var AdapterInterface
     */
    protected $adapter = null;
    
    
    
    /**
     * AbstractTable constructor.
     * To initialize object refer to {@link init()}.
     *
     * @param AdapterInterface $adapter
     *
     * @param FeatureSet|null  $features
     */
    public function __construct(
        AdapterInterface $adapter,
        FeatureSet $features = null
    ) {
//        \Ruga\Log::functionHead($this);
        
        if (empty(static::TABLENAME)) {
            throw new Exception\ConstantMissingException(
                get_called_class() . "::TABLENAME must be set to table name in data base (string)."
            );
        }
        if (empty(static::PRIMARYKEY) || !is_array(static::PRIMARYKEY)) {
            throw new Exception\ConstantMissingException(
                get_called_class(
                ) . "::PRIMARYKEY must be an array of primary keys in table '" . static::TABLENAME . "'"
            );
        }
        
        $this->adapter = $adapter;
        
        if (!$features) {
            $features = new FeatureSet();
        }
        $features = $this->initFeatures($features);
        
        /** @noinspection PhpParamsInspection */
        parent::__construct(
            static::TABLENAME,
            $adapter,
            $features
                ->addFeature(
                    new RowGatewayFeature(
                        (new ReflectionClass(static::ROWCLASS))->newInstance(
                            static::PRIMARYKEY,
                            static::TABLENAME,
                            $adapter,
                            $this
                        )
                    )
                )
//                ->addFeature(new \Ruga\Db\Table\Feature\MetadataFeature())
                ->setTableGateway($this),
            (new ReflectionClass(static::RESULTSETCLASS))->newInstance(),
            null
        );
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
     * Initialize object.
     * Called from {@link AbstractTable::__construct()} as final step of object instantiation.
     */
    public function init()
    {
    }
    
    
    
    /**
     * @param null $key
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function info($key = null)
    {
//        \Ruga\Log::functionHead($this);
        
        $info = [
            'schema' => $this->getSchema(),
            'name' => $this->getTable(),
            'cols' => $this->getColumns(),
            'primary' => static::PRIMARYKEY,
            'metadata' => $this->getMetadata(),
            'rowClass' => static::ROWCLASS,
            'rowsetClass' => static::RESULTSETCLASS,
//            'referenceMap' => $this->_referenceMap,
//            'dependentTables' => $this->_dependentTables,
//            'sequence' => $this->_sequence,
        ];
        
        if ($key === null) {
            return $info;
        }
        
        if (!array_key_exists($key, $info)) {
            throw new Exception\InvalidArgumentException("'{$key}' is not a valid key.");
        }
        
        return $info[$key];
    }
    
    
    
    /**
     * @return string
     * @throws \Exception
     */
    public function getSchema()
    {
//        $this->getMetadata();
        return $this->schema;
    }
    
    
    
    /**
     * Create a new row and initialize it with default values from data base.
     * Uses the row gateway prototype from RowGatewayFeature if it exists.
     *
     * @param array $rowData
     *
     * @return RowInterface
     * @throws \Exception
     */
    public function createRow(array $rowData = []): RowInterface
    {
        /** @var RowGatewayFeature $rowGatewayFeature */
        $rowGatewayFeature = $this->getFeatureSet()->getFeatureByClassName(
            RowGatewayFeature::class
        );
        if ($rowGatewayFeature) {
            $ao = clone $rowGatewayFeature->getRowGatewayPrototype();
        } else {
            $ao = (new ReflectionClass(static::ROWCLASS))->newInstance(
                static::PRIMARYKEY,
                static::TABLENAME,
                $this->adapter,
                $this
            );
        }
        
        $ao->populate($rowData);
        return $ao;
    }
    
    
    
    /**
     * Find the row and return an instance.
     * Returns null if $id is not found or not unique. Returns the row instance if exactly one row was found.
     *
     * @param int|array|string $id
     *
     * @return AbstractRow|RowInterface|null
     * @throws \Exception
     * @see Adapter::rowFactory()
     *
     */
    static public function factory($id)/*: ?RowInterface*/
    {
        \Ruga\Log::functionHead();
        
        /** @var Adapter $adapter */
        $adapter = GlobalAdapterFeature::getStaticAdapter();
        return $adapter->rowFactory($id, get_called_class());
    }
    
    
    
    /**
     * Find rows by primary key(s).
     * If primary key is a compound key, you have to give an array of arrays
     * with the exact number of values as the key components.
     *
     * @param int|string|RowInterface|array $id
     *
     * @return ResultSet
     * @throws \Exception
     */
    public function findById($id): ResultSetInterface
    {
        // Leave with empty result, if empty $id given
        if (empty($id)) {
            return $this->select("1=2");
        }
        
        // Create array if no array given
        if (!is_array($id)) {
            $id = [$id];
        }
        
        $shortClassName = (new \ReflectionClass($this))->getShortName();
        $isUniqueid = null;
        /**
         * Parse the given key. Checks if object or uniqueid or plain.
         *
         * @param $uniquid
         *
         * @return false|mixed|string[]|null
         */
        $isUniqueid = function ($uniquid) use ($shortClassName, &$isUniqueid) {
            $matches = null;
            if (is_object($uniquid) && ($uniquid instanceof RowInterface)) {
                // RowInterface object given: check if class matches and return primary key data
                // If multi key, return an array
                /** @var AbstractRow $uniquid */
                $shortClassNameOfId = (new \ReflectionClass($uniquid->getTableGateway()))->getShortName();
                if ($shortClassNameOfId != $shortClassName) {
                    return null;
                }
                $pk = array_values($uniquid->getPrimaryKeyData());
                return (count($pk) > 1 ? $pk : $pk[0]) ?? null;
            } elseif (is_string($uniquid) && (preg_match('/^([\w\-]+)@([A-Z]\w*)$/', $uniquid, $matches) === 1)) {
                // uniqueid given: check if class matches and return primary key data
                // If multi key, return an array
                if ($matches[2] != $shortClassName) {
                    return null;
                }
                return strpos($matches[1], '-') ? explode('-', $matches[1]) : $matches[1];
            } else {
                // not a uniqueid: return value as is
                return $uniquid;
            }
        };
        
        // create sql
        $str = '(' . implode(
                ', ',
                array_map(
                    function (string $pk_name) {
                        return $this->adapter->getPlatform()->quoteIdentifier($pk_name);
                    },
                    static::PRIMARYKEY
                )
            ) . ')';
        $str .= ' IN ';
        // IN does evaluate the values based on the type of the left-hand-side expression.
        
        $str .= '(' . implode(
                ', ',
                array_filter(
                    array_map(
                        function ($val) use ($isUniqueid) {
                            $val = $isUniqueid($val);
                            if ($val === null) {
                                return null;
                            } elseif (is_array($val)) {
                                $numberOfValues = count($val);
                                $val = '(' . $this->adapter->getPlatform()->quoteValueList($val) . ')';
                            } else {
                                $numberOfValues = 1;
//                                if ($val = $isUniqueid($val)) {
                                $val = $this->adapter->getPlatform()->quoteValue($val);
//                                }
                            }
                            if ($numberOfValues != count(static::PRIMARYKEY)) {
                                throw new Exception\InvalidArgumentException(
                                    "Number of values given ({$numberOfValues}) does not match number of primary keys (" . implode(
                                        ', ',
                                        static::PRIMARYKEY
                                    ) . ")"
                                );
                            }
                            return $val;
                        },
                        $id
                    )
                )
            ) . ')';

//        \Ruga\Log::log_msg("\$sql={$str}");
        
        return $this->select($str);
    }
    
    
    
    public function getAdapter()
    {
        return $this->adapter;
    }
    
    
    
    public function getMetadataCache(): StorageInterface
    {
        return $this->metadataCache;
    }
    
    
    
    /**
     * Manipulates the given Select based on the $customSqlSelectName and the $request. This can be useful to generate
     * customized queries including joins and additional/calculated columns.
     * Does nothing by default.
     *
     * @param string                 $customSqlSelectName
     * @param Select                 $select
     * @param ServerRequestInterface $request
     */
    public function customizeSqlSelectFromRequest(
        string $customSqlSelectName,
        Select $select,
        ServerRequestInterface $request
    ) {
    }
    
    
    
    /**
     * Applies the given filter to a complete Select. This can be used to narrow down a resultset generated by a else
     * complete query.
     *
     * @param array  $filter
     * @param Select $select
     *
     * @return void
     */
    public function applyFilterToSqlSelect(array &$filter, Select $select)
    {
        $select->having(
            function (Having $having) use ($filter) {
                $formFilter = $having->NEST;
                foreach ($filter as $name => $item) {
                    if (is_array($item) && !empty($item)) {
                        $formFilter->in($name, $item);
                    } elseif (is_string($item)) {
                        $formFilter->equalTo($name, $item);
                    }
                }
                if ($formFilter->count() == 0) {
                    $formFilter->expression('TRUE', []);
                }
            }
        );
    }
    
    
}