<?php

AddColumn('defaulttag', 'www_users', 'VARCHAR( 20 )', 'NOT NULL', '', 'defaultlocation', $db);
AddColumn('defaulttag', 'gltrans', 'VARCHAR( 20 )', 'NOT NULL', '', 'jobref', $db);

UpdateDBNo(83, $db);

?>