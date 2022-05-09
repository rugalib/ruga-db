<?php
declare(strict_types=1);

namespace Ruga\Db\Test\Model;

use Ruga\Db\Row\RowAttributesInterface;

/**
 * Interface AlltypesAttributesInterface
 *
 * @package src\Model
 *
 * @property int $id PRIMARY KEY: INT(11) NOT NULL AUTO_INCREMENT,
 * @property int $tinyint_nn TINYINT(4) NOT NULL
 * @property int $smallint_nn SMALLINT(6) NOT NULL
 * @property int $mediumint_nn` MEDIUMINT(9) NOT NULL
 * @property int $int_nn INT(11) NOT NULL
 * @property int $int_nnd INT(11) NOT NULL DEFAULT '6'
 * @property int $int_n INT(11) NULL DEFAULT NULL
 * @property int $int_nd INT(11) NULL DEFAULT '7'
 * @property int $bigint_nn BIGINT(20) NOT NULL
 * @property int $bit_nn BIT(1) NOT NULL
 * @property float $float_nn FLOAT(12,0) NOT NULL
 * @property float $double_nn` DOUBLE(22,0) NOT NULL
 * @property float $decimal_nn` DECIMAL(19,4) NOT NULL
 * @property string $char_nn CHAR(1) NOT NULL
 * @property string $varchar_nn VARCHAR(50) NOT NULL
 * @property string $tinytext_nn TINYTEXT NOT NULL
 * @property string $text_nn` TEXT(65535) NOT NULL
 * @property string $mediumtext_nn` MEDIUMTEXT NOT NULL
 * @property string $longtext_nn` LONGTEXT NOT NULL
 * @property \stdClass $json_nn` JSON NOT NULL
 * @property string $binary_nn` BINARY(1) NOT NULL
 * @property string $varbinary_nn` VARBINARY(50) NOT NULL
 * @property string $tinyblob_nn` TINYBLOB NOT NULL
 * @property string $blob_nn` BLOB NOT NULL
 * @property string $mediumblob_nn` MEDIUMBLOB NOT NULL
 * @property string $longblob_nn` LONGBLOB NOT NULL
 * @property \DateTimeImmutable $date_nn` DATE NOT NULL
 * @property \DateTimeImmutable $time_nn` TIME NOT NULL
 * @property \DateTimeImmutable $datetime_nn` DATETIME NOT NULL
 * @property \DateTimeImmutable $timestamp_nn` TIMESTAMP NOT NULL
 * @property string $enum_nn` ENUM('QUESTION','PROBLEM','REQUEST','OTHER') NOT NULL COLLATE 'utf8_general_ci'
 * @property string $enum_nnd` ENUM('QUESTION','PROBLEM','REQUEST','OTHER') NOT NULL DEFAULT 'QUESTION' COLLATE 'utf8_general_ci'
 * @property string $enum_n` ENUM('QUESTION','PROBLEM','REQUEST','OTHER') NULL DEFAULT NULL COLLATE 'utf8_general_ci'
 * @property string $enum_nd` ENUM('QUESTION','PROBLEM','REQUEST','OTHER') NULL DEFAULT 'QUESTION' COLLATE 'utf8_general_ci'
 * @property array $set_nn` SET('QUESTION','PROBLEM','REQUEST','OTHER') NOT NULL COLLATE 'utf8_general_ci'
 * @property array $set_nnd` SET('QUESTION','PROBLEM','REQUEST','OTHER') NOT NULL DEFAULT 'QUESTION,PROBLEM' COLLATE 'utf8_general_ci'
 * @property array $set_n` SET('QUESTION','PROBLEM','REQUEST','OTHER') NULL DEFAULT NULL COLLATE 'utf8_general_ci'
 * @property array $set_nd` SET('QUESTION','PROBLEM','REQUEST','OTHER') NULL DEFAULT 'QUESTION,PROBLEM' COLLATE 'utf8_general_ci'
 * @property \DateTimeImmutable $created` DATETIME NOT NULL
 * @property int $createdBy` INT(11) NOT NULL
 * @property \DateTimeImmutable $changed` DATETIME NOT NULL
 * @property int $changedBy` INT(11) NOT NULL
 */
interface AlltypesAttributesInterface extends RowAttributesInterface
{
}
