<?php

return <<<'SQL'

SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE `Simple` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `data` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)
ENGINE=InnoDB
;
SET FOREIGN_KEY_CHECKS = 1;

SQL;
