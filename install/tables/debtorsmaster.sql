CREATE TABLE `debtorsmaster` (
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL DEFAULT '',
  `currcode` char(3) NOT NULL DEFAULT '',
  `salestype` char(2) NOT NULL DEFAULT '',
  `clientsince` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `holdreason` smallint NOT NULL DEFAULT '0',
  `paymentterms` char(2) NOT NULL DEFAULT 'f',
  `discount` double NOT NULL DEFAULT '0',
  `pymtdiscount` double NOT NULL DEFAULT '0',
  `lastpaid` double NOT NULL DEFAULT '0',
  `lastpaiddate` datetime DEFAULT NULL,
  `creditlimit` double NOT NULL DEFAULT '1000',
  `invaddrbranch` tinyint NOT NULL DEFAULT '0',
  `discountcode` char(2) NOT NULL DEFAULT '',
  `ediinvoices` tinyint NOT NULL DEFAULT '0',
  `ediorders` tinyint NOT NULL DEFAULT '0',
  `edireference` varchar(20) NOT NULL DEFAULT '',
  `editransport` varchar(5) NOT NULL DEFAULT 'email',
  `ediaddress` varchar(50) NOT NULL DEFAULT '',
  `ediserveruser` varchar(20) NOT NULL DEFAULT '',
  `ediserverpwd` varchar(20) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `customerpoline` tinyint(1) NOT NULL DEFAULT '0',
  `typeid` tinyint NOT NULL DEFAULT '1',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  PRIMARY KEY (`debtorno`),
  KEY `Currency` (`currcode`),
  KEY `HoldReason` (`holdreason`),
  KEY `Name` (`name`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SalesType` (`salestype`),
  KEY `EDIInvoices` (`ediinvoices`),
  KEY `EDIOrders` (`ediorders`),
  KEY `debtorsmaster_ibfk_5` (`typeid`),
  CONSTRAINT `debtorsmaster_ibfk_1` FOREIGN KEY (`holdreason`) REFERENCES `holdreasons` (`reasoncode`),
  CONSTRAINT `debtorsmaster_ibfk_2` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `debtorsmaster_ibfk_3` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `debtorsmaster_ibfk_4` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`),
  CONSTRAINT `debtorsmaster_ibfk_5` FOREIGN KEY (`typeid`) REFERENCES `debtortype` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3