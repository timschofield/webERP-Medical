<?php

AddColumn('defaulttag', 'www_users', 'VARCHAR( 20 )', 'NOT NULL', '', 'defaultlocation', $db);
AddColumn('companytag', 'gltrans', 'VARCHAR( 20 )', 'NOT NULL', '', 'jobref', $db);

UpdateDBNo(83, $db);

?>