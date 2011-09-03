<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('config', array('confname', 'confvalue'), array('WorkingDaysWeek',  '7'), array('confname', 'confvalue'), array('WorkingDaysWeek',  '7'), $db);

UpdateDBNo(93, $db);

?>