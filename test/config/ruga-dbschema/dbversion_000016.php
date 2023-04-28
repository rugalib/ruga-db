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

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000000.php
CREATE TABLE `{$partyTable}` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(190) NULL,
  `party_role` SET('CUSTOMER','SUPPLIER','PROSPECT','SHAREHOLDER','TENANT') NULL DEFAULT NULL,
  `remark` TEXT NULL,
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `{$partyTable}_fullname_idx` (`fullname`),
  INDEX `{$partyTable}_party_role_idx` (`party_role`),
  INDEX `fk_{$partyTable}_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_{$partyTable}_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_{$partyTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000001.php
CREATE TABLE `{$customerTable}` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(190) NULL,
  `customer_number` VARCHAR(190) NULL,
  `Party_id` INT NOT NULL,
  `remark` TEXT NULL,
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `{$customerTable}_fullname_idx` (`fullname`),
  INDEX `fk_{$customerTable}_Party_id_idx` (`Party_id` ASC),
  INDEX `fk_{$customerTable}_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_{$customerTable}_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_{$customerTable}_Party_id` FOREIGN KEY (`Party_id`) REFERENCES `{$partyTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$customerTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$customerTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000002.php
CREATE TABLE `{$partyhasorganizationTable}` (
  `Party_id` INT NOT NULL,
  `Organization_id` INT NOT NULL,
  `organization_role` SET('PARTNER','DEPARTMENT') NULL DEFAULT NULL,
  `remark` TEXT NULL,
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`Party_id`, `Organization_id`),
  INDEX `fk_{$partyhasorganizationTable}_Party_id_idx` (`Party_id` ASC),
  INDEX `fk_{$partyhasorganizationTable}_Organization_id_idx` (`Organization_id` ASC),
  INDEX `fk_{$partyhasorganizationTable}_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_{$partyhasorganizationTable}_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_{$partyhasorganizationTable}_Party_id` FOREIGN KEY (`Party_id`) REFERENCES `{$partyTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhasorganizationTable}_Organization_id` FOREIGN KEY (`Organization_id`) REFERENCES `{$organizationTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhasorganizationTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhasorganizationTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000003.php
CREATE TABLE `{$partyhaspersonTable}` (
  `Party_id` INT NOT NULL,
  `Person_id` INT NOT NULL,
  `person_role` SET('CONTACT','EMPLOYEE') NULL DEFAULT NULL,
  `remark` TEXT NULL,
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`Party_id`, `Person_id`),
  INDEX `fk_{$partyhaspersonTable}_Party_id_idx` (`Party_id` ASC),
  INDEX `fk_{$partyhaspersonTable}_Person_id_idx` (`Person_id` ASC),
  INDEX `fk_{$partyhaspersonTable}_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_{$partyhaspersonTable}_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_{$partyhaspersonTable}_Party_id` FOREIGN KEY (`Party_id`) REFERENCES `{$partyTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhaspersonTable}_Person_id` FOREIGN KEY (`Person_id`) REFERENCES `{$personTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhaspersonTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhaspersonTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000004.php
ALTER TABLE `{$partyTable}` ADD COLUMN `party_subtype` ENUM('PERSON','ORGANIZATION') NOT NULL DEFAULT 'ORGANIZATION' AFTER `party_role`;
ALTER TABLE `{$partyTable}` ADD INDEX `{$partyTable}_party_subtype_idx` (`party_subtype`);

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000005.php
-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000006.php
CREATE TABLE `{$partyhaspartyTable}` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Party1_id` INT NOT NULL,
  `Party2_id` INT NOT NULL,
  `relationship_type` ENUM('CUSTOMER','EMPLOYEE','CONTRACTOR','SUPPLIER','CONTACT','DISTRIBUTOR','PARTNER','ORGANIZATION_UNIT','REPRESENTATIVE') NOT NULL,
  `valid_from` DATETIME NULL DEFAULT NULL,
  `valid_thru` DATETIME NULL DEFAULT NULL,
  `remark` TEXT NULL,
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `{$partyhaspartyTable}_relationship_type_idx` (`relationship_type`),
  INDEX `{$partyhaspartyTable}_valid_from_idx` (`valid_from`),
  INDEX `{$partyhaspartyTable}_valid_thru_idx` (`valid_thru`),
  INDEX `fk_{$partyhaspartyTable}_Party1_id_idx` (`Party1_id` ASC),
  INDEX `fk_{$partyhaspartyTable}_Party2_id_idx` (`Party2_id` ASC),
  INDEX `fk_{$partyhaspartyTable}_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_{$partyhaspartyTable}_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_{$partyhaspartyTable}_Party1_id` FOREIGN KEY (`Party1_id`) REFERENCES `{$partyTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhaspartyTable}_Party2_id` FOREIGN KEY (`Party2_id`) REFERENCES `{$partyTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhaspartyTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhaspartyTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000007.php
CREATE TABLE `{$tenantTable}` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(190) NULL,
  `Party_id` INT NULL DEFAULT NULL,
  `remark` TEXT NULL,
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `{$tenantTable}_fullname_idx` (`fullname`),
  INDEX `fk_{$tenantTable}_Party_id_idx` (`Party_id` ASC),
  INDEX `fk_{$tenantTable}_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_{$tenantTable}_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_{$tenantTable}_Party_id` FOREIGN KEY (`Party_id`) REFERENCES `{$partyTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$tenantTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$tenantTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000008.php
ALTER TABLE `{$partyTable}` CHANGE COLUMN `party_role` `party_role` SET('CUSTOMER','SUPPLIER','PROSPECT','SHAREHOLDER','TENANT') NULL DEFAULT NULL AFTER `fullname`;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000009.php
ALTER TABLE `{$partyTable}` ADD COLUMN `Tenant_id` INT NULL DEFAULT NULL AFTER `party_subtype`;
ALTER TABLE `{$partyTable}` ADD INDEX `fk_{$partyTable}_Tenant_id_idx` (`Tenant_id`);
ALTER TABLE `{$partyTable}` ADD CONSTRAINT `fk_{$partyTable}_Tenant_id` FOREIGN KEY (`Tenant_id`) REFERENCES `{$tenantTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `{$customerTable}` ADD COLUMN `Tenant_id` INT NULL DEFAULT NULL AFTER `Party_id`;
ALTER TABLE `{$customerTable}` ADD INDEX `fk_{$customerTable}_Tenant_id_idx` (`Tenant_id`);
ALTER TABLE `{$customerTable}` ADD CONSTRAINT `fk_{$customerTable}_Tenant_id` FOREIGN KEY (`Tenant_id`) REFERENCES `{$tenantTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000010.php
ALTER TABLE `{$customerTable}` ADD UNIQUE `{$customerTable}_customer_number_UNIQUE` (`customer_number`);

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000011.php
CREATE TABLE `{$partyhasuserTable}` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Party_id` INT NOT NULL,
  `User_id` INT NOT NULL,
  `valid_from` DATETIME NULL DEFAULT NULL,
  `valid_thru` DATETIME NULL DEFAULT NULL,
  `remark` TEXT NULL,
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `{$partyhasuserTable}_valid_from_idx` (`valid_from`),
  INDEX `{$partyhasuserTable}_valid_thru_idx` (`valid_thru`),
  INDEX `fk_{$partyhasuserTable}_Party_id_idx` (`Party_id` ASC),
  INDEX `fk_{$partyhasuserTable}_User_id_idx` (`User_id` ASC),
  INDEX `fk_{$partyhasuserTable}_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_{$partyhasuserTable}_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_{$partyhasuserTable}_Party_id` FOREIGN KEY (`Party_id`) REFERENCES `{$partyTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhasuserTable}_User_id` FOREIGN KEY (`User_id`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhasuserTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_{$partyhasuserTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000012.php
ALTER TABLE `{$partyhaspartyTable}` CHANGE COLUMN `relationship_type` `relationship_type` ENUM('CUSTOMER','EMPLOYEE','CONTRACTOR','SUPPLIER','CONTACT','DISTRIBUTOR','PARTNER','ORGANIZATION_UNIT','REPRESENTATIVE') NOT NULL AFTER `Party2_id`;

-- rugalib/ruga-db-party/ruga-dbschema-party/dbversion_000013.php
INSERT INTO `{$tenantTable}` (`id`, `fullname`, `Party_id`, `created`, `createdBy`, `changed`, `changedBy`) VALUES
('1', 'SYSTEM', null, NOW(), '1', NOW(), '1');








SET FOREIGN_KEY_CHECKS = 1;
SQL;
