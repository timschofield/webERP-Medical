<?php

/* Move the version number to the database from the config.php file
 */

NewConfigValue('VersionNumber', '4.0', $db);

UpdateDBNo(34, $db);

?>
