<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('RecurringSalesOrdersProcess.php',1), array('script', 'security'), array('RecurringSalesOrdersProcess.php',1), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('reportwriter/admin/ReportCreator.php',15), array('script', 'security'), array('reportwriter/admin/ReportCreator.php',15), $db);

UpdateDBNo(96, $db);

?>