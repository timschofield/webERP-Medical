<?php

/* Add new field into the purchase order line to hold the conversion factor
 */

AddColumn('conversionfactor', 'purchorderdetails', 'int(11)', 'NOT NULL', 0, 'uom', $db);

UpdateDBNo(54, $db);

?>