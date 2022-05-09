<?php

return <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE `Muster` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(255) NOT NULL DEFAULT '',
  `Simple_id` INT NULL DEFAULT NULL,
  `Tenant_id` INT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Muster_Simple_id_idx` (`Simple_id` ASC),
  INDEX `fk_Muster_Tenant_id_idx` (`Tenant_id`),
  INDEX `fk_Muster_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_Muster_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_Muster_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `User` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_Muster_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `User` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE=InnoDB
;
INSERT INTO `Muster` (`fullname`, `Simple_id`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Linked to table Simple', '5', NOW(), '3', NOW(), '3');
SET FOREIGN_KEY_CHECKS = 1;
SQL;
