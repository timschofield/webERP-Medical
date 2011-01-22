<?php

/* Update config value and default price end date
 */

$UpdateSQL = "UPDATE config SET confvalue = '4.0' WHERE confname='VersionNumber'";
$UpdateSQL2 = "UPDATE prices SET enddate = '2030-01-01' WHERE enddate = '0000-00-00'";
$UpdateSQL3 = "ALTER TABLE prices ALTER COLUMN enddate SET DEFAULT '2030-01-01'";
$UpdateResult=executeSQL($UpdateSQL, $db);
$UpdateResult2=executeSQL($UpdateSQL2, $db);
$UpdateResult3 = executeSQL($UpdateSQL3,$db);
OutputResult( _('Default end date for prices updated and set to 2030-01-01'), 'success');
UpdateDBNo(58, $db);

?>
