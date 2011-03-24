<?php

/* Add extra field into www_users setting whether a user
 * can create a tender. 0=No 1=Yes
 */

AddColumn('cancreatetender', 'www_users', 'tinyint(1)', 'NOT NULL', '0', 'fullaccess', $db);

$sql="UPDATE www_users SET cancreatetender=0";

UpdateDBNo(74, $db);

?>