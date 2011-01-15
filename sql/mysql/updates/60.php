<?php

/* Database updates for security system
 */


InsertRecord('pagesecurity', array('script', 'security'), array('SupplierTenders.php',9), array('script', 'security'), array('SupplierTenders.php',9), $db);
UpdateField('securitytokens', 'tokenname', 'Supplier tendering', 'tokenid=9', $db);
UpdateField('securitytokens', 'tokenname', 'Prices security', 'tokenid=6', $db);

UpdateDBNo(60, $db);

?>