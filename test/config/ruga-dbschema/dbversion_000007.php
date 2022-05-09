<?php

return <<<'SQL'

SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `Member` (`id`, `fullname`, `first_name`, `last_name`, `created`, `createdBy`, `changed`, `changedBy`)
VALUES
       (null, 'Hans Muster', 'Hans', 'Muster', NOW(), 1, NOW(), 1),
       (null, 'Vreni Meier', 'Vreni', 'Meier', NOW(), 1, NOW(), 1);
SET FOREIGN_KEY_CHECKS = 1;


SQL;
