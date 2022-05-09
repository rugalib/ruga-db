<?php

return <<<'SQL'

SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `Simple` (`id`, `data`)
VALUES
       (null, 'data 1'),
       (null, 'data 2'),
       (null, 'data 3');
SET FOREIGN_KEY_CHECKS = 1;

SQL;
