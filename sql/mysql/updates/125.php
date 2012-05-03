<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('config', array('confname', 'confvalue'), array('ShowLogoOnReceipt','0'), array('confname', 'confvalue'), array('ShowLogoOnReceipt','0'), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>