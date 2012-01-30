<?php

/* Include the new script for printing quotations in Portrait
 */

AddColumn('issueglact', 'stockcategory', 'INT( 11 )', 'not null', 0, 'adjglact', $db);

$sql="UPDATE stockcategory
			SET issueglact=adjglact";
$result=DB_query($sql, $db);

UpdateDBNo(105, $db);

?>