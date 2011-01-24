<?php

/* Database updates for latest version
 */

$response=executeSQL("UPDATE config SET confvalue = '4.0' WHERE confname='VersionNumber'", $db, false);

UpdateDBNo(61, $db);

?>