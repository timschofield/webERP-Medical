<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('config', array('confname', 'confvalue'), array('DispenseOnBill','1'), array('confname', 'confvalue'), array('DispenseOnBill','1'), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>