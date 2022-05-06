<?php

declare(strict_types=1);

namespace Ruga\Db\Table\Feature;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\Cache\StorageFactory;
use Laminas\Db\Metadata\MetadataInterface;
use Laminas\Db\Metadata\Object\ColumnObject;
use Laminas\Db\Metadata\Object\TableObject;
use Laminas\Db\Metadata\Source\Factory as SourceFactory;
use Laminas\Db\Sql\TableIdentifier;
use Ruga\Db\Table\Exception\RuntimeException;
use Ruga\Db\Table\TableInterface;

class MetadataFeature extends AbstractFeature
{
    /**
     * @var MetadataInterface|null
     */
    protected $metadata = null;
    
    /**
     * @var StorageInterface|null
     */
    private $cache;
    
    /**
     * @var StorageInterface[]
     */
    private static $defaultCaches = [];
    
    
    
    /**
     * Constructor
     *
     * @param MetadataInterface     $metadata
     * @param StorageInterface|null $cache
     */
    public function __construct(MetadataInterface $metadata = null, StorageInterface $cache = null)
    {
        if ($metadata) {
            $this->metadata = $metadata;
        }
        $this->sharedData['metadata'] = [
            'primaryKey' => null,
            'columns' => [],
        ];
        
        $this->cache = $cache;
    }
    
    
    
    public function postInitialize()
    {
//        \Ruga\Log::functionHead($this);
        
        
        // localize variable for brevity
        /** @var TableInterface $t */
        $t = $this->tableGateway;
        
        
        // Check cache
        $cacheKey = str_replace(['\\', '/'], '_', implode('-', [self::class, 'metadata', $t->getSchema(), $t->getTable(), $t->getAdapter()->getDbHash()]));
//        \Ruga\Log::log_msg("--------- cacheKey: {$cacheKey}");
        if ($metadataFromCache = $this->getCache()->getItem($cacheKey)) {
//            \Ruga\Log::log_msg("--------- cache HIT");
            $this->sharedData['metadata'] = $metadataFromCache;
            $t->columns = array_keys($this->sharedData['metadata']['columns']);
            return;
        }
//        \Ruga\Log::log_msg("--------- cache MISS");
        
        
        if ($this->metadata === null) {
            $this->metadata = SourceFactory::createSourceFromAdapter($this->tableGateway->adapter);
        }
        $m = $this->metadata;
        
        $tableGatewayTable = is_array($t->table) ? current($t->table) : $t->table;
        
        if ($tableGatewayTable instanceof TableIdentifier) {
            $table = $tableGatewayTable->getTable();
            $schema = $tableGatewayTable->getSchema();
        } else {
            $table = $tableGatewayTable;
            $schema = null;
        }
        
        
        // get columns
        $columns = $m->getColumns($table, $schema);
        /** @var ColumnObject $column */
        $column=null;
        foreach ($columns as $column) {
            $a = [
                'NAME' => $column->getName(),
                'DATA_TYPE' => $column->getDataType(),
                'DEFAULT' => $column->getColumnDefault(),
                'ISNULLABLE' => $column->getIsNullable(),
                'ISPRIMARY' => false,
                'PRIMARY_POSITION' => null,
//                'IDENTITY' => false,
            ];
            
            if (($a['DEFAULT'] === null) && !$a['ISNULLABLE']) {
                unset($a['DEFAULT']);
            }
            
            $this->sharedData['metadata']['columns'][$a['NAME']] = $a;
        }
        $this->sharedData['metadata']['schema'] = $schema;
        if($column) $this->sharedData['metadata']['schema'] = $column->getSchemaName();
//        $t->schema = $this->sharedData['metadata']['schema'];
        $t->columns = array_keys($this->sharedData['metadata']['columns']);
        
        // get constraint keys
        foreach ($m->getConstraintKeys($table, $schema) as $constraintkey) {
            /** @var $constraintkey \Laminas\Db\Metadata\Object\ConstraintKeyObject */
            $a = [
                'COLUMN' => $constraintkey->getColumnName(),
                'REF_SCHEMA' => $constraintkey->getReferencedTableSchema(),
                'REF_TABLE' => $constraintkey->getReferencedTableName(),
                'REF_COLUMN' => $constraintkey->getReferencedColumnName(),
            ];
            $this->sharedData['metadata']['constraintkeys'][$a['COLUMN']] = $a;
        }
        
        
        // get constraints
        $pkc = null;
        foreach ($m->getConstraints($table, $schema) as $constraint) {
            /** @var $constraint \Laminas\Db\Metadata\Object\ConstraintObject */
            if ($constraint->getType() == 'PRIMARY KEY') {
                $pkc = $constraint;
            }
            $a = [
                'NAME' => $constraint->getName(),
                'TYPE' => $constraint->getType(),
                'COLUMNS' => $constraint->getColumns(),
                'REF_SCHEMA' => $constraint->getReferencedTableSchema(),
                'REF_TABLE' => $constraint->getReferencedTableName(),
                'REF_COLUMNS' => $constraint->getReferencedColumns(),
            ];
            $this->sharedData['metadata']['constraints'][$a['NAME']] = $a;
        }
        
        
        // get primary key(s)
        // process primary key only if table is a table; there are no PK constraints on views
        if (($m->getTable($table, $schema) instanceof TableObject)) {
            if ($pkc === null) {
                throw new RuntimeException(
                    'A primary key for table ' . get_class($this->tableGateway) . ' could not be found in the metadata.'
                );
            }
            
            $pkcColumns = $pkc->getColumns();
            if (count($pkcColumns) === 1) {
                $primaryKey = $pkcColumns[0];
            } else {
                $primaryKey = $pkcColumns;
            }
            
            // set flag in column list
            foreach ($pkcColumns as $idx => $pkColName) {
                $this->sharedData['metadata']['columns'][$pkColName]['ISPRIMARY'] = true;
                $this->sharedData['metadata']['columns'][$pkColName]['PRIMARY_POSITION'] = $idx;
            }
            
            $this->sharedData['metadata']['primaryKey'] = $primaryKey;
        }
        $this->getCache()->setItem($cacheKey, $this->sharedData['metadata']);
    }
    
    
    
