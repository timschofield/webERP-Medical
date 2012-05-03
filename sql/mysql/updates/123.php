<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('SupplierPriceList.php',4), array('script', 'security'), array('SupplierPriceList.php',4), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>