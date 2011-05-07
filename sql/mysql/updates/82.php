<?php

AddColumn('assigner', 'pctabs', 'VARCHAR( 20 )', 'NOT NULL', '', 'tablimit', $db);

$sql="UPDATE pctabs SET assigner = authorizer";
$result=DB_query($sql, $db);

UpdateDBNo(82, $db);

?>