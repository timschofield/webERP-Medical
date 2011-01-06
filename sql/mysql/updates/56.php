<?php

/* Include the Report writer scripts in the page security table
 */

InsertRecord('pagesecurity', array('script', 'security'), array('FormMaker.php',1), array('script', 'security'), array('FormMaker.php',1), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('ReportMaker.php',1), array('script', 'security'), array('ReportMaker.php',1), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('ReportCreator.php',13), array('script', 'security'), array('ReportCreator.php',13), $db);

UpdateDBNo(56, $db);

?>