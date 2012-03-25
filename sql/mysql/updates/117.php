<?php

/* Include the new script for printing quotations in Portrait
 */


AddColumn('patient', 'salesorderdetails', 'varchar( 10 )', 'not null', '', 'commissionearned', $db);
AddColumn('employer', 'salesorderdetails', 'varchar( 3 )', 'not null', '', 'patient', $db);

UpdateDBNo(117, $db);

?>