<?php

/* Include the new script for printing quotations in Portrait
 */

AddColumn('pagewidth', 'labels', 'float', 'NOT NULL', '0', 'description', $db);
AddColumn('pageheight', 'labels', 'float', 'NOT NULL', '0', 'pagewidth', $db);
DropColumn('papersize', 'labels', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>