<?php

/* Include the new script for printing quotations in Portrait
 */

AddColumn('canviewprices', 'securityroles', 'tinyint(1)', 'NOT NULL', '1', 'secrolename', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>