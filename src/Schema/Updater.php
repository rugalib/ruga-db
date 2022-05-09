<?php

declare(strict_types=1);

namespace Ruga\Db\Schema;

use Exception;
use Laminas\Db\Metadata\Source\Factory;
use Laminas\Db\Sql\Expression;
use Ruga\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Ddl;
use Laminas\Db\Sql\Ddl\Column;
use Laminas\Db\Sql\Ddl\Constraint;
use Ruga\Db\Schema\Exception\OutOfBoundsException;
use Ruga\Db\Schema\Exception\SchemaUpdateFailedException;

/**
 * Updates the data base schema.
 *
 * @author   Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 */
abstract class Updater
{
    /**
     * @var string Defines the data base table name to store Rugalib information.
     */
    const TBL_OPTION = 'ruga_option';
    const TBL_FIELD_OPTNAME = 'optname';
    const TBL_LENGTH_OPTNAME = 200;
    const TBL_FIELD_OPTVALUE = 'optvalue';
    const TBL_LENGTH_OPTVALUE = 500;
    const TBL_OPTNAME_DBVERSION = 'dbversion';
    const TBL_OPTNAME_DBHASH = 'dbhash';
    const TBL_OPTNAME_DBTAG = 'dbtag';
    
    /**
     * @var string This configuration string sets the required version number of the componentes db schema.
     * The schema is considered incompatilbe if this version differs from the version found in the database table
     * ruga_option
     */
    const CONF_REQUESTED_VERSION = 'requested-dbversion';
    
    /**
     * @var string This configuration string sets the directory where the components schema update files are located.
     */
    const CONF_SCHEMA_DIRECTORY = 'schema-directory';
    
    /**
     * @var string Array containing all the tables this component provides.
     */
    const CONF_TABLES = 'tables';
    
    /**
     * @var string Database tag. Updater checks if this tag is equal to the tag in the database ruga_option table,
     *             before updating the schema.
     */
    const CONF_DBTAG = 'dbtag';
    
    
    /** @var Resolver */
    private static $resolver;
    
    
    
    /**
     * @return bool
     * @throws Exception
     * @deprecated Not implemented.
     * @codeCoverageIgnore
     */
    final public static function checkDbVersion(): bool
    {
        throw new \Exception("NOT IMPLEMENTED!");
    }
    
    
    
    /**
     * Read the database version from database and initialize the version counter if it does not exist.
     *
     * @param Adapter $adapter   Data base adapter
     *
     * @param string  $comp_name Name of the component
     *
     * @return int Current schema version
     */
    public static function getDbVersion(Adapter $adapter, string $comp_name = ''): int
    {
        $tblOptnameDbVersion = implode("_", array_filter([static::TBL_OPTNAME_DBVERSION, $comp_name]));
        
        $select = (new Sql($adapter))->select()
            ->from(static::TBL_OPTION)
            ->where(
                [
                    static::TBL_FIELD_OPTNAME => $tblOptnameDbVersion,
                ]
            );
        $result = $adapter->query($select->getSqlString($adapter->getPlatform()))
            ->execute();
        if (!$o = $result->current()) {
            // Insert version 0 record
            $insert = (new Sql($adapter))->insert()
                ->into(static::TBL_OPTION)
                ->columns(
                    [
                        static::TBL_FIELD_OPTNAME,
                        static::TBL_FIELD_OPTVALUE,
                    ]
                )
                ->values(
                    [
                        static::TBL_FIELD_OPTNAME => $tblOptnameDbVersion,
                        static::TBL_FIELD_OPTVALUE => '0',
                    ]
                );
            $adapter->query($insert->getSqlString($adapter->getPlatform()), Adapter::QUERY_MODE_EXECUTE);
            return 0;
        }
        
        return intval($o[static::TBL_FIELD_OPTVALUE]);
    }
    
    
    
    /**
     * Increment the database version.
     *
     * @param Adapter $adapter Data base adapter
     *
     * @deprecated Not used anymore. The increment is done with the schema update query.
     * @codeCoverageIgnore
     */
    public static function incrementDbVersion(Adapter $adapter, string $comp_name = '')
    {
        $tblOptnameDbVersion = implode("_", array_filter([static::TBL_OPTNAME_DBVERSION, $comp_name]));
        
        $s = (new Sql($adapter))->update()
            ->table(static::TBL_OPTION)
            ->set(
                [
                    static::TBL_FIELD_OPTVALUE => new Expression("`" . static::TBL_FIELD_OPTVALUE . "` + 1"),
                ]
            )
            ->where(
                [
                    static::TBL_FIELD_OPTNAME => $tblOptnameDbVersion,
                ]
            );
        $adapter->query($s->getSqlString($adapter->getPlatform()))
            ->execute();
    }
    
    
    
