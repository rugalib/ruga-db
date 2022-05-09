<?php

return <<<'SQL'

SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE `Simple` ADD COLUMN `Tenant_id` INT NULL DEFAULT NULL AFTER `data`;
ALTER TABLE `Simple` ADD INDEX `Simple_Tenant_id_idx` (`Tenant_id`);
INSERT INTO Simple (data, Tenant_id) VALUES ('data 4', 1);
INSERT INTO Simple (data, Tenant_id) VALUES ('data 5', 1);
INSERT INTO Simple (data, Tenant_id) VALUES ('data 6', 2);
INSERT INTO Simple (data, Tenant_id) VALUES ('data 7', 2);
SET FOREIGN_KEY_CHECKS = 1;

SQL;
