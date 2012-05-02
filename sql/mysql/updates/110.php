<?php

/* Include the new script for printing quotations in Portrait
 */

Createtable('labels', "CREATE TABLE IF NOT EXISTS `labels` (
  `labelid` tinyint(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `papersize` varchar(20) NOT NULL,
  `height` tinyint(11) NOT NULL,
  `width` tinyint(11) NOT NULL,
  `topmargin` tinyint(11) NOT NULL,
  `leftmargin` tinyint(11) NOT NULL,
  `rowheight` tinyint(11) NOT NULL,
  `columnwidth` tinyint(11) NOT NULL,
  PRIMARY KEY (`labelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1", $db);

Createtable('labelfields', "CREATE TABLE IF NOT EXISTS `labelfields` (
  `labelfieldid` int(11) NOT NULL AUTO_INCREMENT,
  `labelid` tinyint(4) NOT NULL,
  `fieldvalue` varchar(20) CHARACTER SET utf8 NOT NULL,
  `vpos` tinyint(4) NOT NULL,
  `hpos` tinyint(4) NOT NULL,
  `fontsize` tinyint(4) NOT NULL,
  `barcode` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`labelfieldid`),
  KEY `labelid` (`labelid`),
  KEY `vpos` (`vpos`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8", $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>