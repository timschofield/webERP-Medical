<?php

/* Add extra field into prices table for unit of measure
 */

AddColumn('units', 'prices', 'varchar(20)', 'NOT NULL', 'each', 'price', $db);

$sql="UPDATE prices SET units=(SELECT units FROM stockmaster WHERE prices.stockid=stockmaster.stockid)";
$result=DB_query($sql, $db);

UpdateDBNo(64, $db);

?>