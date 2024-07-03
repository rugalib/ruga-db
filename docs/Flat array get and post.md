# Flat array get and post

Linked entities are returned in the flat array.

## Parent Rows

Here `changedBy` is the column name with the foreign key. There can be only one parent.

```
changedBy.PK = "1"
changedBy.idname = "[1] "SYSTEM""
changedBy.uniqueid = "1@UserTable"
changedBy.type = "UserTable"
changedBy.fullname = "SYSTEM"
changedBy.password = null
changedBy.email = null
changedBy.mobile = null
changedBy.remark = null
changedBy.id = {int} 1
changedBy.username = "SYSTEM"
changedBy.created = {DateTimeImmutable} 
changedBy.createdBy = {int} 1
changedBy.changed = {DateTimeImmutable} 
changedBy.changedBy = {int} 1
```



## Dependent Rows

Obviously there can be more than one row and a foreign key is not available in `$this` table. `fk_Reparatur_antragsteller_Party_id` is the name of the constraint.

```
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].PK = "1"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].idname = "[1] "TV 200 Zoll""
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].uniqueid = "1@ReparaturTable"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].type = "ReparaturTable"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].fullname = "TV 200 Zoll"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].id = {int} 1
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].reparaturnummer = null
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].name = "TV 200 Zoll"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].status = "ENTWURF"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].budget = null
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].antragsteller_Party_id = {int} 1
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].vertragspartner_Party_id = null
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].currency = "EUR"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].remark = null
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].created = {DateTimeImmutable} 
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].createdBy = {int} 1
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].changed = {DateTimeImmutable} 
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].changedBy = {int} 1
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].isReadOnly = false
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].isDisabled = false
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].isNew = false
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].isDeleted = false
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].canBeChangedBy = true
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].Vertragspartner = ""
fk_Reparatur_antragsteller_Party_id.ReparaturTable[0].budget_TEXT = "EUR 0"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].PK = "2"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].idname = "[2] "Trottinett""
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].uniqueid = "2@ReparaturTable"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].type = "ReparaturTable"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].fullname = "Trottinett"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].reparaturnummer = null
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].name = "Trottinett"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].status = "ENTWURF"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].budget = null
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].antragsteller_Party_id = {int} 1
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].vertragspartner_Party_id = null
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].currency = "EUR"
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].remark = null
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].id = {int} 2
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].created = {DateTimeImmutable} 
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].createdBy = {int} 1
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].changed = {DateTimeImmutable} 
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].changedBy = {int} 1
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].isReadOnly = false
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].isDisabled = false
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].isNew = false
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].isDeleted = false
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].canBeChangedBy = true
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].Vertragspartner = ""
fk_Reparatur_antragsteller_Party_id.ReparaturTable[1].budget_TEXT = "EUR 0"
```





```
fk_Party_has_ContactMechanism_Party1.PartyHasContactMechanismTable[0].ContactMechanism_id.fk_Address_ContactMechanism1.AddressTable[0].createdBy



fk_Party_has_Person_Party_id.PartyHasPersonTable[0].PK
fk_Party_has_Person_Party_id.PartyHasPersonTable[0].Person_id.height


```