    public function getMetadata()
    {
        return $this->sharedData['metadata'];
    }
    
    
    
    private function getCache(): StorageInterface
    {
        if ($this->cache) {
//            self::setGlobalMetadataCache($this->cache);
            return $this->cache;
        }
        return self::getGlobalMetadataCache();
    }
    
    
    
    /**
     * Get global default cache
     *
     * @param string|null $class
     *
     * @return StorageInterface
     * @todo Use memory or blackhole cache
     */
    public static function getGlobalMetadataCache(string $class = null)
    {
        if (!$class) {
            $class = get_called_class();
        }
        
        // class specific adapter
        if (isset(static::$defaultCaches[$class])) {
            return static::$defaultCaches[$class];
        }
        
        // default adapter
        if (isset(static::$defaultCaches[__CLASS__])) {
            return static::$defaultCaches[__CLASS__];
        }

        $cache = StorageFactory::factory(
            [
                'adapter' => [
                    'name' => \Laminas\Cache\Storage\Adapter\Memory::class,
                    'options' => [
                        'ttl' => PHP_INT_MAX,
                    ],
                ],
                'plugins' => [
                    'exception_handler' => [
                        'throw_exceptions' => false,
                    ],
                    'serializer' => [],
                ],
            ]
        );
        self::setGlobalMetadataCache($cache, $class);
        return $cache;
    }
    
    
    
    /**
     * Set global default cache
     *
     * @param StorageInterface $metadataCache
     * @param string|null      $class
     */
    public static function setGlobalMetadataCache(StorageInterface $metadataCache, string $class = null)
    {
        if (!$class) {
            $class = get_called_class();
        }
        
        static::$defaultCaches[$class] = $metadataCache;
        if ($class === __CLASS__) {
            static::$defaultCaches[__CLASS__] = $metadataCache;
        }
    }
    
    
}