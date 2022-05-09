<?php

return <<<'SQL'

SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE `Member` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(255) NOT NULL DEFAULT '',
  `first_name` VARCHAR(255) NOT NULL DEFAULT '',
  `last_name` VARCHAR(255) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `changed` DATETIME NOT NULL,
  `changedBy` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Member_changedBy_idx` (`changedBy` ASC),
  INDEX `fk_Member_createdBy_idx` (`createdBy` ASC),
  CONSTRAINT `fk_Member_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `User` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_Member_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `User` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE=InnoDB
;
SET FOREIGN_KEY_CHECKS = 1;

SQL;

