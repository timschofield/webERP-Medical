<?php

/* Creates the version 2 assetmanager table */

CreateTable("divisions", "CREATE TABLE `divisions` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

UpdateDBNo(112, $db);

?>