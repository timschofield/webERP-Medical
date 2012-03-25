<?php

/* Include the new script for printing quotations in Portrait
 */

UpdateField('pagesecurity', 'script', 'CopyBOM.php', "script='Z_CopyBOM.php'", $db);

UpdateDBNo(114, $db);

?>