    /**
     * Return the dbtag. dbtag is a user defined string containing the name of the corresponding application.
     *
     * @param Adapter $adapter
     *
     * @return string|null
     * @throws Exception
     */
    public static function getDbTag(Adapter $adapter)
    {
        try {
            $select = (new Sql($adapter))->select()
                ->from(static::TBL_OPTION)
                ->where(
                    [
                        static::TBL_FIELD_OPTNAME => static::TBL_OPTNAME_DBTAG,
                    ]
                );
            $result = $adapter->query($select->getSqlString($adapter->getPlatform()))
                ->execute();
        } catch (\Exception $e) {
            \Ruga\Log::addLog($e->getMessage());
            return null;
        }
        return $result->current()[static::TBL_FIELD_OPTVALUE] ?? null;
    }
    
    
    
    /**
     * Set the dbtag.
     *
     * @param Adapter $adapter
     * @param string  $dbtag
     *
     * @throws Exception
     * @see Updater::getDbTag()
     */
    public static function setDbTag(Adapter $adapter, string $dbtag)
    {
        $olddbtag = static::getDbTag($adapter);
        if ($olddbtag === null) {
            $insertOrUpdate = (new Sql($adapter))->insert()->into(static::TBL_OPTION)->columns(
                [static::TBL_FIELD_OPTNAME, static::TBL_FIELD_OPTVALUE]
            )->values([static::TBL_OPTNAME_DBTAG, $dbtag]);
        } else {
            $insertOrUpdate = (new Sql($adapter))->update()->table(static::TBL_OPTION)->set(
                [static::TBL_FIELD_OPTVALUE => $dbtag]
            )->where([static::TBL_FIELD_OPTNAME => static::TBL_OPTNAME_DBTAG]);
        }
        $adapter->query($insertOrUpdate->getSqlString($adapter->getPlatform()))
            ->execute();
    }
    
    
    
    /**
     * Return the dbhash. dbhash is a unique hash created from the dbtag and the schema version
     * numbers of all the components. It changes every time, a component updates the schema or dbtag is changed.
     *
     * @param Adapter $adapter
     *
     * @return string|null
     * @throws Exception
     * @see Updater::setDbHash()
     */
    public static function getDbHash(Adapter $adapter)
    {
        try {
            $select = (new Sql($adapter))->select()
                ->from(static::TBL_OPTION)
                ->where(
                    [
                        static::TBL_FIELD_OPTNAME => static::TBL_OPTNAME_DBHASH,
                    ]
                );
            $result = $adapter->query($select->getSqlString($adapter->getPlatform()))
                ->execute();
        } catch (\Exception $e) {
            \Ruga\Log::addLog($e->getMessage());
            return null;
        }
        return $result->current()[static::TBL_FIELD_OPTVALUE] ?? null;
    }
    
    
    
    /**
     * Set the dbhash if it is not set already.
     *
     * @param Adapter $adapter
     * @param string  $dbhash
     *
     * @throws Exception
     * @see Updater::getDbHash()
     */
    public static function setDbHash(Adapter $adapter, string $dbhash)
    {
        $olddbhash = static::getDbHash($adapter);
        if ($olddbhash) {
            return;
        }
        $insert = (new Sql($adapter))->insert()->into(static::TBL_OPTION)->columns(
            [static::TBL_FIELD_OPTNAME, static::TBL_FIELD_OPTVALUE]
        )->values([static::TBL_OPTNAME_DBHASH, $dbhash]);
        $adapter->query($insert->getSqlString($adapter->getPlatform()))->execute();
    }
    
    
    
    /**
     * Creates \Ruga\Db\Adapter\Adapter.
     *
     * @param array $config Config part to create an adapter
     *
     * @return Adapter
     *
     * @deprecated Create adapter by instantiating {@link \Ruga\Db\Adapter\Adapter}.
     */
    public static function getAdapter($config): Adapter
    {
        return new Adapter($config);
    }
    
    
    
