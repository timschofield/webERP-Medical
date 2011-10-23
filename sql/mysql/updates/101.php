<?php

/* Include the new script for printing quotations in Portrait
 */


AddColumn('completed', 'stockrequestitems', 'TINYINT( 4 )', 'not null', 0, 'uom', $db);
AddColumn('closed', 'stockrequest', 'TINYINT( 4 )', 'not null', 0, 'authorised', $db);
AddColumn('qtydelivered', 'stockrequestitems', 'INT( 11 )', 'not null', 0, 'quantity', $db);

InsertRecord('pagesecurity', array('script', 'security'), array('InternalStockRequestFulfill.php',1), array('script', 'security'), array('InternalStockRequestFulfill.php',1), $db);

UpdateDBNo(101, $db);

?>