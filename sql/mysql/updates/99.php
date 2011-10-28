<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('InternalStockRequestAuthorisation.php',1), array('script', 'security'), array('InternalStockRequestAuthorisation.php',1), $db);

UpdateDBNo(99, $db);

?>