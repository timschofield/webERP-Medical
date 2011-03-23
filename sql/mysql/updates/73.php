<?php

/* Add extra field into prices table for unit of measure
 */

AddColumn('decimalplaces', 'prices', 'int(11)', 'NOT NULL', '0', 'conversionfactor', $db);
AddColumn('decimalplaces', 'salesorderdetails', 'int(11)', 'NOT NULL', '1', 'conversionfactor', $db);

$sql="UPDATE prices SET decimalplaces=(SELECT decimalplaces FROM stockmaster WHERE prices.stockid=stockmaster.stockid)";
$result=DB_query($sql, $db);

$sql="UPDATE salesorderdetails SET decimalplaces=(SELECT decimalplaces FROM stockmaster WHERE salesorderdetails.stkcode=stockmaster.stockid)";
$result=DB_query($sql, $db);

UpdateDBNo(73, $db);

?>