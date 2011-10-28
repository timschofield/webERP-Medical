<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('GLJournalInquiry.php',10), array('script', 'security'), array('GLJournalInquiry.php',10), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('PDFGLJournal.php',10), array('script', 'security'), array('PDFGLJournal.php',10), $db);

UpdateDBNo(90, $db);

?>