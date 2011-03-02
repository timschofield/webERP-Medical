<?php

/* Add extra field into prices table for unit of measure
 */

AddColumn('units', 'salesorderdetails', 'varchar(20)', 'NOT NULL', 'each', 'unitprice', $db);
AddColumn('conversionfactor', 'salesorderdetails', 'double', 'NOT NULL', '1', 'units', $db);

$sql="UPDATE salesorderdetails SET conversionfactor=1";
$result=DB_query($sql, $db);

$sql="UPDATE salesorderdetails SET units=(SELECT units FROM stockmaster WHERE salesorderdetails.stkcode=stockmaster.stockid)";
$result=DB_query($sql, $db);

UpdateDBNo(66, $db);

?>