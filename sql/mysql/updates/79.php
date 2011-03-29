<?php


ChangeColumnName('nw', 'purchorderdetails', 'varchar(50)', 'NOT NULL', '0', 'netweight', $db);

UpdateDBNo(79, $db);

?>