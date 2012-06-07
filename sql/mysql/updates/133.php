<?php

AddColumn('description', 'bomheader', 'text', 'NOT NULL', ' ', 'productid', $db);
AddColumn('changedetails', 'bomheader', 'text', 'NOT NULL', ' ', 'revisionlevel', $db);
AddColumn('validfrom', 'bomheader', 'date', 'NOT NULL', '0000-00-00', 'changedetails', $db);
AddColumn('validto', 'bomheader', 'date', 'NOT NULL', '0000-00-00', 'validfrom', $db);
AddColumn('document', 'bomheader', 'varchar(70)', 'NOT NULL', ' ', 'validto', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>