<?php

AddColumn('closed', 'tenders', 'int(2)', 'NOT NULL', '0', 'telephone', $db);
AddColumn('requiredbydate', 'tenders', 'datetime', 'NOT NULL', '0000-00-00', 'closed', $db);

UpdateDBNo(71, $db);

?>