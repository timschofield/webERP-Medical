<?php

CreateTable('tenders', "CREATE TABLE `tenders` (
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `location` varchar(5) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`tenderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;", $db);

CreateTable('tendersuppliers', "CREATE TABLE `tendersuppliers` (
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `responded` int(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tenderid`, `supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;", $db);

CreateTable('tenderitems', "CREATE TABLE `tenderitems` (
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` varchar(40) NOT NULL DEFAULT '',
  `units` varchar(20) NOT NULL DEFAULT 'each',
  PRIMARY KEY (`tenderid`, `stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;", $db);

InsertRecord('systypes', array('typeid'), array('37'), array('typeid', 'typename', 'typeno'), array('37', 'Tenders', '0'), $db);

UpdateDBNo(70, $db);

?>