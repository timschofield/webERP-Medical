<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('PDFQuotationPortrait.php',2), array('script', 'security'), array('PDFQuotationPortrait.php',2), $db);

UpdateDBNo(63, $db);

?>
~    