    /**
     * Creates the ruga_option table and sets the dbtag if it does not exist and the database is empty. If
     * ruga_option is there, checks if dbtag matches.
     *
     * @param Adapter $adapter
     * @param array   $config
     *
     * @throws Exception
     */
    public static function initDatabase(Adapter $adapter, array $config)
    {
        // Get meta data object
        $metadata = Factory::createSourceFromAdapter($adapter);
        $conf_dbtag = $config[Updater::class][Updater::CONF_DBTAG] ?? null;
        
        // Check if option table exists
        if (!in_array(static::TBL_OPTION, $metadata->getTableNames())) {
            // Is database empty?
            if (count($metadata->getTables()) > 0) {
                throw new \Exception(
                    "Requested to create a new `" . static::TBL_OPTION . "` option table, but the data base `{$adapter->getCurrentSchema()}` is not empty"
                );
            }
            
            \Ruga\Log::addLog(
                "Option table `" . static::TBL_OPTION . "` not found. Creating...",
                \Ruga\Log\Severity::WARNING
            );
            $table = new Ddl\CreateTable(static::TBL_OPTION);
            $table->addColumn(new Column\Varchar(static::TBL_FIELD_OPTNAME, static::TBL_LENGTH_OPTNAME));
            $table->addColumn(new Column\Varchar(static::TBL_FIELD_OPTVALUE, static::TBL_LENGTH_OPTVALUE, true));
            $table->addConstraint(new Constraint\PrimaryKey(static::TBL_FIELD_OPTNAME));
            $adapter->query($table->getSqlString($adapter->getPlatform()), Adapter::QUERY_MODE_EXECUTE);
            
            if ($adapter->getPlatform()->getName() == 'SQLite') {
                // Enable WAL journal for SQLite
                $adapter->query("PRAGMA journal_mode = WAL", Adapter::QUERY_MODE_EXECUTE);
            }
            
            // Set dbtag
            if (isset($config[Updater::class][Updater::CONF_DBTAG])) {
                static::setDbTag($adapter, $conf_dbtag);
            }
        }
        
        // Check if dbtag from config matches dbtag from database
        if (($db_dbtag = static::getDbTag($adapter)) != $conf_dbtag) {
            throw new \Exception(
                "dbtag from database '{$db_dbtag}' does not match dbtag from config '{$conf_dbtag}'. Are you using the correct database ({$adapter->getCurrentSchema()})?"
            );
        }
    }
    
    
    
    public static function getResolver(Adapter $adapter = null, array $config = null)
    {
        if (!static::$resolver) {
            static::$resolver = new Resolver($adapter, $config);
        }
        return static::$resolver;
    }
    
    
    
