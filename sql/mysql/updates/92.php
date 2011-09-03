<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('config', array('confname', 'confvalue'), array('InventoryManagerEmail',  'info@yourdomain.com'), array('confname', 'confvalue'), array('InventoryManagerEmail',  'info@yourdomain.com'), $db);

UpdateDBNo(92, $db);

?>