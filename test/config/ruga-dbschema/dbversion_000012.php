<?php

declare(strict_types=1);

$userTable = "User";
$cartTable = "Cart";
$cartItemTable = "CartItem";

return <<<"SQL"
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `{$cartItemTable}` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `fullname` VARCHAR(190) NULL,
    `{$cartTable}_id` INT NOT NULL,
    `seq` INT NOT NULL,
    `remark` TEXT NULL,
    `created` DATETIME NOT NULL,
    `createdBy` INT NOT NULL,
    `changed` DATETIME NOT NULL,
    `changedBy` INT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `{$cartItemTable}_fullname_idx` (`fullname`),
    INDEX `fk_{$cartItemTable}_{$cartTable}_id_idx` (`{$cartTable}_id`),
    INDEX `{$cartItemTable}_seq_idx` (`seq`),
    -- UNIQUE `fk_{$cartItemTable}_{$cartTable}_id_seq_unique` (`{$cartTable}_id`, `seq`),
    CONSTRAINT `fk_{$cartItemTable}_{$cartTable}_id` FOREIGN KEY (`{$cartTable}_id`) REFERENCES `{$cartTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    INDEX `fk_{$cartItemTable}_changedBy_idx` (`changedBy` ASC),
    INDEX `fk_{$cartItemTable}_createdBy_idx` (`createdBy` ASC),
    CONSTRAINT `fk_{$cartItemTable}_changedBy` FOREIGN KEY (`changedBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_{$cartItemTable}_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `{$userTable}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
SQL;