    /**
     * Run the updater to alter the schema.
     * Executes the following tasks:
     * - creates an option table, if it does not exist.
     * - Reads current schema version from data base.
     * - executes the file with the current version in the file name (dbversion_xxxxxx.php)
     * - increments the schema version
     * - repeats previous two steps as long as files are found
     *
     * @param Adapter  $adapter            Data base adapter
     * @param string   $dbschemadir        Path to schema files
     * @param int|null $requestedDbVersion Version requested by application. Update process will stop at this version,
     *                                     if provided.
     * @param string   $comp_name          Component name
     *
     * @return int New version after update
     * @throws Exception
     */
    public static function updateComponent(
        Adapter $adapter,
        string $dbschemadir,
        int $requestedDbVersion = PHP_INT_MAX,
        string $comp_name = ''
    ) {
        /** @var string $tblOptnameDbVersion Name of the version option for this component */
        $tblOptnameDbVersion = implode("_", array_filter([static::TBL_OPTNAME_DBVERSION, $comp_name]));
        
        
        // Replay all versions found
        $dbschemadir = realpath($dbschemadir_orig = rtrim($dbschemadir, " \t\n\r\0\x0B\\/"));
        if (empty($dbschemadir) || !is_dir($dbschemadir)) {
            throw new Exception("dbschemadir '{$dbschemadir_orig}' not found.");
        }
        
        while ($requestedDbVersion > ($dbversion = static::getDbVersion($adapter, $comp_name)) && file_exists(
                $dbversionfile = sprintf("{$dbschemadir}/dbversion_%06u.php", $dbversion)
            )) {
            \Ruga\Log::addLog("Applying file '{$dbversionfile}'", \Ruga\Log\Severity::NOTICE);
            
            $data = [
                'comp_name' => $comp_name,
                'resolver' => static::getResolver($adapter), //TODO: Where is $config?
            ];
            
            $aSql = (function ($dbversionfile, $data) {
                extract($data, EXTR_SKIP);
//                unset($data);
                try {
                    ob_start();
                    $aSql = include $dbversionfile;
                    $str = trim(ob_get_contents());
                    if (!empty($str)) {
                        \Ruga\Log::log_msg($str);
                    }
                    return $aSql;
                } catch (\Throwable $e) {
                    \Ruga\Log::addLog($e);
                    throw $e;
                } finally {
                    ob_end_clean();
                }
            })(
                $dbversionfile,
                $data
            );
            
            
            $aSql = (array)$aSql;
            
            $sql = '';
            foreach ($aSql as $s) {
                $sql .= trim($s, "; \t\n\r\0\x0B");
                $sql .= ";\n";
            }
            
            // Update component schema version
            $s = (new Sql($adapter))->update()
                ->table(static::TBL_OPTION)
                ->set(
                    [
                        static::TBL_FIELD_OPTVALUE => new Expression("`" . static::TBL_FIELD_OPTVALUE . "` + 1"),
                    ]
                )
                ->where(
                    [
                        static::TBL_FIELD_OPTNAME => $tblOptnameDbVersion,
                    ]
                );
            $sql .= "{$s->getSqlString($adapter->getPlatform())};\n";
            
            // Delete dbhash
            $s = (new Sql($adapter))->delete()
                ->from(static::TBL_OPTION)
                ->where(
                    [
                        static::TBL_FIELD_OPTNAME => 'dbhash',
                    ]
                );
            $sql .= "{$s->getSqlString($adapter->getPlatform())};\n";
            
            \Ruga\Log::addLog(
                "Updating database " . (empty($comp_name) ? '' : "(component {$comp_name}) ") . "version {$dbversion} to " . ($dbversion + 1) . ": {$sql}",
                \Ruga\Log\Severity::DEBUG
            );

//            $fn="output_" . str_replace(['/', "\\"], '_', $comp_name) . "_" . sprintf("%06u", $dbversion) . ".sql";
//            file_put_contents($fn, $sql . PHP_EOL . print_r(sys_get_temp_dir(), true));
            $adapter->query($sql)->execute();
            
            $newVersion = static::getDbVersion($adapter, $comp_name);
            if ($newVersion != ($dbversion + 1)) {
                throw new SchemaUpdateFailedException(
                    "Error updating data base schema. The query did not return errors but the version was not updated. Version found: {$newVersion} | version expected: " . ($dbversion + 1)
                );
            }
        }
        
        \Ruga\Log::addLog(
            "Database " . (empty($comp_name) ? '' : "(component {$comp_name}) ") . "schema version: " . ($newVersion ?? $dbversion),
            \Ruga\Log\Severity::INFORMATIONAL
        );
        
        return $newVersion ?? $dbversion;
    }
    
    
    
    /**
     * Updates the schema for all components.
     *
     * @param Adapter $adapter
     * @param array   $config
     *
     * @throws Exception
     */
    public static function update(Adapter $adapter, array $config)
    {
        if (!is_array($config) || !isset($config[Updater::class])) {
            throw new OutOfBoundsException("Key '" . Updater::class . " not found in configuration.");
        }
        
        static::getResolver($adapter, $config);
        
        // Initialize data base
        static::initDatabase($adapter, $config);
        
        $hashcomponents = [];
        $hashcomponents['dbtag'] = static::getDbTag($adapter);
        $config = $config[Updater::class];
        
        // Update components schema
        if (isset($config['components']) && is_array($config['components'])) {
            foreach ($config['components'] as $comp_name => $comp_config) {
                $hashcomponents[$comp_name] = static::updateComponent(
                    $adapter,
                    $comp_config[static::CONF_SCHEMA_DIRECTORY],
                    $comp_config[static::CONF_REQUESTED_VERSION],
                    $comp_name
                );
            }
        }
        
        // Update project schema
        $hashcomponents['dbversion'] = static::updateComponent(
            $adapter,
            $config[static::CONF_SCHEMA_DIRECTORY],
            $config[static::CONF_REQUESTED_VERSION]
        );
        
        // Set the new hash
        $hashcomponents = array_map(
            function ($key, $val) {
                return "{$key}={$val}";
            },
            array_keys($hashcomponents),
            array_values($hashcomponents)
        );
        $hashstring = implode(';', $hashcomponents);
        static::setDbHash($adapter, md5($hashstring));
    }
}
