<?php

AddColumn('lastcategoryupdate', 'stockmaster', 'DATE', 'NOT NULL', '0000-00-00', 'categoryid', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>