<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('PDFPeriodStockTransListing.php',3), array('script', 'security'), array('PDFPeriodStockTransListing.php',3), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('ReprintGRN.php',11), array('script', 'security'), array('ReprintGRN.php',11), $db);

UpdateDBNo(78, $db);

?>