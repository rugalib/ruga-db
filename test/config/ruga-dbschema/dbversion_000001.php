<?php

return <<<'SQL'

SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `Alltypes` (`id`, `tinyint_nn`, `smallint_nn`, `mediumint_nn`, `int_nn`, `int_nnd`, `int_n`, `int_nd`, `bigint_nn`, `bit_nn`, `float_nn`, `double_nn`, `decimal_nn`, `char_nn`, `varchar_nn`, `tinytext_nn`, `text_nn`, `mediumtext_nn`, `longtext_nn`, `json_nn`, `binary_nn`, `varbinary_nn`, `tinyblob_nn`, `blob_nn`, `mediumblob_nn`, `longblob_nn`, `date_nn`, `time_nn`, `datetime_nn`, `timestamp_nn`, `enum_nn`, `enum_nnd`, `enum_n`, `enum_nd`, `set_nn`, `set_nnd`, `set_n`, `set_nd`, `created`, `createdBy`, `changed`, `changedBy`)
VALUES
 (null, -128, -32768, -8388608, -2147483648, -2147483648, -2147483648, -2147483648, POWER(2,63)*(-1), b'0', RADIANS(180)*(-1), -3.1415, -7.5500, 'z', 'abcd', 'Hallo Welt', 'Hallo Welt', 'Hallo Welt', 'Hallo Welt', '[{}]', 0x00, 0xAB, 0xDE, 0x00, 0x00, 0x00, '2020-04-14', '11:57:17', '2020-04-14 11:57:19', '2020-04-14 11:57:21', 'OTHER', 'QUESTION', 'REQUEST', 'QUESTION', 'REQUEST,OTHER', 'QUESTION,PROBLEM', 'REQUEST', 'QUESTION,PROBLEM', '2020-04-14 11:57:42', 1, '2020-04-14 11:57:46', 1)
,(null, 127, 32767, 8388607, 2147483647, 2147483647, 2147483647, 2147483647, POWER(2,63)-1, b'1', RADIANS(180), 3.1415, 7.5500, 'a', 'abcd', 'Hallo Welt', 'Hallo Welt', 'Hallo Welt', 'Hallo Welt', '[{}]', 0x00, 0xAB, 0xDE, 0x00, 0x00, 0x00, '2020-03-01', '09:00:00', '2020-03-02 14:57:18', '2020-03-03 17:00:00', 'OTHER', 'QUESTION', 'REQUEST', 'QUESTION', 'REQUEST,OTHER', 'QUESTION,PROBLEM', 'REQUEST', 'QUESTION,PROBLEM', '2020-04-14 11:57:42', 1, '2020-04-14 11:57:46', 1);
SET FOREIGN_KEY_CHECKS = 1;

SQL;
