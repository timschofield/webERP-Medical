<?php

/* Add extra field into prices table for unit of measure
 */

AddColumn('conversionfactor', 'prices', 'double', 'NOT NULL', '1', 'units', $db);

$sql="UPDATE prices SET conversionfactor=1";
$result=DB_query($sql, $db);

UpdateDBNo(65, $db);

?>