<?php

declare(strict_types=1);

$userTable = "User";
$cartTable = "Cart";
$cartItemTable = "CartItem";

return <<<"SQL"
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `{$cartTable}` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `fullname` VARCHAR(190) NULL,

    `remark` TEXT NULL,
    `created` DATETIME NOT NULL,
    `createdBy` INT NOT NULL,
    `changed` DATETIME NOT NULL,
    `changedBy` INT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `{$cartTable}_fullname_idx` (`fullname`),
    INDEX `fk_{$cartTable}_changedBy_idx` (`changedBy` ASC),
    INDEX `fk_{$cartTable}_createdBy_idx` (`createdBy` ASC),
    CONSTRAINT `fk_{$cartTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_{$cartTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
SQL;
