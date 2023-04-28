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

-- rugalib/ruga-db-organization/ruga-dbschema-organization/dbversion_000000.php
CREATE TABLE `{$organizationTable}` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(190) NULL,
  `name` VARCHAR(190) NULL DEFAULT NULL,
  `org_type` ENUM('LEGAL', 'INFORMAL') NOT NULL DEFAULT 'LEGAL',
  `org_subtype` VARCHAR(190) NULL DEFAULT NULL,
  `date_of_establishment` DATE NULL DEFAULT NULL,
  `date_of_dissolution` DATE NULL DEFAULT NULL,
  `remark` TEXT NULL,
  `created` DATETIME NULL,
  `createdBy` INT NULL,
  `changed` DATETIME NULL,
  `changedBy` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `{$organizationTable}_fullname_idx` (`fullname`),
  INDEX `fk_{$organizationTable}_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_{$organizationTable}_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_{$organizationTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$organizationTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

-- rugalib/ruga-db-organization/ruga-dbschema-organization/dbversion_000001.php
ALTER TABLE `{$organizationTable}` ADD COLUMN `Tenant_id` INT NULL DEFAULT NULL AFTER `date_of_dissolution`;
ALTER TABLE `{$organizationTable}` ADD INDEX `fk_{$organizationTable}_Tenant_id_idx` (`Tenant_id`);

SET FOREIGN_KEY_CHECKS = 1;
SQL;
