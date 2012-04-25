<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('KCMCUnbilledItems.php',1), array('script', 'security'), array('KCMCUnbilledItems.php',1), $db);

UpdateDBNo(120, $db);

?>