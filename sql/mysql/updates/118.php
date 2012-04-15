<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('config', array('confname', 'confvalue'), array('Care2xDatabase','None'), array('confname', 'confvalue'), array('Care2xDatabase','None'), $db);

UpdateDBNo(118, $db);

?>