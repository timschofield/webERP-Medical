<?php

/* Include the new script for printing quotations in Portrait
 */

AddIndex(array('debtorno', 'typeid'), 'debtorsmaster', 'debtorsmaster_idx1', $db);
AddIndex(array('name'), 'debtorsmaster', 'debtorsmaster_idx2', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>