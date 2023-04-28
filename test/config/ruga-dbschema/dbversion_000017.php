<?php

declare(strict_types=1);

$userTable = "User";
$partyTable = "Party";
$customerTable = "Customer";
$organizationTable = "Organization";
$personTable = "Person";
$tenantTable = "Tenant";
$partyhasorganizationTable = "Party_has_Organization";
$partyhaspersonTable = "Party_has_Person";
$partyhaspartyTable = "Party_has_Party";
$partyhasuserTable = "Party_has_User";

$passwordHash = function(string $pwd): string {
    return password_hash($pwd, PASSWORD_DEFAULT);
};



return <<<"SQL"
SET FOREIGN_KEY_CHECKS = 0;

# Tenant Kaufmann AG
INSERT INTO `{$partyTable}` (`fullname`, `party_role`, `party_subtype`, `Tenant_id`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Kaufmann AG', 'TENANT', 'ORGANIZATION', null, NOW(), 3, NOW(), 3);
SET @TENANT_PARTY_ID = LAST_INSERT_ID();
INSERT INTO `{$tenantTable}` (`fullname`, `Party_id`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Kaufmann AG', @TENANT_PARTY_ID, NOW(), 3, NOW(), 3);
SET @TENANT_ID = LAST_INSERT_ID();

# User Prisca Kaufmann, representative of Tenant Kaufmann AG
INSERT INTO `{$partyTable}` (`fullname`, `party_role`, `party_subtype`, `Tenant_id`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Prisca Kaufmann', null, 'PERSON', null, NOW(), 3, NOW(), 3);
SET @PARTY_ID = LAST_INSERT_ID();
INSERT INTO `{$userTable}` (username, password, fullname, email, mobile, created, createdBy, changed, changedBy) VALUES ('prisca.kaufmann', '{$passwordHash('5000')}', 'Prisca Kaufmann', 'prisca@example.com', '', NOW(), 3, NOW(), 3);
SET @USER_ID = LAST_INSERT_ID();
INSERT INTO `{$partyhasuserTable}` (Party_id, User_id, valid_from, valid_thru, created, createdBy, changed, changedBy) VALUES (@PARTY_ID, @USER_ID, null, null, NOW(), 3, NOW(), 3);
INSERT INTO `{$partyhaspartyTable}` (Party1_id, Party2_id, relationship_type, valid_from, valid_thru, created, createdBy, changed, changedBy) VALUES (@PARTY_ID, @TENANT_PARTY_ID, 'REPRESENTATIVE', null, null, NOW(), 3, NOW(), 3);

# Kunde Nadine Muster
INSERT INTO `{$partyTable}` (`fullname`, `party_role`, `party_subtype`, `remark`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Nadine Muster', 'CUSTOMER', 'PERSON', NULL, NOW(), 1, NOW(), 1);
SET @PARTY_ID = LAST_INSERT_ID();
INSERT INTO `{$customerTable}` (`fullname`, `customer_number`, `Party_id`, `remark`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Nadine Muster', '12345', @PARTY_ID, NULL, NOW(), 1, NOW(), 1);
INSERT INTO `{$personTable}` (`fullname`, `salutation`, `first_name`, `title`, `honorific_prefix`, `last_name`, `honorific_suffix`, `middle_name`, `birth_name`, `religious_name`, `nick_name`, `gender`, `nationality`, `citizenship`, `migrationid`, `migrationid_until`, `religion`, `denomination`, `language`, `birth_date`, `death_date`, `birth_place`, `death_place`, `familystatus`, `spouse`, `height`, `remark`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Nadine Muster', NULL, 'Nadine', NULL, NULL, 'Muster', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1977-03-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NOW(), 1, NOW(), 1);
INSERT INTO `{$partyhaspersonTable}` (`Party_id`, `Person_id`, `person_role`, `remark`, `created`, `createdBy`, `changed`, `changedBy`) VALUES (@PARTY_ID, LAST_INSERT_ID(), NULL, NULL, NOW(), 1, NOW(), 1);

# Kunde Hugentobler AG
INSERT INTO `{$partyTable}` (`fullname`, `party_role`, `party_subtype`, `remark`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Hugentobler AG', 'CUSTOMER', 'ORGANIZATION', NULL, NOW(), 1, NOW(), 1);
SET @PARTY_ID = LAST_INSERT_ID();
INSERT INTO `{$customerTable}` (`fullname`, `customer_number`, `Party_id`, `remark`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Hugentobler AG', '67890', LAST_INSERT_ID(), NULL, NOW(), 1, NOW(), 1);
INSERT INTO `{$organizationTable}` (`fullname`, `name`, `org_type`, `org_subtype`, `date_of_establishment`, `date_of_dissolution`, `remark`, `created`, `createdBy`, `changed`, `changedBy`) VALUES ('Hugentobler AG', 'Hugentobler AG', 'LEGAL', NULL, '2010-02-27', NULL, NULL, NOW(), 1, NOW(), 1);
INSERT INTO `{$partyhasorganizationTable}` (`Party_id`, `Organization_id`, `organization_role`, `remark`, `created`, `createdBy`, `changed`, `changedBy`) VALUES (@PARTY_ID, LAST_INSERT_ID(), NULL, NULL, NOW(), 1, NOW(), 1);



SET FOREIGN_KEY_CHECKS = 1;
SQL;
