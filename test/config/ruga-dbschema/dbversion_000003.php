<?php

return <<<'SQL'

SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `User` (`id`, `username`, `password`, `fullname`, `email`, `mobile`, `role`, `created`, `createdBy`, `changed`, `changedBy`) VALUES
 ('1', 'SYSTEM', null, 'SYSTEM', null, null, 'system', '2020-01-01 00:00:00', '1', '2020-01-01 00:00:00', '1')
,('2', 'GUEST', null, 'GUEST', null, null, 'guest', '2020-01-01 00:00:00', '1', '2020-01-01 00:00:00', '1')
,('3', 'admin', '$2y$10$CNIWWxUHD8SyLPPTzrDmxOi6wuer1jKlvYcA46diECISimM2nFZJ6', 'admin', null, null, 'admin', '2020-01-01 00:00:00', '1', '2020-01-01 00:00:00', '1')
;
SET FOREIGN_KEY_CHECKS = 1;

SQL;
