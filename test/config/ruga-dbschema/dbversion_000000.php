<?php

return <<<'SQL'

SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE `Alltypes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  
  `tinyint_nn` TINYINT NOT NULL,
  `smallint_nn` SMALLINT NOT NULL,
  `mediumint_nn` MEDIUMINT NOT NULL,
  `int_nn` INT NOT NULL,
  `int_nnd` INT NOT NULL DEFAULT '6',
  `int_n` INT NULL,
  `int_nd` INT NULL DEFAULT '7',
  `bigint_nn` BIGINT NOT NULL,
  `bit_nn` BIT NOT NULL,
  
  `float_nn` FLOAT NOT NULL,
  `double_nn` DOUBLE NOT NULL,
  `decimal_nn` DECIMAL(19,4) NOT NULL,
  
  `char_nn` CHAR NOT NULL,
  `varchar_nn` VARCHAR(50) NOT NULL,
  `tinytext_nn` TINYTEXT NOT NULL,
  `text_nn` TEXT NOT NULL,
  `mediumtext_nn` MEDIUMTEXT NOT NULL,
  `longtext_nn` LONGTEXT NOT NULL,
  `json_nn` JSON NOT NULL,
  
  `binary_nn` BINARY NOT NULL,
  `varbinary_nn` VARBINARY(50) NOT NULL,
  `tinyblob_nn` TINYBLOB NOT NULL,
  `blob_nn` BLOB NOT NULL,
  `mediumblob_nn` MEDIUMBLOB NOT NULL,
  `longblob_nn` LONGBLOB NOT NULL,
  
  `date_nn` DATE NOT NULL,
  `time_nn` TIME NOT NULL,
  `datetime_nn` DATETIME NOT NULL,
  `timestamp_nn` TIMESTAMP NOT NULL,
  
  `enum_nn` ENUM('QUESTION','PROBLEM','REQUEST','OTHER') NOT NULL,
  `enum_nnd` ENUM('QUESTION','PROBLEM','REQUEST','OTHER') NOT NULL DEFAULT 'QUESTION',
  `enum_n` ENUM('QUESTION','PROBLEM','REQUEST','OTHER') NULL,
  `enum_nd` ENUM('QUESTION','PROBLEM','REQUEST','OTHER') NULL DEFAULT 'QUESTION',

  `set_nn` SET('QUESTION','PROBLEM','REQUEST','OTHER') NOT NULL,
  `set_nnd` SET('QUESTION','PROBLEM','REQUEST','OTHER') NOT NULL DEFAULT 'QUESTION,PROBLEM',
  `set_n` SET('QUESTION','PROBLEM','REQUEST','OTHER') NULL,
  `set_nd` SET('QUESTION','PROBLEM','REQUEST','OTHER') NULL DEFAULT 'QUESTION,PROBLEM',
  
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Alltypes_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_Alltypes_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_Alltypes_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `User` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_Alltypes_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `User` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE=InnoDB
;
SET FOREIGN_KEY_CHECKS = 1;



SQL;
