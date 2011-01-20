<?php

/* Database updates for security system
 */


InsertRecord('pagesecurity', array('script', 'security'), array('SupplierTenders.php',9), array('script', 'security'), array('SupplierTenders.php',9), $db);
UpdateField('securitytokens', 'tokenname', 'Supplier tendering', 'tokenid=9', $db);
UpdateField('securitytokens', 'tokenname', 'Prices security', 'tokenid=6', $db);
$UpdateSQL = "UPDATE config SET confvalue = '4.00 RC3' WHERE confname='VersionNumber'";

UpdateDBNo(60, $db);

?>