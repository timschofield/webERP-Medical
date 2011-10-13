<?php

/* Include the new script for printing quotations in Portrait
 */

UpdateField('pagesecurity', 'script', 'InternalStockRequest.php', 'script=\'StockTransfers_departments.php\'', $db);

UpdateDBNo(98, $db);

?>