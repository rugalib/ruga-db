<?php

declare(strict_types=1);

$userTable = "User";
$cartTable = "Cart";
$cartItemTable = "CartItem";

return <<<"SQL"
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `{$cartTable}` (`fullname`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('cart 1', NOW(), 3, NOW(), 3);
SET @CART_ID = LAST_INSERT_ID();
INSERT INTO `{$cartItemTable}` (`{$cartTable}_id`, `fullname`, `seq`, `created`, `createdBy`, `changed`, `changedBy`)
VALUES
    (@CART_ID, 'cart 1, item 1', 1, NOW(), 3, NOW(), 3),
    (@CART_ID, 'cart 1, item 2', 2, NOW(), 3, NOW(), 3),
    (@CART_ID, 'cart 1, item 3', 3, NOW(), 3, NOW(), 3),
    (@CART_ID, 'cart 1, item 4', 4, NOW(), 3, NOW(), 3);


INSERT INTO `{$cartTable}` (`fullname`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('cart 2', NOW(), 3, NOW(), 3);
SET @CART_ID = LAST_INSERT_ID();
INSERT INTO `{$cartItemTable}` (`{$cartTable}_id`, `fullname`, `seq`, `created`, `createdBy`, `changed`, `changedBy`)
VALUES
    (@CART_ID, 'cart 2, item 1', 1, NOW(), 3, NOW(), 3),
    (@CART_ID, 'cart 2, item 2', 2, NOW(), 3, NOW(), 3),
    (@CART_ID, 'cart 2, item 3', 3, NOW(), 3, NOW(), 3),
    (@CART_ID, 'cart 2, item 4', 4, NOW(), 3, NOW(), 3),
    (@CART_ID, 'cart 2, item 5', 4, NOW(), 3, NOW(), 3),
    (@CART_ID, 'cart 2, item 6', 4, NOW(), 3, NOW(), 3);



SET FOREIGN_KEY_CHECKS = 1;
SQL;
