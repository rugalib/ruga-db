<?php

declare(strict_types=1);

$userTable = "User";
$partyTable = "Party";
$customerTable = "Customer";
$organizationTable = "Organization";
$personTable = "Person";
$tenantTable = "Tenant";
$partyhasorganizationTable = "Party_has_Organization";
$partyhaspersonTable = "Party_has_Person";
$partyhaspartyTable = "Party_has_Party";



$partyhasuserTable = "Party_has_User";




return <<<"SQL"
SET FOREIGN_KEY_CHECKS = 0;

-- rugalib/ruga-db-person/ruga-dbschema-person/dbversion_000000.php
CREATE TABLE `{$personTable}` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(190) NULL,
  `salutation` VARCHAR(190) NULL DEFAULT NULL,
  `first_name` VARCHAR(190) NULL DEFAULT NULL,
  `title` VARCHAR(190) NULL DEFAULT NULL,
  `prefix` VARCHAR(190) NULL DEFAULT NULL,
  `last_name` VARCHAR(190) NULL DEFAULT NULL,
  `middle_name` VARCHAR(190) NULL DEFAULT NULL,
  `birth_name` VARCHAR(190) NULL DEFAULT NULL,
  `religious_name` VARCHAR(190) NULL DEFAULT NULL,
  `nickname` VARCHAR(190) NULL DEFAULT NULL,
  `gender` VARCHAR(3) NULL DEFAULT NULL,
  `nationality` VARCHAR(3) NULL DEFAULT NULL,
  `language` VARCHAR(3) NULL DEFAULT NULL,
  `date_of_birth` DATE NULL DEFAULT NULL,
  `date_of_death` DATE NULL DEFAULT NULL,
  `remark` TEXT NULL,
  `created` DATETIME NULL,
  `createdBy` INT NULL,
  `changed` DATETIME NULL,
  `changedBy` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `{$personTable}_fullname_idx` (`fullname`),
  INDEX `fk_{$personTable}_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_{$personTable}_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_{$personTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$personTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

-- rugalib/ruga-db-person/ruga-dbschema-person/dbversion_000001.php
ALTER TABLE `{$personTable}` ADD COLUMN `citizenship` VARCHAR(3) NULL DEFAULT NULL AFTER `nationality`;

-- rugalib/ruga-db-person/ruga-dbschema-person/dbversion_000002.php
ALTER TABLE `{$personTable}`
    ADD COLUMN `familystatus` VARCHAR(2) NULL DEFAULT NULL AFTER `date_of_death`,
    ADD COLUMN `spouse` VARCHAR(190) NULL DEFAULT NULL AFTER `familystatus`;

-- rugalib/ruga-db-person/ruga-dbschema-person/dbversion_000003.php
ALTER TABLE `{$personTable}`
    ADD COLUMN `migrationid` VARCHAR(10) NULL DEFAULT NULL AFTER `citizenship`,
    ADD COLUMN `migrationid_until` DATE NULL DEFAULT NULL AFTER `migrationid`;

-- rugalib/ruga-db-person/ruga-dbschema-person/dbversion_000004.php
ALTER TABLE `{$personTable}`
    ADD COLUMN `religion` INT NULL DEFAULT NULL AFTER `migrationid_until`,
    ADD COLUMN `denomination` INT NULL DEFAULT NULL AFTER `religion`;

-- rugalib/ruga-db-person/ruga-dbschema-person/dbversion_000005.php
ALTER TABLE `{$personTable}`
    ADD COLUMN `birth_place` VARCHAR(190) NULL DEFAULT NULL AFTER `date_of_birth`,
    ADD COLUMN `death_place` VARCHAR(190) NULL DEFAULT NULL AFTER `date_of_death`;

-- rugalib/ruga-db-person/ruga-dbschema-person/dbversion_000006.php
ALTER TABLE `{$personTable}`
    CHANGE COLUMN `date_of_birth` `birth_date` DATE NULL DEFAULT NULL AFTER `language`,
    CHANGE COLUMN `date_of_death` `death_date` DATE NULL DEFAULT NULL AFTER `birth_date`,
    CHANGE COLUMN `prefix` `honorific_prefix` VARCHAR(190) NULL DEFAULT NULL AFTER `title`,
    ADD COLUMN `honorific_suffix` VARCHAR(190) NULL DEFAULT NULL AFTER `last_name`,
    CHANGE COLUMN `nickname` `nick_name` VARCHAR(190) NULL DEFAULT NULL AFTER `religious_name`,
    ADD COLUMN `height` DECIMAL(3,2) NULL DEFAULT NULL AFTER `spouse`;

-- rugalib/ruga-db-person/ruga-dbschema-person/dbversion_000007.php
ALTER TABLE `{$personTable}` ADD COLUMN `Tenant_id` INT NULL DEFAULT NULL AFTER `height`;
ALTER TABLE `{$personTable}` ADD INDEX `fk_{$personTable}_Tenant_id_idx` (`Tenant_id`);

SET FOREIGN_KEY_CHECKS = 1;
SQL;
