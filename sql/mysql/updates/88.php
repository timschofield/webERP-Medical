<?php

AddColumn('units', 'stockmoves', 'varchar(40)', 'NOT NULL', '', 'narrative', $db);
AddColumn('conversionfactor', 'stockmoves', 'double', 'NOT NULL', '1', 'units', $db);

UpdateDBNo(85, $db);

?>