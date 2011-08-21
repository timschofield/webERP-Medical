<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('BackupDatabase.php',15), array('script', 'security'), array('BackupDatabase.php',15), $db);

UpdateDBNo(91, $db);

?>