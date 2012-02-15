<?php

/* Include the new script for printing quotations in Portrait
 */

AddColumn('email', 'custcontacts', 'varchar(55)', 'not null', '', 'notes', $db);

UpdateDBNo(107, $db);

?>