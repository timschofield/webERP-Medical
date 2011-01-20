-- MySQL dump 10.13  Distrib 5.1.49, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	5.1.49-1ubuntu8.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accountgroups`
--

DROP TABLE IF EXISTS `accountgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountgroups` (
  `groupname` char(30) NOT NULL DEFAULT '',
  `sectioninaccounts` int(11) NOT NULL DEFAULT '0',
  `pandl` tinyint(4) NOT NULL DEFAULT '1',
  `sequenceintb` smallint(6) NOT NULL DEFAULT '0',
  `parentgroupname` varchar(30) NOT NULL,
  PRIMARY KEY (`groupname`),
  KEY `SequenceInTB` (`sequenceintb`),
  KEY `sectioninaccounts` (`sectioninaccounts`),
  KEY `parentgroupname` (`parentgroupname`),
  CONSTRAINT `accountgroups_ibfk_1` FOREIGN KEY (`sectioninaccounts`) REFERENCES `accountsection` (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountgroups`
--

LOCK TABLES `accountgroups` WRITE;
/*!40000 ALTER TABLE `accountgroups` DISABLE KEYS */;
INSERT INTO `accountgroups` VALUES ('BBQs',5,1,6000,'Promotions'),('Cost of Goods Sold',2,1,5000,''),('Current Assets',20,0,1000,''),('Equity',50,0,3000,''),('Fixed Assets',10,0,500,''),('Giveaways',5,1,6000,'Promotions'),('Income Tax',5,1,9000,''),('Liabilities',30,0,2000,''),('Marketing Expenses',5,1,6000,''),('Operating Expenses',5,1,7000,''),('Other Revenue and Expenses',5,1,8000,''),('Outward Freight',2,1,5000,'Cost of Goods Sold'),('Promotions',5,1,6000,'Marketing Expenses'),('Revenue',1,1,4000,''),('Sales',1,1,10,'');
/*!40000 ALTER TABLE `accountgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accountsection`
--

DROP TABLE IF EXISTS `accountsection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountsection` (
  `sectionid` int(11) NOT NULL DEFAULT '0',
  `sectionname` mediumtext NOT NULL,
  PRIMARY KEY (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountsection`
--

LOCK TABLES `accountsection` WRITE;
/*!40000 ALTER TABLE `accountsection` DISABLE KEYS */;
INSERT INTO `accountsection` VALUES (1,'Income'),(2,'Cost Of Sales'),(5,'Overheads'),(10,'Fixed Assets'),(20,'Amounts Receivable'),(30,'Amounts Payable'),(50,'Financed By');
/*!40000 ALTER TABLE `accountsection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `areacode` char(3) NOT NULL,
  `areadescription` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`areacode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audittrail`
--

DROP TABLE IF EXISTS `audittrail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audittrail` (
  `transactiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userid` varchar(20) NOT NULL DEFAULT '',
  `querystring` mediumtext,
  KEY `UserID` (`userid`),
  CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bankaccounts`
--

DROP TABLE IF EXISTS `bankaccounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bankaccounts` (
  `accountcode` int(11) NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL,
  `invoice` smallint(2) NOT NULL DEFAULT '0',
  `bankaccountcode` varchar(50) NOT NULL DEFAULT '',
  `bankaccountname` char(50) NOT NULL DEFAULT '',
  `bankaccountnumber` char(50) NOT NULL DEFAULT '',
  `bankaddress` char(50) DEFAULT NULL,
  PRIMARY KEY (`accountcode`),
  KEY `currcode` (`currcode`),
  KEY `BankAccountName` (`bankaccountname`),
  KEY `BankAccountNumber` (`bankaccountnumber`),
  CONSTRAINT `bankaccounts_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bankaccounts`
--

LOCK TABLES `bankaccounts` WRITE;
/*!40000 ALTER TABLE `bankaccounts` DISABLE KEYS */;
INSERT INTO `bankaccounts` VALUES (1030,'AUD',0,'','Cheque Account','',''),(1040,'AUD',0,'','Savings Account','','');
/*!40000 ALTER TABLE `bankaccounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banktrans`
--

DROP TABLE IF EXISTS `banktrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banktrans` (
  `banktransid` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) NOT NULL DEFAULT '0',
  `transno` bigint(20) NOT NULL DEFAULT '0',
  `bankact` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(50) NOT NULL DEFAULT '',
  `amountcleared` double NOT NULL DEFAULT '0',
  `exrate` double NOT NULL DEFAULT '1' COMMENT 'From bank account currency to payment currency',
  `functionalexrate` double NOT NULL DEFAULT '1' COMMENT 'Account currency to functional currency',
  `transdate` date NOT NULL DEFAULT '0000-00-00',
  `banktranstype` varchar(30) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`banktransid`),
  KEY `BankAct` (`bankact`,`ref`),
  KEY `TransDate` (`transdate`),
  KEY `TransType` (`banktranstype`),
  KEY `Type` (`type`,`transno`),
  KEY `CurrCode` (`currcode`),
  CONSTRAINT `banktrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `banktrans_ibfk_2` FOREIGN KEY (`bankact`) REFERENCES `bankaccounts` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banktrans`
--

LOCK TABLES `banktrans` WRITE;
/*!40000 ALTER TABLE `banktrans` DISABLE KEYS */;
/*!40000 ALTER TABLE `banktrans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bom`
--

DROP TABLE IF EXISTS `bom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bom` (
  `parent` char(20) NOT NULL DEFAULT '',
  `component` char(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `loccode` char(5) NOT NULL DEFAULT '',
  `effectiveafter` date NOT NULL DEFAULT '0000-00-00',
  `effectiveto` date NOT NULL DEFAULT '9999-12-31',
  `quantity` double NOT NULL DEFAULT '1',
  `autoissue` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`parent`,`component`,`workcentreadded`,`loccode`),
  KEY `Component` (`component`),
  KEY `EffectiveAfter` (`effectiveafter`),
  KEY `EffectiveTo` (`effectiveto`),
  KEY `LocCode` (`loccode`),
  KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
  KEY `Parent_2` (`parent`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bom`
--

LOCK TABLES `bom` WRITE;
/*!40000 ALTER TABLE `bom` DISABLE KEYS */;
/*!40000 ALTER TABLE `bom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buckets`
--

DROP TABLE IF EXISTS `buckets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `buckets` (
  `workcentre` char(5) NOT NULL DEFAULT '',
  `availdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `capacity` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`workcentre`,`availdate`),
  KEY `WorkCentre` (`workcentre`),
  KEY `AvailDate` (`availdate`),
  CONSTRAINT `buckets_ibfk_1` FOREIGN KEY (`workcentre`) REFERENCES `workcentres` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buckets`
--

LOCK TABLES `buckets` WRITE;
/*!40000 ALTER TABLE `buckets` DISABLE KEYS */;
/*!40000 ALTER TABLE `buckets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chartdetails`
--

DROP TABLE IF EXISTS `chartdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chartdetails` (
  `accountcode` int(11) NOT NULL DEFAULT '0',
  `period` smallint(6) NOT NULL DEFAULT '0',
  `budget` double NOT NULL DEFAULT '0',
  `actual` double NOT NULL DEFAULT '0',
  `bfwd` double NOT NULL DEFAULT '0',
  `bfwdbudget` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`accountcode`,`period`),
  KEY `Period` (`period`),
  CONSTRAINT `chartdetails_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `chartdetails_ibfk_2` FOREIGN KEY (`period`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chartdetails`
--

LOCK TABLES `chartdetails` WRITE;
/*!40000 ALTER TABLE `chartdetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `chartdetails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chartmaster`
--

DROP TABLE IF EXISTS `chartmaster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chartmaster` (
  `accountcode` int(11) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountCode` (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`),
  CONSTRAINT `chartmaster_ibfk_1` FOREIGN KEY (`group_`) REFERENCES `accountgroups` (`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chartmaster`
--

LOCK TABLES `chartmaster` WRITE;
/*!40000 ALTER TABLE `chartmaster` DISABLE KEYS */;
INSERT INTO `chartmaster` VALUES (1,'Default Sales/Discounts','Sales'),(1010,'Petty Cash','Current Assets'),(1020,'Cash on Hand','Current Assets'),(1030,'Cheque Accounts','Current Assets'),(1040,'Savings Accounts','Current Assets'),(1050,'Payroll Accounts','Current Assets'),(1060,'Special Accounts','Current Assets'),(1070,'Money Market Investments','Current Assets'),(1080,'Short-Term Investments (< 90 days)','Current Assets'),(1090,'Interest Receivable','Current Assets'),(1100,'Accounts Receivable','Current Assets'),(1150,'Allowance for Doubtful Accounts','Current Assets'),(1200,'Notes Receivable','Current Assets'),(1250,'Income Tax Receivable','Current Assets'),(1300,'Prepaid Expenses','Current Assets'),(1350,'Advances','Current Assets'),(1400,'Supplies Inventory','Current Assets'),(1420,'Raw Material Inventory','Current Assets'),(1440,'Work in Progress Inventory','Current Assets'),(1460,'Finished Goods Inventory','Current Assets'),(1500,'Land','Fixed Assets'),(1550,'Bonds','Fixed Assets'),(1600,'Buildings','Fixed Assets'),(1620,'Accumulated Depreciation of Buildings','Fixed Assets'),(1650,'Equipment','Fixed Assets'),(1670,'Accumulated Depreciation of Equipment','Fixed Assets'),(1700,'Furniture & Fixtures','Fixed Assets'),(1710,'Accumulated Depreciation of Furniture & Fixtures','Fixed Assets'),(1720,'Office Equipment','Fixed Assets'),(1730,'Accumulated Depreciation of Office Equipment','Fixed Assets'),(1740,'Software','Fixed Assets'),(1750,'Accumulated Depreciation of Software','Fixed Assets'),(1760,'Vehicles','Fixed Assets'),(1770,'Accumulated Depreciation Vehicles','Fixed Assets'),(1780,'Other Depreciable Property','Fixed Assets'),(1790,'Accumulated Depreciation of Other Depreciable Prop','Fixed Assets'),(1800,'Patents','Fixed Assets'),(1850,'Goodwill','Fixed Assets'),(1900,'Future Income Tax Receivable','Current Assets'),(2010,'Bank Indedebtedness (overdraft)','Liabilities'),(2020,'Retainers or Advances on Work','Liabilities'),(2050,'Interest Payable','Liabilities'),(2100,'Accounts Payable','Liabilities'),(2150,'Goods Received Suspense','Liabilities'),(2200,'Short-Term Loan Payable','Liabilities'),(2230,'Current Portion of Long-Term Debt Payable','Liabilities'),(2250,'Income Tax Payable','Liabilities'),(2300,'GST Payable','Liabilities'),(2310,'GST Recoverable','Liabilities'),(2320,'PST Payable','Liabilities'),(2330,'PST Recoverable (commission)','Liabilities'),(2340,'Payroll Tax Payable','Liabilities'),(2350,'Withholding Income Tax Payable','Liabilities'),(2360,'Other Taxes Payable','Liabilities'),(2400,'Employee Salaries Payable','Liabilities'),(2410,'Management Salaries Payable','Liabilities'),(2420,'Director / Partner Fees Payable','Liabilities'),(2450,'Health Benefits Payable','Liabilities'),(2460,'Pension Benefits Payable','Liabilities'),(2470,'Canada Pension Plan Payable','Liabilities'),(2480,'Employment Insurance Premiums Payable','Liabilities'),(2500,'Land Payable','Liabilities'),(2550,'Long-Term Bank Loan','Liabilities'),(2560,'Notes Payable','Liabilities'),(2600,'Building & Equipment Payable','Liabilities'),(2700,'Furnishing & Fixture Payable','Liabilities'),(2720,'Office Equipment Payable','Liabilities'),(2740,'Vehicle Payable','Liabilities'),(2760,'Other Property Payable','Liabilities'),(2800,'Shareholder Loans','Liabilities'),(2900,'Suspense','Liabilities'),(3100,'Capital Stock','Equity'),(3200,'Capital Surplus / Dividends','Equity'),(3300,'Dividend Taxes Payable','Equity'),(3400,'Dividend Taxes Refundable','Equity'),(3500,'Retained Earnings','Equity'),(4100,'Product / Service Sales','Revenue'),(4200,'Sales Exchange Gains/Losses','Revenue'),(4500,'Consulting Services','Revenue'),(4600,'Rentals','Revenue'),(4700,'Finance Charge Income','Revenue'),(4800,'Sales Returns & Allowances','Revenue'),(4900,'Sales Discounts','Revenue'),(5000,'Cost of Sales','Cost of Goods Sold'),(5100,'Production Expenses','Cost of Goods Sold'),(5200,'Purchases Exchange Gains/Losses','Cost of Goods Sold'),(5500,'Direct Labour Costs','Cost of Goods Sold'),(5600,'Freight Charges','Outward Freight'),(5700,'Inventory Adjustment','Cost of Goods Sold'),(5800,'Purchase Returns & Allowances','Cost of Goods Sold'),(5900,'Purchase Discounts','Cost of Goods Sold'),(6100,'Advertising','Marketing Expenses'),(6150,'Promotion','Promotions'),(6200,'Communications','Marketing Expenses'),(6250,'Meeting Expenses','Marketing Expenses'),(6300,'Travelling Expenses','Marketing Expenses'),(6400,'Delivery Expenses','Marketing Expenses'),(6500,'Sales Salaries & Commission','Marketing Expenses'),(6550,'Sales Salaries & Commission Deductions','Marketing Expenses'),(6590,'Benefits','Marketing Expenses'),(6600,'Other Selling Expenses','Marketing Expenses'),(6700,'Permits, Licenses & License Fees','Marketing Expenses'),(6800,'Research & Development','Marketing Expenses'),(6900,'Professional Services','Marketing Expenses'),(7020,'Support Salaries & Wages','Operating Expenses'),(7030,'Support Salary & Wage Deductions','Operating Expenses'),(7040,'Management Salaries','Operating Expenses'),(7050,'Management Salary deductions','Operating Expenses'),(7060,'Director / Partner Fees','Operating Expenses'),(7070,'Director / Partner Deductions','Operating Expenses'),(7080,'Payroll Tax','Operating Expenses'),(7090,'Benefits','Operating Expenses'),(7100,'Training & Education Expenses','Operating Expenses'),(7150,'Dues & Subscriptions','Operating Expenses'),(7200,'Accounting Fees','Operating Expenses'),(7210,'Audit Fees','Operating Expenses'),(7220,'Banking Fees','Operating Expenses'),(7230,'Credit Card Fees','Operating Expenses'),(7240,'Consulting Fees','Operating Expenses'),(7260,'Legal Fees','Operating Expenses'),(7280,'Other Professional Fees','Operating Expenses'),(7300,'Business Tax','Operating Expenses'),(7350,'Property Tax','Operating Expenses'),(7390,'Corporation Capital Tax','Operating Expenses'),(7400,'Office Rent','Operating Expenses'),(7450,'Equipment Rental','Operating Expenses'),(7500,'Office Supplies','Operating Expenses'),(7550,'Office Repair & Maintenance','Operating Expenses'),(7600,'Automotive Expenses','Operating Expenses'),(7610,'Communication Expenses','Operating Expenses'),(7620,'Insurance Expenses','Operating Expenses'),(7630,'Postage & Courier Expenses','Operating Expenses'),(7640,'Miscellaneous Expenses','Operating Expenses'),(7650,'Travel Expenses','Operating Expenses'),(7660,'Utilities','Operating Expenses'),(7700,'Ammortization Expenses','Operating Expenses'),(7750,'Depreciation Expenses','Operating Expenses'),(7800,'Interest Expense','Operating Expenses'),(7900,'Bad Debt Expense','Operating Expenses'),(8100,'Gain on Sale of Assets','Other Revenue and Expenses'),(8200,'Interest Income','Other Revenue and Expenses'),(8300,'Recovery on Bad Debt','Other Revenue and Expenses'),(8400,'Other Revenue','Other Revenue and Expenses'),(8500,'Loss on Sale of Assets','Other Revenue and Expenses'),(8600,'Charitable Contributions','Other Revenue and Expenses'),(8900,'Other Expenses','Other Revenue and Expenses'),(9100,'Income Tax Provision','Income Tax');
/*!40000 ALTER TABLE `chartmaster` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cogsglpostings`
--

DROP TABLE IF EXISTS `cogsglpostings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cogsglpostings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area` char(2) NOT NULL DEFAULT '',
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `glcode` int(11) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `GLCode` (`glcode`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cogsglpostings`
--

LOCK TABLES `cogsglpostings` WRITE;
/*!40000 ALTER TABLE `cogsglpostings` DISABLE KEYS */;
INSERT INTO `cogsglpostings` VALUES (3,'AN','ANY',5000,'AN');
/*!40000 ALTER TABLE `cogsglpostings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `coycode` int(11) NOT NULL DEFAULT '1',
  `coyname` varchar(50) NOT NULL DEFAULT '',
  `gstno` varchar(20) NOT NULL DEFAULT '',
  `companynumber` varchar(20) NOT NULL DEFAULT '0',
  `regoffice1` varchar(40) NOT NULL DEFAULT '',
  `regoffice2` varchar(40) NOT NULL DEFAULT '',
  `regoffice3` varchar(40) NOT NULL DEFAULT '',
  `regoffice4` varchar(40) NOT NULL DEFAULT '',
  `regoffice5` varchar(20) NOT NULL DEFAULT '',
  `regoffice6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `currencydefault` varchar(4) NOT NULL DEFAULT '',
  `debtorsact` int(11) NOT NULL DEFAULT '70000',
  `pytdiscountact` int(11) NOT NULL DEFAULT '55000',
  `creditorsact` int(11) NOT NULL DEFAULT '80000',
  `payrollact` int(11) NOT NULL DEFAULT '84000',
  `grnact` int(11) NOT NULL DEFAULT '72000',
  `exchangediffact` int(11) NOT NULL DEFAULT '65000',
  `purchasesexchangediffact` int(11) NOT NULL DEFAULT '0',
  `retainedearnings` int(11) NOT NULL DEFAULT '90000',
  `gllink_debtors` tinyint(1) DEFAULT '1',
  `gllink_creditors` tinyint(1) DEFAULT '1',
  `gllink_stock` tinyint(1) DEFAULT '1',
  `freightact` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`coycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'webERP\'s Demo Company Inc','not entered yet','','123 Web Way','PO Box 123','Queen Street','Melbourne','Victoria 3043','Australia','+61 3 4567 8901','+61 3 4567 8902','weberp@weberpdemo.com','AUD',1100,4900,2100,2400,2150,4200,5200,3500,1,1,1,5600);
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL DEFAULT '',
  `confvalue` mediumtext NOT NULL,
  PRIMARY KEY (`confname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES ('AllowOrderLineItemNarrative','0'),('AllowSalesOfZeroCostItems','0'),('AutoCreateWOs','1'),('AutoDebtorNo','0'),('AutoIssue','1'),('CheckCreditLimits','1'),('Check_Price_Charged_vs_Order_Price','1'),('Check_Qty_Charged_vs_Del_Qty','1'),('CountryOfOperation','AUD'),('CreditingControlledItems_MustExist','0'),('DBUpdateNumber','60'),('DB_Maintenance','30'),('DB_Maintenance_LastRun','2011-01-15'),('DefaultBlindPackNote','1'),('DefaultCreditLimit','1000'),('DefaultCustomerType','1'),('DefaultDateFormat','d/m/Y'),('DefaultDisplayRecordsMax','50'),('DefaultFactoryLocation','MEL'),('DefaultPriceList','DE'),('DefaultSupplierType','1'),('DefaultTaxCategory','1'),('DefaultTheme','silverwolf'),('Default_Shipper','1'),('DefineControlledOnWOEntry','1'),('DispatchCutOffTime','14'),('DoFreightCalc','0'),('EDIHeaderMsgId','D:01B:UN:EAN010'),('EDIReference','WEBERP'),('EDI_Incoming_Orders','companies/weberp/EDI_Incoming_Orders'),('EDI_MsgPending','companies/weberp/EDI_MsgPending'),('EDI_MsgSent','companies/weberp/EDI_Sent'),('Extended_CustomerInfo','0'),('Extended_SupplierInfo','0'),('FactoryManagerEmail','phil@logicworks.co.nz'),('FreightChargeAppliesIfLessThan','1000'),('FreightTaxCategory','1'),('FrequentlyOrderedItems','0'),('geocode_integration','0'),('HTTPS_Only','0'),('InvoicePortraitFormat','0'),('LogPath',''),('LogSeverity','0'),('MaxImageSize','300'),('MonthsAuditTrail','1'),('NumberOfMonthMustBeShown','6'),('NumberOfPeriodsOfStockUsage','12'),('OverChargeProportion','30'),('OverReceiveProportion','20'),('PackNoteFormat','1'),('PageLength','48'),('part_pics_dir','companies/weberpdemo/part_pics'),('PastDueDays1','30'),('PastDueDays2','60'),('PO_AllowSameItemMultipleTimes','1'),('ProhibitJournalsToControlAccounts','1'),('ProhibitNegativeStock','1'),('ProhibitPostingsBefore','2009-03-31'),('PurchasingManagerEmail',''),('QuickEntries','10'),('RadioBeaconFileCounter','/home/RadioBeacon/FileCounter'),('RadioBeaconFTP_user_name','RadioBeacon ftp server user name'),('RadioBeaconHomeDir','/home/RadioBeacon'),('RadioBeaconStockLocation','BL'),('RadioBraconFTP_server','192.168.2.2'),('RadioBreaconFilePrefix','ORDXX'),('RadionBeaconFTP_user_pass','Radio Beacon remote ftp server password'),('reports_dir','companies/weberpdemo/reportwriter'),('RequirePickingNote','0'),('RomalpaClause','Ownership will not pass to the buyer until the goods have been paid for in full.'),('SalesOrder_FOI','0'),('ShowValueOnGRN','1'),('Show_Settled_LastMonth','1'),('SO_AllowSameItemMultipleTimes','1'),('TaxAuthorityReferenceName','Tax Ref'),('UpdateCurrencyRatesDaily','0'),('VersionNumber','4.00 RC3'),('WeightedAverageCosting','1'),('WikiApp','WackoWiki'),('WikiPath','wiki'),('YearEnd','3');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contractbom`
--

DROP TABLE IF EXISTS `contractbom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contractbom` (
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`),
  KEY `Stockid` (`stockid`),
  KEY `ContractRef` (`contractref`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contractbom`
--

LOCK TABLES `contractbom` WRITE;
/*!40000 ALTER TABLE `contractbom` DISABLE KEYS */;
/*!40000 ALTER TABLE `contractbom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contractcharges`
--

DROP TABLE IF EXISTS `contractcharges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contractcharges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL,
  `transtype` smallint(6) NOT NULL DEFAULT '20',
  `transno` int(11) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  `anticipated` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contractref` (`contractref`,`transtype`,`transno`),
  KEY `contractcharges_ibfk_2` (`transtype`),
  CONSTRAINT `contractcharges_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`),
  CONSTRAINT `contractcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contractcharges`
--

LOCK TABLES `contractcharges` WRITE;
/*!40000 ALTER TABLE `contractcharges` DISABLE KEYS */;
/*!40000 ALTER TABLE `contractcharges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contractreqts`
--

DROP TABLE IF EXISTS `contractreqts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contractreqts` (
  `contractreqid` int(11) NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  `costperunit` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`contractreqid`),
  KEY `ContractRef` (`contractref`),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contractreqts`
--

LOCK TABLES `contractreqts` WRITE;
/*!40000 ALTER TABLE `contractreqts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contractreqts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts`
--

DROP TABLE IF EXISTS `contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts` (
  `contractref` varchar(20) NOT NULL DEFAULT '',
  `contractdescription` text NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `customerref` varchar(20) NOT NULL DEFAULT '',
  `margin` double NOT NULL DEFAULT '1',
  `wo` int(11) NOT NULL DEFAULT '0',
  `requireddate` date NOT NULL DEFAULT '0000-00-00',
  `drawing` varchar(50) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`),
  KEY `OrderNo` (`orderno`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `WO` (`wo`),
  KEY `loccode` (`loccode`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`debtorno`, `branchcode`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts`
--

LOCK TABLES `contracts` WRITE;
/*!40000 ALTER TABLE `contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `currency` char(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `country` char(50) NOT NULL DEFAULT '',
  `hundredsname` char(15) NOT NULL DEFAULT 'Cents',
  `decimalplaces` tinyint(3) NOT NULL DEFAULT '2',
  `rate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`currabrev`),
  KEY `Country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES ('Australian Dollars','AUD','Australia','cents',2,1),('Swiss Francs','CHF','Swizerland','centimes',2,1),('Euro','EUR','Euroland','cents',2,0.44),('Pounds','GBP','England','Pence',2,0.8),('US Dollars','USD','United States','Cents',2,1);
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custallocns`
--

DROP TABLE IF EXISTS `custallocns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custallocns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amt` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `datealloc` date NOT NULL DEFAULT '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL DEFAULT '0',
  `transid_allocto` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `DateAlloc` (`datealloc`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  CONSTRAINT `custallocns_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `debtortrans` (`id`),
  CONSTRAINT `custallocns_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `debtortrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custallocns`
--

LOCK TABLES `custallocns` WRITE;
/*!40000 ALTER TABLE `custallocns` DISABLE KEYS */;
/*!40000 ALTER TABLE `custallocns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custbranch`
--

DROP TABLE IF EXISTS `custbranch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custbranch` (
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `brname` varchar(40) NOT NULL DEFAULT '',
  `braddress1` varchar(40) NOT NULL DEFAULT '',
  `braddress2` varchar(40) NOT NULL DEFAULT '',
  `braddress3` varchar(40) NOT NULL DEFAULT '',
  `braddress4` varchar(50) NOT NULL DEFAULT '',
  `braddress5` varchar(20) NOT NULL DEFAULT '',
  `braddress6` varchar(15) NOT NULL DEFAULT '',
  `lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `lng` float(10,6) NOT NULL DEFAULT '0.000000',
  `estdeliverydays` smallint(6) NOT NULL DEFAULT '1',
  `area` char(3) NOT NULL,
  `salesman` varchar(4) NOT NULL DEFAULT '',
  `fwddate` smallint(6) NOT NULL DEFAULT '0',
  `phoneno` varchar(20) NOT NULL DEFAULT '',
  `faxno` varchar(20) NOT NULL DEFAULT '',
  `contactname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '1',
  `defaultshipvia` int(11) NOT NULL DEFAULT '1',
  `deliverblind` tinyint(1) DEFAULT '1',
  `disabletrans` tinyint(4) NOT NULL DEFAULT '0',
  `brpostaddr1` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr2` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr3` varchar(30) NOT NULL DEFAULT '',
  `brpostaddr4` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr5` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr6` varchar(15) NOT NULL DEFAULT '',
  `specialinstructions` mediumtext NOT NULL,
  `custbranchcode` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`branchcode`,`debtorno`),
  KEY `BrName` (`brname`),
  KEY `DebtorNo` (`debtorno`),
  KEY `Salesman` (`salesman`),
  KEY `Area` (`area`),
  KEY `DefaultLocation` (`defaultlocation`),
  KEY `DefaultShipVia` (`defaultshipvia`),
  KEY `taxgroupid` (`taxgroupid`),
  CONSTRAINT `custbranch_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`),
  CONSTRAINT `custbranch_ibfk_2` FOREIGN KEY (`area`) REFERENCES `areas` (`areacode`),
  CONSTRAINT `custbranch_ibfk_3` FOREIGN KEY (`salesman`) REFERENCES `salesman` (`salesmancode`),
  CONSTRAINT `custbranch_ibfk_4` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `custbranch_ibfk_6` FOREIGN KEY (`defaultshipvia`) REFERENCES `shippers` (`shipper_id`),
  CONSTRAINT `custbranch_ibfk_7` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custbranch`
--

LOCK TABLES `custbranch` WRITE;
/*!40000 ALTER TABLE `custbranch` DISABLE KEYS */;
/*!40000 ALTER TABLE `custbranch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custcontacts`
--

DROP TABLE IF EXISTS `custcontacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  PRIMARY KEY (`contid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custcontacts`
--

LOCK TABLES `custcontacts` WRITE;
/*!40000 ALTER TABLE `custcontacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `custcontacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custnotes`
--

DROP TABLE IF EXISTS `custnotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custnotes` (
  `noteid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` mediumtext NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custnotes`
--

LOCK TABLES `custnotes` WRITE;
/*!40000 ALTER TABLE `custnotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `custnotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `debtorsmaster`
--

DROP TABLE IF EXISTS `debtorsmaster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtorsmaster` (
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `currcode` char(3) NOT NULL DEFAULT '',
  `salestype` char(2) NOT NULL DEFAULT '',
  `clientsince` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `holdreason` smallint(6) NOT NULL DEFAULT '0',
  `paymentterms` char(2) NOT NULL DEFAULT 'f',
  `discount` double NOT NULL DEFAULT '0',
  `pymtdiscount` double NOT NULL DEFAULT '0',
  `lastpaid` double NOT NULL DEFAULT '0',
  `lastpaiddate` datetime DEFAULT NULL,
  `creditlimit` double NOT NULL DEFAULT '1000',
  `invaddrbranch` tinyint(4) NOT NULL DEFAULT '0',
  `discountcode` char(2) NOT NULL DEFAULT '',
  `ediinvoices` tinyint(4) NOT NULL DEFAULT '0',
  `ediorders` tinyint(4) NOT NULL DEFAULT '0',
  `edireference` varchar(20) NOT NULL DEFAULT '',
  `editransport` varchar(5) NOT NULL DEFAULT 'email',
  `ediaddress` varchar(50) NOT NULL DEFAULT '',
  `ediserveruser` varchar(20) NOT NULL DEFAULT '',
  `ediserverpwd` varchar(20) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `customerpoline` tinyint(1) NOT NULL DEFAULT '0',
  `typeid` tinyint(4) NOT NULL DEFAULT '1',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `debtorsmaster`
--

LOCK TABLES `debtorsmaster` WRITE;
/*!40000 ALTER TABLE `debtorsmaster` DISABLE KEYS */;
/*!40000 ALTER TABLE `debtorsmaster` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `debtortrans`
--

DROP TABLE IF EXISTS `debtortrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortrans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transno` int(11) NOT NULL DEFAULT '0',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `trandate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `inputdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `prd` smallint(6) NOT NULL DEFAULT '0',
  `settled` tinyint(4) NOT NULL DEFAULT '0',
  `reference` varchar(20) NOT NULL DEFAULT '',
  `tpe` char(2) NOT NULL DEFAULT '',
  `order_` int(11) NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '0',
  `ovamount` double NOT NULL DEFAULT '0',
  `ovgst` double NOT NULL DEFAULT '0',
  `ovfreight` double NOT NULL DEFAULT '0',
  `ovdiscount` double NOT NULL DEFAULT '0',
  `diffonexch` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  `invtext` mediumtext,
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `edisent` tinyint(4) NOT NULL DEFAULT '0',
  `consignment` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  KEY `Order_` (`order_`),
  KEY `Prd` (`prd`),
  KEY `Tpe` (`tpe`),
  KEY `Type` (`type`),
  KEY `Settled` (`settled`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type_2` (`type`,`transno`),
  KEY `EDISent` (`edisent`),
  CONSTRAINT `debtortrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `debtortrans_ibfk_3` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `debtortrans`
--

LOCK TABLES `debtortrans` WRITE;
/*!40000 ALTER TABLE `debtortrans` DISABLE KEYS */;
/*!40000 ALTER TABLE `debtortrans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `debtortranstaxes`
--

DROP TABLE IF EXISTS `debtortranstaxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortranstaxes` (
  `debtortransid` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`debtortransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `debtortranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `debtortranstaxes_ibfk_2` FOREIGN KEY (`debtortransid`) REFERENCES `debtortrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `debtortranstaxes`
--

LOCK TABLES `debtortranstaxes` WRITE;
/*!40000 ALTER TABLE `debtortranstaxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `debtortranstaxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `debtortype`
--

DROP TABLE IF EXISTS `debtortype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `debtortype`
--

LOCK TABLES `debtortype` WRITE;
/*!40000 ALTER TABLE `debtortype` DISABLE KEYS */;
/*!40000 ALTER TABLE `debtortype` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `debtortypenotes`
--

DROP TABLE IF EXISTS `debtortypenotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortypenotes` (
  `noteid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typeid` tinyint(4) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` varchar(200) NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `debtortypenotes`
--

LOCK TABLES `debtortypenotes` WRITE;
/*!40000 ALTER TABLE `debtortypenotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `debtortypenotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliverynotes`
--

DROP TABLE IF EXISTS `deliverynotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliverynotes` (
  `deliverynotenumber` int(11) NOT NULL,
  `deliverynotelineno` tinyint(4) NOT NULL,
  `salesorderno` int(11) NOT NULL,
  `salesorderlineno` int(11) NOT NULL,
  `qtydelivered` double NOT NULL DEFAULT '0',
  `printed` tinyint(4) NOT NULL DEFAULT '0',
  `invoiced` tinyint(4) NOT NULL DEFAULT '0',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`deliverynotenumber`,`deliverynotelineno`),
  KEY `deliverynotes_ibfk_2` (`salesorderno`,`salesorderlineno`),
  CONSTRAINT `deliverynotes_ibfk_1` FOREIGN KEY (`salesorderno`) REFERENCES `salesorders` (`orderno`),
  CONSTRAINT `deliverynotes_ibfk_2` FOREIGN KEY (`salesorderno`, `salesorderlineno`) REFERENCES `salesorderdetails` (`orderno`, `orderlineno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliverynotes`
--

LOCK TABLES `deliverynotes` WRITE;
/*!40000 ALTER TABLE `deliverynotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `deliverynotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discountmatrix`
--

DROP TABLE IF EXISTS `discountmatrix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discountmatrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT '1',
  `discountrate` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`salestype`,`discountcategory`,`quantitybreak`),
  KEY `QuantityBreak` (`quantitybreak`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `SalesType` (`salestype`),
  CONSTRAINT `discountmatrix_ibfk_1` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discountmatrix`
--

LOCK TABLES `discountmatrix` WRITE;
/*!40000 ALTER TABLE `discountmatrix` DISABLE KEYS */;
/*!40000 ALTER TABLE `discountmatrix` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edi_orders_seg_groups`
--

DROP TABLE IF EXISTS `edi_orders_seg_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint(4) NOT NULL DEFAULT '0',
  `maxoccur` int(4) NOT NULL DEFAULT '0',
  `parentseggroup` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`seggroupno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edi_orders_seg_groups`
--

LOCK TABLES `edi_orders_seg_groups` WRITE;
/*!40000 ALTER TABLE `edi_orders_seg_groups` DISABLE KEYS */;
INSERT INTO `edi_orders_seg_groups` VALUES (0,1,0),(1,9999,0),(2,99,0),(3,99,2),(5,5,2),(6,5,0),(7,5,0),(8,10,0),(9,9999,8),(10,10,0),(11,10,10),(12,5,0),(13,99,0),(14,5,13),(15,10,0),(19,99,0),(20,1,19),(21,1,19),(22,2,19),(23,1,19),(24,5,19),(28,200000,0),(32,25,28),(33,9999,28),(34,99,28),(36,5,34),(37,9999,28),(38,10,28),(39,999,28),(42,5,39),(43,99,28),(44,1,43),(45,1,43),(46,2,43),(47,1,43),(48,5,43),(49,10,28),(50,1,0);
/*!40000 ALTER TABLE `edi_orders_seg_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edi_orders_segs`
--

DROP TABLE IF EXISTS `edi_orders_segs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edi_orders_segs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `segtag` char(3) NOT NULL DEFAULT '',
  `seggroup` tinyint(4) NOT NULL DEFAULT '0',
  `maxoccur` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `SegTag` (`segtag`),
  KEY `SegNo` (`seggroup`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edi_orders_segs`
--

LOCK TABLES `edi_orders_segs` WRITE;
/*!40000 ALTER TABLE `edi_orders_segs` DISABLE KEYS */;
INSERT INTO `edi_orders_segs` VALUES (1,'UNB',0,1),(2,'UNH',0,1),(3,'BGM',0,1),(4,'DTM',0,35),(5,'PAI',0,1),(6,'ALI',0,5),(7,'FTX',0,99),(8,'RFF',1,1),(9,'DTM',1,5),(10,'NAD',2,1),(11,'LOC',2,99),(12,'FII',2,5),(13,'RFF',3,1),(14,'CTA',5,1),(15,'COM',5,5),(16,'TAX',6,1),(17,'MOA',6,1),(18,'CUX',7,1),(19,'DTM',7,5),(20,'PAT',8,1),(21,'DTM',8,5),(22,'PCD',8,1),(23,'MOA',9,1),(24,'TDT',10,1),(25,'LOC',11,1),(26,'DTM',11,5),(27,'TOD',12,1),(28,'LOC',12,2),(29,'PAC',13,1),(30,'PCI',14,1),(31,'RFF',14,1),(32,'DTM',14,5),(33,'GIN',14,10),(34,'EQD',15,1),(35,'ALC',19,1),(36,'ALI',19,5),(37,'DTM',19,5),(38,'QTY',20,1),(39,'RNG',20,1),(40,'PCD',21,1),(41,'RNG',21,1),(42,'MOA',22,1),(43,'RNG',22,1),(44,'RTE',23,1),(45,'RNG',23,1),(46,'TAX',24,1),(47,'MOA',24,1),(48,'LIN',28,1),(49,'PIA',28,25),(50,'IMD',28,99),(51,'MEA',28,99),(52,'QTY',28,99),(53,'ALI',28,5),(54,'DTM',28,35),(55,'MOA',28,10),(56,'GIN',28,127),(57,'QVR',28,1),(58,'FTX',28,99),(59,'PRI',32,1),(60,'CUX',32,1),(61,'DTM',32,5),(62,'RFF',33,1),(63,'DTM',33,5),(64,'PAC',34,1),(65,'QTY',34,5),(66,'PCI',36,1),(67,'RFF',36,1),(68,'DTM',36,5),(69,'GIN',36,10),(70,'LOC',37,1),(71,'QTY',37,1),(72,'DTM',37,5),(73,'TAX',38,1),(74,'MOA',38,1),(75,'NAD',39,1),(76,'CTA',42,1),(77,'COM',42,5),(78,'ALC',43,1),(79,'ALI',43,5),(80,'DTM',43,5),(81,'QTY',44,1),(82,'RNG',44,1),(83,'PCD',45,1),(84,'RNG',45,1),(85,'MOA',46,1),(86,'RNG',46,1),(87,'RTE',47,1),(88,'RNG',47,1),(89,'TAX',48,1),(90,'MOA',48,1),(91,'TDT',49,1),(92,'UNS',50,1),(93,'MOA',50,1),(94,'CNT',50,1),(95,'UNT',50,1);
/*!40000 ALTER TABLE `edi_orders_segs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ediitemmapping`
--

DROP TABLE IF EXISTS `ediitemmapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ediitemmapping` (
  `supporcust` varchar(4) NOT NULL DEFAULT '',
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `partnerstockid` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`supporcust`,`partnercode`,`stockid`),
  KEY `PartnerCode` (`partnercode`),
  KEY `StockID` (`stockid`),
  KEY `PartnerStockID` (`partnerstockid`),
  KEY `SuppOrCust` (`supporcust`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ediitemmapping`
--

LOCK TABLES `ediitemmapping` WRITE;
/*!40000 ALTER TABLE `ediitemmapping` DISABLE KEYS */;
/*!40000 ALTER TABLE `ediitemmapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edimessageformat`
--

DROP TABLE IF EXISTS `edimessageformat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edimessageformat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `messagetype` varchar(6) NOT NULL DEFAULT '',
  `section` varchar(7) NOT NULL DEFAULT '',
  `sequenceno` int(11) NOT NULL DEFAULT '0',
  `linetext` varchar(70) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `PartnerCode` (`partnercode`,`messagetype`,`sequenceno`),
  KEY `Section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edimessageformat`
--

LOCK TABLES `edimessageformat` WRITE;
/*!40000 ALTER TABLE `edimessageformat` DISABLE KEYS */;
/*!40000 ALTER TABLE `edimessageformat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailsettings`
--

DROP TABLE IF EXISTS `emailsettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailsettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(30) NOT NULL,
  `port` char(5) NOT NULL,
  `heloaddress` varchar(20) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `timeout` int(11) DEFAULT '5',
  `companyname` varchar(50) DEFAULT NULL,
  `auth` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailsettings`
--

LOCK TABLES `emailsettings` WRITE;
/*!40000 ALTER TABLE `emailsettings` DISABLE KEYS */;
INSERT INTO `emailsettings` VALUES (1,'localhost','25','helo','','',5,'',0);
/*!40000 ALTER TABLE `emailsettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `factorcompanies`
--

DROP TABLE IF EXISTS `factorcompanies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `factorcompanies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coyname` varchar(50) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `contact` varchar(25) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `factor_name` (`coyname`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `factorcompanies`
--

LOCK TABLES `factorcompanies` WRITE;
/*!40000 ALTER TABLE `factorcompanies` DISABLE KEYS */;
/*!40000 ALTER TABLE `factorcompanies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favourites`
--

DROP TABLE IF EXISTS `favourites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favourites` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `caption` varchar(50) NOT NULL DEFAULT '',
  `href` varchar(200) NOT NULL DEFAULT '#',
  PRIMARY KEY (`userid`,`caption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favourites`
--

LOCK TABLES `favourites` WRITE;
/*!40000 ALTER TABLE `favourites` DISABLE KEYS */;
/*!40000 ALTER TABLE `favourites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fixedassetcategories`
--

DROP TABLE IF EXISTS `fixedassetcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` int(11) NOT NULL DEFAULT '0',
  `depnact` int(11) NOT NULL DEFAULT '0',
  `disposalact` int(11) NOT NULL DEFAULT '80000',
  `accumdepnact` int(11) NOT NULL DEFAULT '0',
  `defaultdepnrate` double NOT NULL DEFAULT '0.2',
  `defaultdepntype` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixedassetcategories`
--

LOCK TABLES `fixedassetcategories` WRITE;
/*!40000 ALTER TABLE `fixedassetcategories` DISABLE KEYS */;
/*!40000 ALTER TABLE `fixedassetcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fixedassetlocations`
--

DROP TABLE IF EXISTS `fixedassetlocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassetlocations` (
  `locationid` char(6) NOT NULL DEFAULT '',
  `locationdescription` char(20) NOT NULL DEFAULT '',
  `parentlocationid` char(6) DEFAULT '',
  PRIMARY KEY (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixedassetlocations`
--

LOCK TABLES `fixedassetlocations` WRITE;
/*!40000 ALTER TABLE `fixedassetlocations` DISABLE KEYS */;
/*!40000 ALTER TABLE `fixedassetlocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fixedassets`
--

DROP TABLE IF EXISTS `fixedassets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassets` (
  `assetid` int(11) NOT NULL AUTO_INCREMENT,
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `assetlocation` varchar(6) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT '0',
  `accumdepn` double NOT NULL DEFAULT '0',
  `datepurchased` date NOT NULL DEFAULT '0000-00-00',
  `disposalproceeds` double NOT NULL DEFAULT '0',
  `assetcategoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `depntype` int(11) NOT NULL DEFAULT '1',
  `depnrate` double NOT NULL DEFAULT '0',
  `barcode` varchar(30) NOT NULL DEFAULT '',
  `disposaldate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`assetid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixedassets`
--

LOCK TABLES `fixedassets` WRITE;
/*!40000 ALTER TABLE `fixedassets` DISABLE KEYS */;
/*!40000 ALTER TABLE `fixedassets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fixedassettrans`
--

DROP TABLE IF EXISTS `fixedassettrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassettrans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assetid` int(11) NOT NULL,
  `transtype` tinyint(4) NOT NULL,
  `transdate` date NOT NULL,
  `transno` int(11) NOT NULL,
  `periodno` smallint(6) NOT NULL,
  `inputdate` date NOT NULL,
  `fixedassettranstype` varchar(8) NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assetid` (`assetid`,`transtype`,`transno`),
  KEY `inputdate` (`inputdate`),
  KEY `transdate` (`transdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixedassettrans`
--

LOCK TABLES `fixedassettrans` WRITE;
/*!40000 ALTER TABLE `fixedassettrans` DISABLE KEYS */;
/*!40000 ALTER TABLE `fixedassettrans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `freightcosts`
--

DROP TABLE IF EXISTS `freightcosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `freightcosts` (
  `shipcostfromid` int(11) NOT NULL AUTO_INCREMENT,
  `locationfrom` varchar(5) NOT NULL DEFAULT '',
  `destination` varchar(40) NOT NULL DEFAULT '',
  `shipperid` int(11) NOT NULL DEFAULT '0',
  `cubrate` double NOT NULL DEFAULT '0',
  `kgrate` double NOT NULL DEFAULT '0',
  `maxkgs` double NOT NULL DEFAULT '999999',
  `maxcub` double NOT NULL DEFAULT '999999',
  `fixedprice` double NOT NULL DEFAULT '0',
  `minimumchg` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipcostfromid`),
  KEY `Destination` (`destination`),
  KEY `LocationFrom` (`locationfrom`),
  KEY `ShipperID` (`shipperid`),
  KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`),
  CONSTRAINT `freightcosts_ibfk_1` FOREIGN KEY (`locationfrom`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `freightcosts_ibfk_2` FOREIGN KEY (`shipperid`) REFERENCES `shippers` (`shipper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `freightcosts`
--

LOCK TABLES `freightcosts` WRITE;
/*!40000 ALTER TABLE `freightcosts` DISABLE KEYS */;
/*!40000 ALTER TABLE `freightcosts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geocode_param`
--

DROP TABLE IF EXISTS `geocode_param`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geocode_param` (
  `geocodeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `geocode_key` varchar(200) NOT NULL DEFAULT '',
  `center_long` varchar(20) NOT NULL DEFAULT '',
  `center_lat` varchar(20) NOT NULL DEFAULT '',
  `map_height` varchar(10) NOT NULL DEFAULT '',
  `map_width` varchar(10) NOT NULL DEFAULT '',
  `map_host` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`geocodeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geocode_param`
--

LOCK TABLES `geocode_param` WRITE;
/*!40000 ALTER TABLE `geocode_param` DISABLE KEYS */;
/*!40000 ALTER TABLE `geocode_param` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gltrans`
--

DROP TABLE IF EXISTS `gltrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gltrans` (
  `counterindex` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) NOT NULL DEFAULT '0',
  `typeno` bigint(16) NOT NULL DEFAULT '1',
  `chequeno` int(11) NOT NULL DEFAULT '0',
  `trandate` date NOT NULL DEFAULT '0000-00-00',
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `account` int(11) NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `posted` tinyint(4) NOT NULL DEFAULT '0',
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `tag` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`counterindex`),
  KEY `Account` (`account`),
  KEY `ChequeNo` (`chequeno`),
  KEY `PeriodNo` (`periodno`),
  KEY `Posted` (`posted`),
  KEY `TranDate` (`trandate`),
  KEY `TypeNo` (`typeno`),
  KEY `Type_and_Number` (`type`,`typeno`),
  KEY `JobRef` (`jobref`),
  CONSTRAINT `gltrans_ibfk_1` FOREIGN KEY (`account`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `gltrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `gltrans_ibfk_3` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gltrans`
--

LOCK TABLES `gltrans` WRITE;
/*!40000 ALTER TABLE `gltrans` DISABLE KEYS */;
/*!40000 ALTER TABLE `gltrans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grns`
--

DROP TABLE IF EXISTS `grns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grns` (
  `grnbatch` smallint(6) NOT NULL DEFAULT '0',
  `grnno` int(11) NOT NULL AUTO_INCREMENT,
  `podetailitem` int(11) NOT NULL DEFAULT '0',
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `itemdescription` varchar(100) NOT NULL DEFAULT '',
  `qtyrecd` double NOT NULL DEFAULT '0',
  `quantityinv` double NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stdcostunit` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`grnno`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `ItemCode` (`itemcode`),
  KEY `PODetailItem` (`podetailitem`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `grns_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `grns_ibfk_2` FOREIGN KEY (`podetailitem`) REFERENCES `purchorderdetails` (`podetailitem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grns`
--

LOCK TABLES `grns` WRITE;
/*!40000 ALTER TABLE `grns` DISABLE KEYS */;
/*!40000 ALTER TABLE `grns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holdreasons`
--

DROP TABLE IF EXISTS `holdreasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holdreasons` (
  `reasoncode` smallint(6) NOT NULL DEFAULT '1',
  `reasondescription` char(30) NOT NULL DEFAULT '',
  `dissallowinvoices` tinyint(4) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`reasoncode`),
  KEY `ReasonCode` (`reasoncode`),
  KEY `ReasonDescription` (`reasondescription`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holdreasons`
--

LOCK TABLES `holdreasons` WRITE;
/*!40000 ALTER TABLE `holdreasons` DISABLE KEYS */;
INSERT INTO `holdreasons` VALUES (1,'Good History',0),(20,'Watch',0),(51,'In liquidation',1);
/*!40000 ALTER TABLE `holdreasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lastcostrollup`
--

DROP TABLE IF EXISTS `lastcostrollup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lastcostrollup` (
  `stockid` char(20) NOT NULL DEFAULT '',
  `totalonhand` double NOT NULL DEFAULT '0',
  `matcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `labcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `oheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockact` int(11) NOT NULL DEFAULT '0',
  `adjglact` int(11) NOT NULL DEFAULT '0',
  `newmatcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `newlabcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `newoheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lastcostrollup`
--

LOCK TABLES `lastcostrollup` WRITE;
/*!40000 ALTER TABLE `lastcostrollup` DISABLE KEYS */;
/*!40000 ALTER TABLE `lastcostrollup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `locationname` varchar(50) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) NOT NULL DEFAULT '',
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `taxprovinceid` tinyint(4) NOT NULL DEFAULT '1',
  `cashsalecustomer` varchar(10) NOT NULL DEFAULT '',
  `cashsalebranch` varchar(10) NOT NULL DEFAULT '',
  `managed` int(11) DEFAULT '0',
  PRIMARY KEY (`loccode`),
  KEY `taxprovinceid` (`taxprovinceid`),
  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`taxprovinceid`) REFERENCES `taxprovinces` (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
INSERT INTO `locations` VALUES ('MEL','Melbourne','1234 Collins Street','Melbourne','Victoria 2345','','','Australia','+61 3 56789012','+61 3 56789013','jacko@webdemo.com','Jack Roberts',1,'','',0),('TOR','Toronto','Level 100 ','CN Tower','Toronto','','','','','','','Clive Contrary',1,'','',1);
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locstock`
--

DROP TABLE IF EXISTS `locstock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locstock` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `reorderlevel` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`loccode`,`stockid`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `locstock_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `locstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locstock`
--

LOCK TABLES `locstock` WRITE;
/*!40000 ALTER TABLE `locstock` DISABLE KEYS */;
/*!40000 ALTER TABLE `locstock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loctransfers`
--

DROP TABLE IF EXISTS `loctransfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loctransfers` (
  `reference` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `shipqty` double NOT NULL DEFAULT '0',
  `recqty` double NOT NULL DEFAULT '0',
  `shipdate` date NOT NULL DEFAULT '0000-00-00',
  `recdate` date NOT NULL DEFAULT '0000-00-00',
  `shiploc` varchar(7) NOT NULL DEFAULT '',
  `recloc` varchar(7) NOT NULL DEFAULT '',
  KEY `Reference` (`reference`,`stockid`),
  KEY `ShipLoc` (`shiploc`),
  KEY `RecLoc` (`recloc`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `loctransfers_ibfk_1` FOREIGN KEY (`shiploc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_2` FOREIGN KEY (`recloc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores Shipments To And From Locations';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loctransfers`
--

LOCK TABLES `loctransfers` WRITE;
/*!40000 ALTER TABLE `loctransfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `loctransfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL DEFAULT '0',
  `caption` varchar(70) NOT NULL DEFAULT '',
  `parent` int(11) NOT NULL DEFAULT '-1',
  `href` varchar(200) NOT NULL DEFAULT '#',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (1,'Sales',-1,'#'),(2,'Transactions',1,'#'),(3,'Enter An Order or Quotation',2,'SelectOrderItems.php?&amp;NewOrder=Yes'),(4,'Enter Counter Sales',2,'CounterSales.php?'),(5,'Print Picking Lists',2,'PDFPickingList.php?'),(6,'Outstanding Sales Orders/Quotations',2,'SelectSalesOrder.php?'),(7,'Special Order',2,'SpecialOrder.php?&amp;NewSpecial=Yes'),(8,'Recurring Order Template',2,'SelectRecurringSalesOrder.php?'),(9,'Process Recurring Orders',2,'RecurringSalesOrdersProcess.php?'),(10,'Inquiries and Reports ',1,'#'),(11,'Order Inquiry',10,'SelectCompletedOrder.php?'),(12,'Print Price Lists',10,'PDFPriceList.php?'),(13,'Order Status Report',10,'PDFOrderStatus.php?'),(14,'Orders Invoiced Reports',10,'PDFOrdersInvoiced.php?'),(15,'Daily Sales Inquiry',10,'DailySalesInquiry.php?'),(16,'Order Delivery Differences Report',10,'PDFDeliveryDifferences.php?'),(17,'Delivery In Full On Time (DIFOT) Report',10,'PDFDIFOT.php?'),(18,'Sales Order Detail Or Summary Inquiries',10,'SalesInquiry.php?'),(19,'Top Sales Items Report',10,'TopItems.php?'),(20,'Sales With Low Gross Profit Report',10,'PDFLowGP.php?'),(21,'Maintenance',1,'#'),(22,'Select Contract',21,'SelectContract.php?'),(23,'Create Contract',21,'Contracts.php?'),(24,'Receivables',-1,'#'),(25,'Transactions',24,'#'),(26,'Select Order to Invoice',25,'SelectSalesOrder.php?'),(27,'Create A Credit Note',25,'SelectCreditItems.php?&amp;NewCredit=Yes'),(28,'Enter Receipts',25,'CustomerReceipt.php?&amp;NewReceipt=Yes&amp;Type=Customer'),(29,'Allocate Receipts or Credit Notes',25,'CustomerAllocations.php?'),(30,'Inquiries and Reports',24,'#'),(31,'Customer Transaction Inquiries',30,'SelectCustomer.php?'),(32,'Where Allocated Inquiry',30,'CustWhereAlloc.php?'),(33,'Print Invoices or Credit Notes',30,'PrintCustTransPortrait.php?'),(34,'Print Statements',30,'PrintCustStatements.php?'),(35,'Sales Analysis Reports',30,'SalesAnalRepts.php?'),(36,'Aged Customer Balances/Overdues Report',30,'AgedDebtors.php?'),(37,'Transaction Inquiries',30,'CustomerTransInquiry.php?'),(38,'Re-Print A Deposit Listing',30,'PDFBankingSummary.php?'),(39,'Debtor Balances At A Prior Month End',30,'DebtorsAtPeriodEnd.php?'),(40,'Customer Listing By Area/Salesperson',30,'PDFCustomerList.php?'),(41,'Sales Graphs',30,'SalesGraph.php?'),(42,'List Daily Transactions',30,'PDFCustTransListing.php?'),(43,'Maintenance',24,'#'),(44,'Add Customer',43,'Customers.php?'),(45,'Customers',43,'SelectCustomer.php?'),(46,'Payables',-1,'#'),(47,'Transactions',46,'#'),(48,'Select Supplier',47,'SelectSupplier.php?'),(49,'Supplier Allocations',47,'SupplierAllocations.php?'),(50,'Inquiries and Reports',46,'#'),(51,'Aged Supplier Report',50,'AgedSuppliers.php?'),(52,'Payment Run Report',50,'SuppPaymentRun.php?'),(53,'Remittance Advices',50,'PDFRemittanceAdvice.php?'),(54,'Outstanding GRNs Report',50,'OutstandingGRNs.php?'),(55,'Supplier Balances At A Prior Month End',50,'SupplierBalsAtPeriodEnd.php?'),(56,'List Daily Transactions',50,'PDFSuppTransListing.php?'),(57,'Supplier Transaction Inquiries',50,'SupplierTransInquiry.php?'),(58,'Maintenance',46,'#'),(59,'Add Supplier',58,'Suppliers.php?'),(60,'Maintain Factor Companies',58,'Factors.php?'),(61,'Purchases',-1,'#'),(62,'Transactions',61,'#'),(63,'Purchase Orders',62,'PO_SelectOSPurchOrder.php?'),(64,'Add Purchase Order',62,'PO_Header.php?&amp;NewOrder=Yes'),(65,'Process Tenders and Offers',62,'OffersReceived.php?'),(66,'Orders to Authorise',62,'PO_AuthoriseMyOrders.php?'),(67,'Shipment Entry',62,'SelectSupplier.php?'),(68,'Select A Shipment',62,'Shipt_Select.php?'),(69,'Inquiries and Reports',61,'#'),(70,'Purchase Order Inquiry',69,'PO_SelectPurchOrder.php?'),(71,'Purchase Order Detail Or Summary Inquiries',69,'POReport.php?'),(72,'Inventory',-1,'#'),(73,'Transactions',72,'#'),(74,'Receive Purchase Orders',73,'PO_SelectOSPurchOrder.php?'),(75,'Bulk Inventory Transfer - Dispatch',73,'StockLocTransfer.php'),(76,'Bulk Inventory Transfer - Receive',73,'StockLocTransferReceive.php?'),(77,'Inventory Location Transfers',73,'StockTransfers.php?'),(78,'Inventory Adjustments',73,'StockAdjustments.php?&amp;NewAdjustment=Yes'),(79,'Reverse Goods Received',73,'ReverseGRN.php?'),(80,'Enter Stock Counts',73,'StockCounts.php?'),(81,'Inquiries and Reports',72,'#'),(82,'Serial Item Research Tool',81,'StockSerialItemResearch.php?'),(83,'Print Price Labels',81,'PDFPrintLabel.php?'),(84,'Inventory Item Movements',81,'StockMovements.php?'),(85,'Inventory Item Status',81,'StockStatus.php?'),(86,'Inventory Item Usage',81,'StockUsage.php?'),(87,'Inventory Quantities',81,'InventoryQuantities.php?'),(88,'Reorder Level',81,'ReorderLevel.php?'),(89,'Reorder Level By Category/Location',81,'ReorderLevelLocation.php?'),(90,'Stock Dispatch',81,'StockDispatch.php?'),(91,'Inventory Valuation Report',81,'InventoryValuation.php?'),(92,'Inventory Planning Report',81,'InventoryPlanning.php?'),(93,'Inventory Planning Based On Preferred Supplier Data',81,'InventoryPlanningPrefSupplier.php?'),(94,'Inventory Stock Check Sheets',81,'StockCheck.php?'),(95,'Make Inventory Quantities CSV',81,'StockQties_csv.php?'),(96,'Compare Counts Vs Stock Check Data',81,'PDFStockCheckComparison.php?'),(97,'All Inventory Movements By Location/Date',81,'StockLocMovements.php?'),(98,'List Inventory Status By Location/Category',81,'StockLocStatus.php?'),(99,'Historical Stock Quantity By Location/Category',81,'StockQuantityByDate.php?'),(100,'List Negative Stocks',81,'PDFStockNegatives.php?'),(101,'Daily Stock Transaction Listing',81,'PDFStockTransListing.php?'),(102,'Maintenance',72,'#'),(103,'Add A New Item',102,'Stocks.php?'),(104,'Select An Item',102,'SelectProduct.php?'),(105,'Sales Category Maintenance',102,'SalesCategories.php?'),(106,'Add or Update Prices Based On Costs',102,'PricesBasedOnMarkUp.php?'),(107,'View or Update Prices Based On Costs',102,'PricesByCost.php?'),(108,'Manufacturing',-1,'#'),(109,'Transactions',108,'#'),(110,'Work Order Entry',109,'WorkOrderEntry.php?'),(111,'Select A Work Order',109,'SelectWorkOrder.php?'),(112,'Inquiries and Reports',108,'#'),(113,'Select A Work Order',112,'SelectWorkOrder.php?'),(114,'Costed Bill Of Material Inquiry',112,'BOMInquiry.php?'),(115,'Where Used Inquiry',112,'WhereUsedInquiry.php?'),(116,'Indented Bill Of Material Listing',112,'BOMIndented.php?'),(117,'List Components Required',112,'BOMExtendedQty.php?'),(118,'Indented Where Used Listing',112,'BOMIndentedReverse.php?'),(119,'MRP',112,'MRPReport.php?'),(120,'MRP Shortages',112,'MRPShortages.php?'),(121,'MRP Suggested Purchase Orders',112,'MRPPlannedPurchaseOrders.php?'),(122,'MRP Suggested Work Orders',112,'MRPPlannedWorkOrders.php?'),(123,'MRP Reschedules Required',112,'MRPReschedules.php?'),(124,'Maintenance',108,'#'),(125,'Work Centre',124,'WorkCentres.php?'),(126,'Bills Of Material',124,'BOMs.php?'),(127,'Master Schedule',124,'MRPDemands.php?'),(128,'Auto Create Master Schedule',124,'MRPCreateDemands.php?'),(129,'MRP Calculation',124,'MRP.php?'),(130,'General Ledger',-1,'#'),(131,'Transactions',130,'#'),(132,'Bank Account Payments Entry',131,'Payments.php?&amp;NewPayment=Yes'),(133,'Bank Account Receipts Entry',131,'CustomerReceipt.php?&amp;NewReceipt=Yes&amp;Type=GL'),(134,'Journal Entry',131,'GLJournal.php?&amp;NewJournal=Yes'),(135,'Bank Account Payments Matching',131,'BankMatching.php?&amp;Type=Payments'),(136,'Bank Account Receipts Matching',131,'BankMatching.php?&amp;Type=Receipts'),(137,'Inquiries and Reports',130,'#'),(138,'Trial Balance',137,'GLTrialBalance.php?'),(139,'Account Inquiry',137,'SelectGLAccount.php?'),(140,'Account Listing',137,'GLAccountReport.php?'),(141,'Account Listing to CSV File',137,'GLAccountCSV.php?'),(142,'Bank Account Reconciliation Statement',137,'BankReconciliation.php?'),(143,'Cheque Payments Listing',137,'PDFChequeListing.php?'),(144,'Daily Bank Transactions',137,'DailyBankTransactions.php?'),(145,'Profit and Loss Statement',137,'GLProfit_Loss.php?'),(146,'Balance Sheet',137,'GLBalanceSheet.php?'),(147,'Tag Reports',137,'GLTagProfit_Loss.php?'),(148,'Tax Reports',137,'Tax.php?'),(149,'Maintenance',130,'#'),(150,'GL Account',149,'GLAccounts.php?'),(151,'GL Budgets',149,'GLBudgets.php?'),(152,'Account Groups',149,'AccountGroups.php?'),(153,'Account Sections',149,'AccountSections.php?'),(154,'GL Tags',149,'GLTags.php?'),(155,'Asset Manager',-1,'#'),(156,'Transactions',155,'#'),(157,'Add a new Asset',156,'FixedAssetItems.php?'),(158,'Select an Asset',156,'SelectAsset.php?'),(159,'Change Asset Location',156,'FixedAssetTransfer.php?'),(160,'Depreciation Journal',156,'FixedAssetDepreciation.php'),(161,'Inquiries and Reports',155,'#'),(162,'Asset Register',161,'FixedAssetRegister.php?'),(163,'Maintenance',155,'#'),(164,'Asset Categories Maintenance',163,'FixedAssetCategories.php?'),(165,'Add or Maintain Asset Locations',163,'FixedAssetLocations.php?'),(166,'Petty Cash',-1,'#'),(167,'Transactions',166,'#'),(168,'Assign Cash to PC Tab',167,'PcAssignCashToTab.php?'),(169,'Claim Expenses From PC Tab',167,'PcClaimExpensesFromTab.php'),(170,'Expenses Authorisation',167,'PcAuthorizeExpenses.php?'),(171,'Inquiries and Reports',166,'#'),(172,'PC Tab General Report',171,'PcReportTab.php?'),(173,'Maintenance',166,'#'),(174,'Types of PC Tabs',173,'PcTypeTabs.php?'),(175,'PC Tabs',173,'PcTabs.php?'),(176,'PC Expenses',173,'PcExpenses.php?'),(177,'Expenses for Type of PC Tab',173,'PcExpensesTypeTab.php?'),(178,'Configuration',-1,'#'),(179,'General Settings',178,'#'),(180,'Company Preferences',179,'CompanyPreferences.php?'),(181,'Configuration Settings',179,'SystemParameters.php?'),(182,'Users',178,'#'),(183,'User Maintenance',182,'WWW_Users.php?'),(184,'Role Permissions',182,'WWW_Access.php?'),(185,'Menu Access Rights',182,'MenuAccess.php?'),(186,'General Ledger Setup',178,'#'),(187,'Bank Accounts',186,'BankAccounts.php?'),(188,'Page Security Settings',179,'PageSecurity.php?'),(189,'Currency Maintenance',186,'Currencies.php?'),(190,'Tax Authorities and Rates Maintenance',186,'TaxAuthorities.php?'),(191,'Tax Group Maintenance',186,'TaxGroups.php?'),(192,'Dispatch Tax Province Maintenance',186,'TaxProvinces.php?'),(193,'Tax Category Maintenance',186,'TaxCategories.php?'),(194,'List Periods Defined (Periods are automatically maintained)',186,'PeriodsInquiry.php?'),(195,'Report Builder Tool',179,'ReportCreator.php?'),(196,'View Audit Trail',179,'AuditTrail.php?'),(197,'View System Check',179,'SystemCheck.php?'),(198,'Geocode Setup',179,'GeocodeSetup.php?'),(199,'Form Layout Editor',179,'FormDesigner.php?'),(200,'Label Templates Maintenance',179,'Labels.php?'),(201,'SMTP Server Details',179,'SMTPServer.php?'),(202,'Sales/Receivables Setup',178,'#'),(203,'Sales Types',202,'SalesTypes.php?'),(204,'Customer Types',202,'CustomerTypes.php?'),(205,'Credit Status',202,'CreditStatus.php?'),(206,'Customer Payment Terms',202,'PaymentTerms.php?'),(207,'Customer Payment Methods',202,'PaymentMethods.php?'),(208,'Sales People',202,'SalesPeople.php?'),(209,'Sales Areas',202,'Areas.php?'),(210,'Sales GL Interface Postings',202,'SalesGLPostings.php?'),(211,'COGS GL Interface Postings',202,'COGSGLPostings.php?'),(212,'Purchases/Payables Setup',178,'#'),(213,'Supplier Types',212,'SupplierTypes.php?'),(214,'Supplier Payment Terms',212,'PaymentTerms.php?'),(215,'Set Purchase Order Authorisation levels',212,'PO_AuthorisationLevels.php?'),(216,'Supplier Payment Methods',212,'PaymentMethods.php?'),(217,'Shippers',212,'Shippers.php?'),(218,'Freight Costs Maintenance',212,'FreightCosts.php?'),(219,'Discount Matrix',202,'DiscountMatrix.php?'),(220,'Inventory Setup',178,'#'),(221,'Inventory Categories Maintenance',220,'StockCategories.php?'),(222,'Inventory Locations Maintenance',220,'Locations.php?'),(223,'Discount Category Maintenance',220,'DiscountCategories.php?'),(224,'Units of Measure',220,'UnitsOfMeasure.php?'),(225,'Manufacturing Setup',178,'#'),(226,'MRP Available Production Days',225,'MRPCalendar.php?'),(227,'MRP Demand Types',225,'MRPDemandTypes.php?');
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mrpcalendar`
--

DROP TABLE IF EXISTS `mrpcalendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpcalendar` (
  `calendardate` date NOT NULL,
  `daynumber` int(6) NOT NULL,
  `manufacturingflag` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`calendardate`),
  KEY `daynumber` (`daynumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mrpcalendar`
--

LOCK TABLES `mrpcalendar` WRITE;
/*!40000 ALTER TABLE `mrpcalendar` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrpcalendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mrpdemands`
--

DROP TABLE IF EXISTS `mrpdemands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpdemands` (
  `demandid` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `duedate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`demandid`),
  KEY `StockID` (`stockid`),
  KEY `mrpdemands_ibfk_1` (`mrpdemandtype`),
  CONSTRAINT `mrpdemands_ibfk_1` FOREIGN KEY (`mrpdemandtype`) REFERENCES `mrpdemandtypes` (`mrpdemandtype`),
  CONSTRAINT `mrpdemands_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mrpdemands`
--

LOCK TABLES `mrpdemands` WRITE;
/*!40000 ALTER TABLE `mrpdemands` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrpdemands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mrpdemandtypes`
--

DROP TABLE IF EXISTS `mrpdemandtypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `description` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`mrpdemandtype`),
  KEY `mrpdemandtype` (`mrpdemandtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mrpdemandtypes`
--

LOCK TABLES `mrpdemandtypes` WRITE;
/*!40000 ALTER TABLE `mrpdemandtypes` DISABLE KEYS */;
/*!40000 ALTER TABLE `mrpdemandtypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offers` (
  `offerid` int(11) NOT NULL AUTO_INCREMENT,
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `uom` varchar(15) NOT NULL DEFAULT '',
  `price` double NOT NULL DEFAULT '0',
  `expirydate` date NOT NULL DEFAULT '0000-00-00',
  `currcode` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`offerid`),
  KEY `offers_ibfk_1` (`supplierid`),
  KEY `offers_ibfk_2` (`stockid`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offers`
--

LOCK TABLES `offers` WRITE;
/*!40000 ALTER TABLE `offers` DISABLE KEYS */;
/*!40000 ALTER TABLE `offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orderdeliverydifferenceslog`
--

DROP TABLE IF EXISTS `orderdeliverydifferenceslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orderdeliverydifferenceslog` (
  `orderno` int(11) NOT NULL DEFAULT '0',
  `invoiceno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitydiff` double NOT NULL DEFAULT '0',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branch` varchar(10) NOT NULL DEFAULT '',
  `can_or_bo` char(3) NOT NULL DEFAULT 'CAN',
  KEY `StockID` (`stockid`),
  KEY `DebtorNo` (`debtorno`,`branch`),
  KEY `Can_or_BO` (`can_or_bo`),
  KEY `OrderNo` (`orderno`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_2` FOREIGN KEY (`debtorno`, `branch`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_3` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orderdeliverydifferenceslog`
--

LOCK TABLES `orderdeliverydifferenceslog` WRITE;
/*!40000 ALTER TABLE `orderdeliverydifferenceslog` DISABLE KEYS */;
/*!40000 ALTER TABLE `orderdeliverydifferenceslog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagesecurity`
--

DROP TABLE IF EXISTS `pagesecurity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagesecurity` (
  `script` varchar(78) NOT NULL DEFAULT '',
  `security` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`script`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagesecurity`
--

LOCK TABLES `pagesecurity` WRITE;
/*!40000 ALTER TABLE `pagesecurity` DISABLE KEYS */;
INSERT INTO `pagesecurity` VALUES ('AccountGroups.php',10),('AccountSections.php',10),('AddCustomerContacts.php',3),('AddCustomerNotes.php',3),('AddCustomerTypeNotes.php',3),('AgedDebtors.php',2),('AgedSuppliers.php',2),('Areas.php',3),('AuditTrail.php',15),('BankAccounts.php',10),('BankMatching.php',7),('BankReconciliation.php',7),('BOMExtendedQty.php',2),('BOMIndented.php',2),('BOMIndentedReverse.php',2),('BOMInquiry.php',2),('BOMListing.php',2),('BOMs.php',9),('COGSGLPostings.php',10),('CompanyPreferences.php',10),('ConfirmDispatchControlled_Invoice.php',11),('ConfirmDispatch_Invoice.php',2),('ContractBOM.php',6),('ContractCosting.php',6),('ContractOtherReqts.php',4),('Contracts.php',6),('CounterSales.php',1),('CreditItemsControlled.php',3),('CreditStatus.php',3),('Credit_Invoice.php',3),('Currencies.php',9),('CustEDISetup.php',11),('CustLoginSetup.php',15),('CustomerAllocations.php',3),('CustomerBranches.php',3),('CustomerInquiry.php',1),('CustomerReceipt.php',3),('Customers.php',3),('CustomerTransInquiry.php',2),('CustomerTypes.php',15),('CustWhereAlloc.php',2),('DailyBankTransactions.php',8),('DailySalesInquiry.php',2),('DebtorsAtPeriodEnd.php',2),('DeliveryDetails.php',1),('DiscountCategories.php',11),('DiscountMatrix.php',11),('EDIMessageFormat.php',10),('EDIProcessOrders.php',11),('EDISendInvoices.php',15),('EmailConfirmation.php',2),('EmailCustTrans.php',2),('ExchangeRateTrend.php',2),('Factors.php',5),('FixedAssetCategories.php',11),('FixedAssetDepreciation.php',10),('FixedAssetItems.php',11),('FixedAssetList.php',11),('FixedAssetLocations.php',11),('FixedAssetRegister.php',11),('FixedAssetTransfer.php',11),('FormDesigner.php',14),('FormMaker.php',1),('FreightCosts.php',11),('FTP_RadioBeacon.php',2),('geocode.php',3),('GeocodeSetup.php',3),('geocode_genxml_customers.php',3),('geocode_genxml_suppliers.php',3),('geo_displaymap_customers.php',3),('geo_displaymap_suppliers.php',3),('GetStockImage.php',1),('GLAccountCSV.php',8),('GLAccountInquiry.php',8),('GLAccountReport.php',8),('GLAccounts.php',10),('GLBalanceSheet.php',8),('GLBudgets.php',10),('GLCodesInquiry.php',8),('GLJournal.php',10),('GLProfit_Loss.php',8),('GLTagProfit_Loss.php',8),('GLTags.php',10),('GLTransInquiry.php',8),('GLTrialBalance.php',8),('GLTrialBalance_csv.php',8),('GoodsReceived.php',11),('GoodsReceivedControlled.php',11),('index.php',1),('InventoryPlanning.php',2),('InventoryPlanningPrefSupplier.php',2),('InventoryQuantities.php',2),('InventoryValuation.php',2),('Labels.php',15),('Locations.php',11),('Logout.php',1),('MailInventoryValuation.php',1),('ManualContents.php',10),('MenuAccess.php',15),('MRP.php',9),('MRPCalendar.php',9),('MRPCreateDemands.php',9),('MRPDemands.php',9),('MRPDemandTypes.php',9),('MRPPlannedPurchaseOrders.php',2),('MRPPlannedWorkOrders.php',2),('MRPReport.php',2),('MRPReschedules.php',2),('MRPShortages.php',2),('OffersReceived.php',4),('OrderDetails.php',2),('OutstandingGRNs.php',2),('PageSecurity.php',15),('PaymentAllocations.php',5),('PaymentMethods.php',15),('Payments.php',5),('PaymentTerms.php',10),('PcAssignCashToTab.php',6),('PcAuthorizeExpenses.php',6),('PcClaimExpensesFromTab.php',6),('PcExpenses.php',15),('PcExpensesTypeTab.php',15),('PcReportTab.php',6),('PcTabs.php',15),('PcTypeTabs.php',15),('PDFBankingSummary.php',3),('PDFChequeListing.php',3),('PDFCustomerList.php',2),('PDFCustTransListing.php',3),('PDFDeliveryDifferences.php',3),('PDFDIFOT.php',3),('PDFGrn.php',2),('PDFLowGP.php',2),('PDFOrdersInvoiced.php',3),('PDFOrderStatus.php',3),('PDFPickingList.php',2),('PDFPriceList.php',2),('PDFPrintLabel.php',10),('PDFQuotation.php',2),('PDFReceipt.php',2),('PDFRemittanceAdvice.php',2),('PDFStockCheckComparison.php',2),('PDFStockLocTransfer.php',1),('PDFStockNegatives.php',1),('PDFStockTransfer.php',2),('PDFStockTransListing.php',3),('PDFSuppTransListing.php',3),('PDFTopItems.php',2),('PeriodsInquiry.php',2),('POReport.php',2),('PO_AuthorisationLevels.php',15),('PO_AuthoriseMyOrders.php',4),('PO_Header.php',4),('PO_Items.php',4),('PO_OrderDetails.php',2),('PO_PDFPurchOrder.php',2),('PO_SelectOSPurchOrder.php',2),('PO_SelectPurchOrder.php',2),('Prices.php',9),('PricesBasedOnMarkUp.php',11),('PricesByCost.php',11),('Prices_Customer.php',11),('PrintCheque.php',5),('PrintCustOrder.php',2),('PrintCustOrder_generic.php',2),('PrintCustStatements.php',2),('PrintCustTrans.php',1),('PrintCustTransPortrait.php',1),('PrintSalesOrder_generic.php',2),('PurchData.php',4),('RecurringSalesOrders.php',1),('ReorderLevel.php',2),('ReorderLevelLocation.php',2),('ReportBug.php',15),('ReportCreator.php',13),('ReportletContainer.php',1),('ReportMaker.php',1),('ReverseGRN.php',11),('SalesAnalReptCols.php',2),('SalesAnalRepts.php',2),('SalesAnalysis_UserDefined.php',2),('SalesCategories.php',11),('SalesGLPostings.php',10),('SalesGraph.php',6),('SalesInquiry.php',2),('SalesPeople.php',3),('SalesTypes.php',15),('SelectAsset.php',2),('SelectCompletedOrder.php',1),('SelectContract.php',6),('SelectCreditItems.php',3),('SelectCustomer.php',2),('SelectGLAccount.php',8),('SelectOrderItems.php',1),('SelectProduct.php',2),('SelectRecurringSalesOrder.php',2),('SelectSalesOrder.php',2),('SelectSupplier.php',2),('SelectWorkOrder.php',2),('ShipmentCosting.php',11),('Shipments.php',11),('Shippers.php',15),('ShiptsList.php',2),('Shipt_Select.php',11),('SMTPServer.php',15),('SpecialOrder.php',4),('StockAdjustments.php',11),('StockAdjustmentsControlled.php',11),('StockCategories.php',11),('StockCheck.php',2),('StockCostUpdate.php',9),('StockCounts.php',2),('StockDispatch.php',2),('StockLocMovements.php',2),('StockLocStatus.php',2),('StockLocTransfer.php',11),('StockLocTransferReceive.php',11),('StockMovements.php',2),('StockQties_csv.php',5),('StockQuantityByDate.php',2),('StockReorderLevel.php',4),('Stocks.php',11),('StockSerialItemResearch.php',3),('StockSerialItems.php',2),('StockStatus.php',2),('StockTransferControlled.php',11),('StockTransfers.php',11),('StockUsage.php',2),('StockUsageGraph.php',2),('SuppContractChgs.php',5),('SuppCreditGRNs.php',5),('SuppFixedAssetChgs.php',5),('SuppInvGRNs.php',5),('SupplierAllocations.php',5),('SupplierBalsAtPeriodEnd.php',2),('SupplierContacts.php',5),('SupplierCredit.php',5),('SupplierInquiry.php',2),('SupplierInvoice.php',5),('Suppliers.php',5),('SupplierTenders.php',9),('SupplierTransInquiry.php',2),('SupplierTypes.php',4),('SuppLoginSetup.php',15),('SuppPaymentRun.php',5),('SuppPriceList.php',2),('SuppShiptChgs.php',5),('SuppTransGLAnalysis.php',5),('SystemCheck.php',10),('SystemParameters.php',15),('Tax.php',2),('TaxAuthorities.php',15),('TaxAuthorityRates.php',11),('TaxCategories.php',15),('TaxGroups.php',15),('TaxProvinces.php',15),('TopItems.php',2),('UnitsOfMeasure.php',15),('UpgradeDatabase.php',15),('UserSettings.php',1),('WhereUsedInquiry.php',2),('WorkCentres.php',9),('WorkOrderCosting.php',11),('WorkOrderEntry.php',10),('WorkOrderIssue.php',11),('WorkOrderReceive.php',11),('WorkOrderStatus.php',11),('WOSerialNos.php',10),('WWW_Access.php',15),('WWW_Users.php',15),('Z_BottomUpCosts.php',15),('Z_ChangeBranchCode.php',15),('Z_ChangeCustomerCode.php',15),('Z_ChangeStockCategory.php',15),('Z_ChangeStockCode.php',15),('Z_CheckAllocationsFrom.php',15),('Z_CheckAllocs.php',2),('Z_CheckDebtorsControl.php',15),('Z_CheckGLTransBalance.php',15),('Z_CopyBOM.php',9),('Z_CreateChartDetails.php',9),('Z_CreateCompany.php',15),('Z_CreateCompanyTemplateFile.php',15),('Z_CurrencyDebtorsBalances.php',15),('Z_CurrencySuppliersBalances.php',15),('Z_DataExport.php',15),('Z_DeleteCreditNote.php',15),('Z_DeleteInvoice.php',15),('Z_DeleteSalesTransActions.php',15),('Z_DescribeTable.php',11),('Z_ImportChartOfAccounts.php',11),('Z_ImportFixedAssets.php',15),('Z_ImportGLAccountGroups.php',11),('Z_ImportGLAccountSections.php',11),('Z_ImportPartCodes.php',11),('Z_ImportStocks.php',15),('Z_index.php',15),('Z_MakeNewCompany.php',15),('Z_MakeStockLocns.php',15),('Z_poAddLanguage.php',15),('Z_poAdmin.php',15),('Z_poEditLangHeader.php',15),('Z_poEditLangModule.php',15),('Z_poEditLangRemaining.php',15),('Z_poRebuildDefault.php',15),('Z_PriceChanges.php',15),('Z_ReApplyCostToSA.php',15),('Z_RePostGLFromPeriod.php',15),('Z_ReverseSuppPaymentRun.php',15),('Z_SalesIntegrityCheck.php',15),('Z_UpdateChartDetailsBFwd.php',15),('Z_Upgrade3.10.php',15),('Z_Upgrade_3.01-3.02.php',15),('Z_Upgrade_3.04-3.05.php',15),('Z_Upgrade_3.05-3.06.php',15),('Z_Upgrade_3.07-3.08.php',15),('Z_Upgrade_3.08-3.09.php',15),('Z_Upgrade_3.09-3.10.php',15),('Z_Upgrade_3.10-3.11.php',15),('Z_Upgrade_3.11-4.00.php',15),('Z_UploadForm.php',15),('Z_UploadResult.php',15);
/*!40000 ALTER TABLE `pagesecurity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paymentmethods`
--

DROP TABLE IF EXISTS `paymentmethods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `paymentname` varchar(15) NOT NULL DEFAULT '',
  `paymenttype` int(11) NOT NULL DEFAULT '1',
  `receipttype` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`paymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymentmethods`
--

LOCK TABLES `paymentmethods` WRITE;
/*!40000 ALTER TABLE `paymentmethods` DISABLE KEYS */;
INSERT INTO `paymentmethods` VALUES (1,'Cheque',1,1),(2,'Cash',1,1),(3,'Direct Credit',1,1);
/*!40000 ALTER TABLE `paymentmethods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paymentterms`
--

DROP TABLE IF EXISTS `paymentterms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymentterms` (
  `termsindicator` char(2) NOT NULL DEFAULT '',
  `terms` char(40) NOT NULL DEFAULT '',
  `daysbeforedue` smallint(6) NOT NULL DEFAULT '0',
  `dayinfollowingmonth` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`termsindicator`),
  KEY `DaysBeforeDue` (`daysbeforedue`),
  KEY `DayInFollowingMonth` (`dayinfollowingmonth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymentterms`
--

LOCK TABLES `paymentterms` WRITE;
/*!40000 ALTER TABLE `paymentterms` DISABLE KEYS */;
INSERT INTO `paymentterms` VALUES ('20','Due 20th Of the Following Month',0,22),('30','Due By End Of The Following Month',0,30),('7','Payment due within 7 days',7,0),('CA','Cash Only',2,0);
/*!40000 ALTER TABLE `paymentterms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pcashdetails`
--

DROP TABLE IF EXISTS `pcashdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcashdetails` (
  `counterindex` int(20) NOT NULL AUTO_INCREMENT,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `notes` text NOT NULL,
  `receipt` text COMMENT 'filename or path to scanned receipt or code of receipt to find physical receipt if tax guys or auditors show up',
  PRIMARY KEY (`counterindex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pcashdetails`
--

LOCK TABLES `pcashdetails` WRITE;
/*!40000 ALTER TABLE `pcashdetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `pcashdetails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pcexpenses`
--

DROP TABLE IF EXISTS `pcexpenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` int(11) NOT NULL COMMENT 'GL related account',
  PRIMARY KEY (`codeexpense`),
  KEY `glaccount` (`glaccount`),
  CONSTRAINT `pcexpenses_ibfk_1` FOREIGN KEY (`glaccount`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pcexpenses`
--

LOCK TABLES `pcexpenses` WRITE;
/*!40000 ALTER TABLE `pcexpenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `pcexpenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pctabexpenses`
--

DROP TABLE IF EXISTS `pctabexpenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  KEY `typetabcode` (`typetabcode`),
  KEY `codeexpense` (`codeexpense`),
  CONSTRAINT `pctabexpenses_ibfk_2` FOREIGN KEY (`codeexpense`) REFERENCES `pcexpenses` (`codeexpense`),
  CONSTRAINT `pctabexpenses_ibfk_1` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pctabexpenses`
--

LOCK TABLES `pctabexpenses` WRITE;
/*!40000 ALTER TABLE `pctabexpenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `pctabexpenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pctabs`
--

DROP TABLE IF EXISTS `pctabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
  `typetabcode` varchar(20) NOT NULL,
  `currency` char(3) NOT NULL,
  `tablimit` double NOT NULL,
  `authorizer` varchar(20) NOT NULL COMMENT 'code of user from www_users',
  `glaccountassignment` int(11) NOT NULL COMMENT 'gl account where the money comes from',
  `glaccountpcash` int(11) NOT NULL,
  PRIMARY KEY (`tabcode`),
  KEY `usercode` (`usercode`),
  KEY `typetabcode` (`typetabcode`),
  KEY `currency` (`currency`),
  KEY `authorizer` (`authorizer`),
  KEY `glaccountassignment` (`glaccountassignment`),
  CONSTRAINT `pctabs_ibfk_5` FOREIGN KEY (`glaccountassignment`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `pctabs_ibfk_1` FOREIGN KEY (`usercode`) REFERENCES `www_users` (`userid`),
  CONSTRAINT `pctabs_ibfk_2` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  CONSTRAINT `pctabs_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `pctabs_ibfk_4` FOREIGN KEY (`authorizer`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pctabs`
--

LOCK TABLES `pctabs` WRITE;
/*!40000 ALTER TABLE `pctabs` DISABLE KEYS */;
/*!40000 ALTER TABLE `pctabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pctypetabs`
--

DROP TABLE IF EXISTS `pctypetabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO',
  PRIMARY KEY (`typetabcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pctypetabs`
--

LOCK TABLES `pctypetabs` WRITE;
/*!40000 ALTER TABLE `pctypetabs` DISABLE KEYS */;
/*!40000 ALTER TABLE `pctypetabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periods`
--

DROP TABLE IF EXISTS `periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periods` (
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `lastdate_in_period` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`periodno`),
  KEY `LastDate_in_Period` (`lastdate_in_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periods`
--

LOCK TABLES `periods` WRITE;
/*!40000 ALTER TABLE `periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pickinglistdetails`
--

DROP TABLE IF EXISTS `pickinglistdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT '0',
  `pickinglistlineno` int(11) NOT NULL DEFAULT '0',
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `qtyexpected` double NOT NULL DEFAULT '0',
  `qtypicked` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`pickinglistno`,`pickinglistlineno`),
  CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pickinglistdetails`
--

LOCK TABLES `pickinglistdetails` WRITE;
/*!40000 ALTER TABLE `pickinglistdetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `pickinglistdetails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pickinglists`
--

DROP TABLE IF EXISTS `pickinglists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `pickinglistdate` date NOT NULL DEFAULT '0000-00-00',
  `dateprinted` date NOT NULL DEFAULT '0000-00-00',
  `deliverynotedate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`pickinglistno`),
  KEY `pickinglists_ibfk_1` (`orderno`),
  CONSTRAINT `pickinglists_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pickinglists`
--

LOCK TABLES `pickinglists` WRITE;
/*!40000 ALTER TABLE `pickinglists` DISABLE KEYS */;
/*!40000 ALTER TABLE `pickinglists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prices`
--

DROP TABLE IF EXISTS `prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prices` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `enddate` date NOT NULL DEFAULT '2030-01-01',
  PRIMARY KEY (`stockid`,`typeabbrev`,`currabrev`,`debtorno`,`branchcode`,`startdate`,`enddate`),
  KEY `CurrAbrev` (`currabrev`),
  KEY `DebtorNo` (`debtorno`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`currabrev`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `prices_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`typeabbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prices`
--

LOCK TABLES `prices` WRITE;
/*!40000 ALTER TABLE `prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchdata`
--

DROP TABLE IF EXISTS `purchdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchdata` (
  `supplierno` char(10) NOT NULL DEFAULT '',
  `stockid` char(20) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `suppliersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT '1',
  `supplierdescription` char(50) NOT NULL DEFAULT '',
  `leadtime` smallint(6) NOT NULL DEFAULT '1',
  `preferred` tinyint(4) NOT NULL DEFAULT '0',
  `effectivefrom` date NOT NULL,
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `minorderqty` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`supplierno`,`stockid`,`effectivefrom`),
  KEY `StockID` (`stockid`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Preferred` (`preferred`),
  CONSTRAINT `purchdata_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `purchdata_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchdata`
--

LOCK TABLES `purchdata` WRITE;
/*!40000 ALTER TABLE `purchdata` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchdata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchorderauth`
--

DROP TABLE IF EXISTS `purchorderauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchorderauth` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `cancreate` smallint(2) NOT NULL DEFAULT '0',
  `authlevel` int(11) NOT NULL DEFAULT '0',
  `offhold` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`,`currabrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchorderauth`
--

LOCK TABLES `purchorderauth` WRITE;
/*!40000 ALTER TABLE `purchorderauth` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchorderauth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchorderdetails`
--

DROP TABLE IF EXISTS `purchorderdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchorderdetails` (
  `podetailitem` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL DEFAULT '0',
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `itemdescription` varchar(100) NOT NULL DEFAULT '',
  `glcode` int(11) NOT NULL DEFAULT '0',
  `qtyinvoiced` double NOT NULL DEFAULT '0',
  `unitprice` double NOT NULL DEFAULT '0',
  `actprice` double NOT NULL DEFAULT '0',
  `stdcostunit` double NOT NULL DEFAULT '0',
  `quantityord` double NOT NULL DEFAULT '0',
  `quantityrecd` double NOT NULL DEFAULT '0',
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT '0',
  `itemno` varchar(50) NOT NULL DEFAULT '',
  `uom` varchar(50) NOT NULL DEFAULT '',
  `conversionfactor` int(11) NOT NULL DEFAULT '0',
  `subtotal_amount` varchar(50) NOT NULL DEFAULT '',
  `package` varchar(100) NOT NULL DEFAULT '',
  `pcunit` varchar(50) NOT NULL DEFAULT '',
  `nw` varchar(50) NOT NULL DEFAULT '',
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `gw` varchar(50) NOT NULL DEFAULT '',
  `cuft` varchar(50) NOT NULL DEFAULT '',
  `total_quantity` varchar(50) NOT NULL DEFAULT '',
  `total_amount` varchar(50) NOT NULL DEFAULT '',
  `assetid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`podetailitem`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `GLCode` (`glcode`),
  KEY `ItemCode` (`itemcode`),
  KEY `JobRef` (`jobref`),
  KEY `OrderNo` (`orderno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `Completed` (`completed`),
  CONSTRAINT `purchorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `purchorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchorderdetails`
--

LOCK TABLES `purchorderdetails` WRITE;
/*!40000 ALTER TABLE `purchorderdetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchorderdetails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchorders`
--

DROP TABLE IF EXISTS `purchorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchorders` (
  `orderno` int(11) NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `comments` longblob,
  `orddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rate` double NOT NULL DEFAULT '1',
  `dateprinted` datetime DEFAULT NULL,
  `allowprint` tinyint(4) NOT NULL DEFAULT '1',
  `initiator` varchar(10) DEFAULT NULL,
  `requisitionno` varchar(15) DEFAULT NULL,
  `intostocklocation` varchar(5) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) NOT NULL DEFAULT '',
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `tel` varchar(15) NOT NULL DEFAULT '""',
  `suppdeladdress1` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress2` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress3` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress4` varchar(20) NOT NULL DEFAULT '',
  `suppdeladdress5` varchar(15) NOT NULL DEFAULT '',
  `suppdeladdress6` varchar(30) NOT NULL DEFAULT '',
  `supptel` varchar(30) NOT NULL DEFAULT '""',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `version` decimal(3,2) NOT NULL DEFAULT '1.00',
  `revised` date NOT NULL DEFAULT '0000-00-00',
  `realorderno` varchar(16) NOT NULL DEFAULT '',
  `deliveryby` varchar(100) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `status` varchar(12) NOT NULL DEFAULT '',
  `stat_comment` mediumtext NOT NULL,
  `paymentterms` char(2) NOT NULL DEFAULT '""',
  `port` varchar(40) NOT NULL DEFAULT '""',
  PRIMARY KEY (`orderno`),
  KEY `OrdDate` (`orddate`),
  KEY `SupplierNo` (`supplierno`),
  KEY `IntoStockLocation` (`intostocklocation`),
  KEY `AllowPrintPO` (`allowprint`),
  CONSTRAINT `purchorders_ibfk_1` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `purchorders_ibfk_2` FOREIGN KEY (`intostocklocation`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchorders`
--

LOCK TABLES `purchorders` WRITE;
/*!40000 ALTER TABLE `purchorders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchorders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurringsalesorders`
--

DROP TABLE IF EXISTS `recurringsalesorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurringsalesorders` (
  `recurrorderno` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob,
  `orddate` date NOT NULL DEFAULT '0000-00-00',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(25) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `freightcost` double NOT NULL DEFAULT '0',
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `lastrecurrence` date NOT NULL DEFAULT '0000-00-00',
  `stopdate` date NOT NULL DEFAULT '0000-00-00',
  `frequency` tinyint(4) NOT NULL DEFAULT '1',
  `autoinvoice` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`recurrorderno`),
  KEY `debtorno` (`debtorno`),
  KEY `orddate` (`orddate`),
  KEY `ordertype` (`ordertype`),
  KEY `locationindex` (`fromstkloc`),
  KEY `branchcode` (`branchcode`,`debtorno`),
  CONSTRAINT `recurringsalesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurringsalesorders`
--

LOCK TABLES `recurringsalesorders` WRITE;
/*!40000 ALTER TABLE `recurringsalesorders` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurringsalesorders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurrsalesorderdetails`
--

DROP TABLE IF EXISTS `recurrsalesorderdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurrsalesorderdetails` (
  `recurrorderno` int(11) NOT NULL DEFAULT '0',
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `unitprice` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `narrative` mediumtext NOT NULL,
  KEY `orderno` (`recurrorderno`),
  KEY `stkcode` (`stkcode`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_1` FOREIGN KEY (`recurrorderno`) REFERENCES `recurringsalesorders` (`recurrorderno`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurrsalesorderdetails`
--

LOCK TABLES `recurrsalesorderdetails` WRITE;
/*!40000 ALTER TABLE `recurrsalesorderdetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurrsalesorderdetails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportcolumns`
--

DROP TABLE IF EXISTS `reportcolumns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportcolumns` (
  `reportid` smallint(6) NOT NULL DEFAULT '0',
  `colno` smallint(6) NOT NULL DEFAULT '0',
  `heading1` varchar(15) NOT NULL DEFAULT '',
  `heading2` varchar(15) DEFAULT NULL,
  `calculation` tinyint(1) NOT NULL DEFAULT '0',
  `periodfrom` smallint(6) DEFAULT NULL,
  `periodto` smallint(6) DEFAULT NULL,
  `datatype` varchar(15) DEFAULT NULL,
  `colnumerator` tinyint(4) DEFAULT NULL,
  `coldenominator` tinyint(4) DEFAULT NULL,
  `calcoperator` char(1) DEFAULT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT '0',
  `valformat` char(1) NOT NULL DEFAULT 'N',
  `constant` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`reportid`,`colno`),
  CONSTRAINT `reportcolumns_ibfk_1` FOREIGN KEY (`reportid`) REFERENCES `reportheaders` (`reportid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportcolumns`
--

LOCK TABLES `reportcolumns` WRITE;
/*!40000 ALTER TABLE `reportcolumns` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportcolumns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportfields`
--

DROP TABLE IF EXISTS `reportfields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportfields` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `reportid` int(5) NOT NULL DEFAULT '0',
  `entrytype` varchar(15) NOT NULL DEFAULT '',
  `seqnum` int(3) NOT NULL DEFAULT '0',
  `fieldname` varchar(80) NOT NULL DEFAULT '''',
  `displaydesc` varchar(25) NOT NULL DEFAULT '',
  `visible` enum('1','0') NOT NULL DEFAULT '1',
  `columnbreak` enum('1','0') NOT NULL DEFAULT '1',
  `params` mediumtext,
  PRIMARY KEY (`id`),
  KEY `reportid` (`reportid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportfields`
--

LOCK TABLES `reportfields` WRITE;
/*!40000 ALTER TABLE `reportfields` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportfields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportheaders`
--

DROP TABLE IF EXISTS `reportheaders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportheaders` (
  `reportid` smallint(6) NOT NULL AUTO_INCREMENT,
  `reportheading` varchar(80) NOT NULL DEFAULT '',
  `groupbydata1` varchar(15) NOT NULL DEFAULT '',
  `newpageafter1` tinyint(1) NOT NULL DEFAULT '0',
  `lower1` varchar(10) NOT NULL DEFAULT '',
  `upper1` varchar(10) NOT NULL DEFAULT '',
  `groupbydata2` varchar(15) DEFAULT NULL,
  `newpageafter2` tinyint(1) NOT NULL DEFAULT '0',
  `lower2` varchar(10) DEFAULT NULL,
  `upper2` varchar(10) DEFAULT NULL,
  `groupbydata3` varchar(15) DEFAULT NULL,
  `newpageafter3` tinyint(1) NOT NULL DEFAULT '0',
  `lower3` varchar(10) DEFAULT NULL,
  `upper3` varchar(10) DEFAULT NULL,
  `groupbydata4` varchar(15) NOT NULL DEFAULT '',
  `newpageafter4` tinyint(1) NOT NULL DEFAULT '0',
  `upper4` varchar(10) NOT NULL DEFAULT '',
  `lower4` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`reportid`),
  KEY `ReportHeading` (`reportheading`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportheaders`
--

LOCK TABLES `reportheaders` WRITE;
/*!40000 ALTER TABLE `reportheaders` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportheaders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportlets`
--

DROP TABLE IF EXISTS `reportlets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportlets` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `id` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(50) NOT NULL DEFAULT '',
  `refresh` int(11) NOT NULL DEFAULT '600',
  PRIMARY KEY (`userid`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportlets`
--

LOCK TABLES `reportlets` WRITE;
/*!40000 ALTER TABLE `reportlets` DISABLE KEYS */;
INSERT INTO `reportlets` VALUES ('admin','SalesGraph','Graph of last 12 months sales activity',60),('admin','TopSalesQty','Top selling items by quantity',60),('admin','TopSalesVal','Top selling items by value',60);
/*!40000 ALTER TABLE `reportlets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportlinks`
--

DROP TABLE IF EXISTS `reportlinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportlinks` (
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) NOT NULL DEFAULT '',
  `equation` varchar(75) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportlinks`
--

LOCK TABLES `reportlinks` WRITE;
/*!40000 ALTER TABLE `reportlinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportlinks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `reportname` varchar(30) NOT NULL DEFAULT '',
  `reporttype` char(3) NOT NULL DEFAULT 'rpt',
  `groupname` varchar(9) NOT NULL DEFAULT 'misc',
  `defaultreport` enum('1','0') NOT NULL DEFAULT '0',
  `papersize` varchar(15) NOT NULL DEFAULT 'A4,210,297',
  `paperorientation` enum('P','L') NOT NULL DEFAULT 'P',
  `margintop` int(3) NOT NULL DEFAULT '10',
  `marginbottom` int(3) NOT NULL DEFAULT '10',
  `marginleft` int(3) NOT NULL DEFAULT '10',
  `marginright` int(3) NOT NULL DEFAULT '10',
  `coynamefont` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `coynamefontsize` int(3) NOT NULL DEFAULT '12',
  `coynamefontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `coynamealign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `coynameshow` enum('1','0') NOT NULL DEFAULT '1',
  `title1desc` varchar(50) NOT NULL DEFAULT '%reportname%',
  `title1font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title1fontsize` int(3) NOT NULL DEFAULT '10',
  `title1fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title1fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title1show` enum('1','0') NOT NULL DEFAULT '1',
  `title2desc` varchar(50) NOT NULL DEFAULT 'Report Generated %date%',
  `title2font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title2fontsize` int(3) NOT NULL DEFAULT '10',
  `title2fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title2fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title2show` enum('1','0') NOT NULL DEFAULT '1',
  `filterfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `filterfontsize` int(3) NOT NULL DEFAULT '8',
  `filterfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `filterfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `datafont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `datafontsize` int(3) NOT NULL DEFAULT '10',
  `datafontcolor` varchar(10) NOT NULL DEFAULT 'black',
  `datafontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `totalsfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `totalsfontsize` int(3) NOT NULL DEFAULT '10',
  `totalsfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `totalsfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `col1width` int(3) NOT NULL DEFAULT '25',
  `col2width` int(3) NOT NULL DEFAULT '25',
  `col3width` int(3) NOT NULL DEFAULT '25',
  `col4width` int(3) NOT NULL DEFAULT '25',
  `col5width` int(3) NOT NULL DEFAULT '25',
  `col6width` int(3) NOT NULL DEFAULT '25',
  `col7width` int(3) NOT NULL DEFAULT '25',
  `col8width` int(3) NOT NULL DEFAULT '25',
  `col9width` int(3) NOT NULL DEFAULT '25',
  `col10width` int(3) NOT NULL DEFAULT '25',
  `col11width` int(3) NOT NULL DEFAULT '25',
  `col12width` int(3) NOT NULL DEFAULT '25',
  `col13width` int(3) NOT NULL DEFAULT '25',
  `col14width` int(3) NOT NULL DEFAULT '25',
  `col15width` int(3) NOT NULL DEFAULT '25',
  `col16width` int(3) NOT NULL DEFAULT '25',
  `col17width` int(3) NOT NULL DEFAULT '25',
  `col18width` int(3) NOT NULL DEFAULT '25',
  `col19width` int(3) NOT NULL DEFAULT '25',
  `col20width` int(3) NOT NULL DEFAULT '25',
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) DEFAULT NULL,
  `table2criteria` varchar(75) DEFAULT NULL,
  `table3` varchar(25) DEFAULT NULL,
  `table3criteria` varchar(75) DEFAULT NULL,
  `table4` varchar(25) DEFAULT NULL,
  `table4criteria` varchar(75) DEFAULT NULL,
  `table5` varchar(25) DEFAULT NULL,
  `table5criteria` varchar(75) DEFAULT NULL,
  `table6` varchar(25) DEFAULT NULL,
  `table6criteria` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`reportname`,`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salesanalysis`
--

DROP TABLE IF EXISTS `salesanalysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesanalysis` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `amt` double NOT NULL DEFAULT '0',
  `cost` double NOT NULL DEFAULT '0',
  `cust` varchar(10) NOT NULL DEFAULT '',
  `custbranch` varchar(10) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT '0',
  `disc` double NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `area` varchar(3) NOT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT '0',
  `salesperson` char(3) NOT NULL DEFAULT '',
  `stkcategory` varchar(6) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `CustBranch` (`custbranch`),
  KEY `Cust` (`cust`),
  KEY `PeriodNo` (`periodno`),
  KEY `StkCategory` (`stkcategory`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  KEY `Area` (`area`),
  KEY `BudgetOrActual` (`budgetoractual`),
  KEY `Salesperson` (`salesperson`),
  CONSTRAINT `salesanalysis_ibfk_1` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salesanalysis`
--

LOCK TABLES `salesanalysis` WRITE;
/*!40000 ALTER TABLE `salesanalysis` DISABLE KEYS */;
/*!40000 ALTER TABLE `salesanalysis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salescat`
--

DROP TABLE IF EXISTS `salescat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescat` (
  `salescatid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `parentcatid` tinyint(4) DEFAULT NULL,
  `salescatname` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salescat`
--

LOCK TABLES `salescat` WRITE;
/*!40000 ALTER TABLE `salescat` DISABLE KEYS */;
/*!40000 ALTER TABLE `salescat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salescatprod`
--

DROP TABLE IF EXISTS `salescatprod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescatprod` (
  `salescatid` tinyint(4) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`salescatid`,`stockid`),
  KEY `salescatid` (`salescatid`),
  KEY `stockid` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_2` FOREIGN KEY (`salescatid`) REFERENCES `salescat` (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salescatprod`
--

LOCK TABLES `salescatprod` WRITE;
/*!40000 ALTER TABLE `salescatprod` DISABLE KEYS */;
/*!40000 ALTER TABLE `salescatprod` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salesglpostings`
--

DROP TABLE IF EXISTS `salesglpostings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesglpostings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area` varchar(3) NOT NULL,
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `discountglcode` int(11) NOT NULL DEFAULT '0',
  `salesglcode` int(11) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salesglpostings`
--

LOCK TABLES `salesglpostings` WRITE;
/*!40000 ALTER TABLE `salesglpostings` DISABLE KEYS */;
INSERT INTO `salesglpostings` VALUES (1,'AN','ANY',4900,4100,'AN'),(2,'AN','AIRCON',5000,4800,'DE');
/*!40000 ALTER TABLE `salesglpostings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salesman`
--

DROP TABLE IF EXISTS `salesman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesman` (
  `salesmancode` char(3) NOT NULL DEFAULT '',
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `commissionrate1` double NOT NULL DEFAULT '0',
  `breakpoint` decimal(10,0) NOT NULL DEFAULT '0',
  `commissionrate2` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`salesmancode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salesman`
--

LOCK TABLES `salesman` WRITE;
/*!40000 ALTER TABLE `salesman` DISABLE KEYS */;
/*!40000 ALTER TABLE `salesman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salesorderdetails`
--

DROP TABLE IF EXISTS `salesorderdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorderdetails` (
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `qtyinvoiced` double NOT NULL DEFAULT '0',
  `unitprice` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `estimate` tinyint(4) NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `actualdispatchdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `narrative` mediumtext,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `commissionrate` double NOT NULL DEFAULT '0',
  `commissionearned` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`orderlineno`,`orderno`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`),
  CONSTRAINT `salesorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`),
  CONSTRAINT `salesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salesorderdetails`
--

LOCK TABLES `salesorderdetails` WRITE;
/*!40000 ALTER TABLE `salesorderdetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `salesorderdetails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salesorders`
--

DROP TABLE IF EXISTS `salesorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorders` (
  `orderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob,
  `orddate` date NOT NULL DEFAULT '0000-00-00',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT '1',
  `freightcost` double NOT NULL DEFAULT '0',
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `quotedate` date NOT NULL DEFAULT '0000-00-00',
  `confirmeddate` date NOT NULL DEFAULT '0000-00-00',
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT '0',
  `datepackingslipprinted` date NOT NULL DEFAULT '0000-00-00',
  `quotation` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`orderno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `OrdDate` (`orddate`),
  KEY `OrderType` (`ordertype`),
  KEY `LocationIndex` (`fromstkloc`),
  KEY `BranchCode` (`branchcode`,`debtorno`),
  KEY `ShipVia` (`shipvia`),
  KEY `quotation` (`quotation`),
  CONSTRAINT `salesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`),
  CONSTRAINT `salesorders_ibfk_2` FOREIGN KEY (`shipvia`) REFERENCES `shippers` (`shipper_id`),
  CONSTRAINT `salesorders_ibfk_3` FOREIGN KEY (`fromstkloc`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salesorders`
--

LOCK TABLES `salesorders` WRITE;
/*!40000 ALTER TABLE `salesorders` DISABLE KEYS */;
/*!40000 ALTER TABLE `salesorders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salestypes`
--

DROP TABLE IF EXISTS `salestypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salestypes` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `sales_type` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`typeabbrev`),
  KEY `Sales_Type` (`sales_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salestypes`
--

LOCK TABLES `salestypes` WRITE;
/*!40000 ALTER TABLE `salestypes` DISABLE KEYS */;
/*!40000 ALTER TABLE `salestypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scripts`
--

DROP TABLE IF EXISTS `scripts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scripts` (
  `pageid` smallint(4) NOT NULL AUTO_INCREMENT,
  `filename` varchar(50) NOT NULL DEFAULT '',
  `pagedescription` mediumtext NOT NULL,
  PRIMARY KEY (`pageid`),
  KEY `FileName` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Index of all scripts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scripts`
--

LOCK TABLES `scripts` WRITE;
/*!40000 ALTER TABLE `scripts` DISABLE KEYS */;
/*!40000 ALTER TABLE `scripts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `securitygroups`
--

DROP TABLE IF EXISTS `securitygroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securitygroups` (
  `secroleid` int(11) NOT NULL DEFAULT '0',
  `tokenid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`secroleid`,`tokenid`),
  KEY `secroleid` (`secroleid`),
  KEY `tokenid` (`tokenid`),
  CONSTRAINT `securitygroups_secroleid_fk` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `securitygroups_tokenid_fk` FOREIGN KEY (`tokenid`) REFERENCES `securitytokens` (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `securitygroups`
--

LOCK TABLES `securitygroups` WRITE;
/*!40000 ALTER TABLE `securitygroups` DISABLE KEYS */;
INSERT INTO `securitygroups` VALUES (1,1),(1,2),(2,1),(2,2),(2,11),(3,1),(3,2),(3,3),(3,4),(3,5),(3,11),(4,1),(4,2),(4,5),(5,1),(5,2),(5,3),(5,11),(6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),(6,8),(6,9),(6,10),(6,11),(7,1),(8,1),(8,2),(8,3),(8,4),(8,5),(8,6),(8,7),(8,8),(8,9),(8,10),(8,11),(8,12),(8,13),(8,14),(8,15),(9,9);
/*!40000 ALTER TABLE `securitygroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `securityroles`
--

DROP TABLE IF EXISTS `securityroles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securityroles` (
  `secroleid` int(11) NOT NULL AUTO_INCREMENT,
  `secrolename` mediumtext NOT NULL,
  PRIMARY KEY (`secroleid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `securityroles`
--

LOCK TABLES `securityroles` WRITE;
/*!40000 ALTER TABLE `securityroles` DISABLE KEYS */;
INSERT INTO `securityroles` VALUES (1,'Inquiries/Order Entry'),(2,'Manufac/Stock Admin'),(3,'Purchasing Officer'),(4,'AP Clerk'),(5,'AR Clerk'),(6,'Accountant'),(7,'Customer Log On Only'),(8,'System Administrator'),(9,'Supplier Log On Only');
/*!40000 ALTER TABLE `securityroles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `securitytokens`
--

DROP TABLE IF EXISTS `securitytokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL DEFAULT '0',
  `tokenname` mediumtext NOT NULL,
  PRIMARY KEY (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `securitytokens`
--

LOCK TABLES `securitytokens` WRITE;
/*!40000 ALTER TABLE `securitytokens` DISABLE KEYS */;
INSERT INTO `securitytokens` VALUES (1,'Order Entry/Inquiries customer access only'),(2,'Basic Reports and Inquiries with selection options'),(3,'Credit notes and AR management'),(4,'Purchasing data/PO Entry/Reorder Levels'),(5,'Accounts Payable'),(6,'Prices security'),(7,'Bank Reconciliations'),(8,'General ledger reports/inquiries'),(9,'Supplier tendering'),(10,'General Ledger Maintenance, stock valuation & Configuration'),(11,'Inventory Management and Pricing'),(12,'Prices Security'),(13,'Unknown'),(14,'Unknown'),(15,'User Management and System Administration');
/*!40000 ALTER TABLE `securitytokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipmentcharges`
--

DROP TABLE IF EXISTS `shipmentcharges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipmentcharges` (
  `shiptchgid` int(11) NOT NULL AUTO_INCREMENT,
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `transtype` smallint(6) NOT NULL DEFAULT '0',
  `transno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `value` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`shiptchgid`),
  KEY `TransType` (`transtype`,`transno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `StockID` (`stockid`),
  KEY `TransType_2` (`transtype`),
  CONSTRAINT `shipmentcharges_ibfk_1` FOREIGN KEY (`shiptref`) REFERENCES `shipments` (`shiptref`),
  CONSTRAINT `shipmentcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipmentcharges`
--

LOCK TABLES `shipmentcharges` WRITE;
/*!40000 ALTER TABLE `shipmentcharges` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipmentcharges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipments` (
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `voyageref` varchar(20) NOT NULL DEFAULT '0',
  `vessel` varchar(50) NOT NULL DEFAULT '',
  `eta` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `accumvalue` double NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`shiptref`),
  KEY `ETA` (`eta`),
  KEY `SupplierID` (`supplierid`),
  KEY `ShipperRef` (`voyageref`),
  KEY `Vessel` (`vessel`),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipments`
--

LOCK TABLES `shipments` WRITE;
/*!40000 ALTER TABLE `shipments` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shippers`
--

DROP TABLE IF EXISTS `shippers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL AUTO_INCREMENT,
  `shippername` char(40) NOT NULL DEFAULT '',
  `mincharge` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipper_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shippers`
--

LOCK TABLES `shippers` WRITE;
/*!40000 ALTER TABLE `shippers` DISABLE KEYS */;
INSERT INTO `shippers` VALUES (1,'Default Shipper',0);
/*!40000 ALTER TABLE `shippers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockcategory`
--

DROP TABLE IF EXISTS `stockcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcategory` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `stocktype` char(1) NOT NULL DEFAULT 'F',
  `stockact` int(11) NOT NULL DEFAULT '0',
  `adjglact` int(11) NOT NULL DEFAULT '0',
  `purchpricevaract` int(11) NOT NULL DEFAULT '80000',
  `materialuseagevarac` int(11) NOT NULL DEFAULT '80000',
  `wipact` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`categoryid`),
  KEY `CategoryDescription` (`categorydescription`),
  KEY `StockType` (`stocktype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockcategory`
--

LOCK TABLES `stockcategory` WRITE;
/*!40000 ALTER TABLE `stockcategory` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockcategory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockcatproperties`
--

DROP TABLE IF EXISTS `stockcatproperties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcatproperties` (
  `stkcatpropid` int(11) NOT NULL AUTO_INCREMENT,
  `categoryid` char(6) NOT NULL,
  `label` mediumtext NOT NULL,
  `controltype` tinyint(4) NOT NULL DEFAULT '0',
  `defaultvalue` varchar(100) NOT NULL DEFAULT '''''',
  `maximumvalue` double NOT NULL DEFAULT '999999999',
  `minimumvalue` double NOT NULL DEFAULT '-999999999',
  `numericvalue` tinyint(4) NOT NULL DEFAULT '0',
  `reqatsalesorder` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkcatpropid`),
  KEY `categoryid` (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockcatproperties`
--

LOCK TABLES `stockcatproperties` WRITE;
/*!40000 ALTER TABLE `stockcatproperties` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockcatproperties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockcheckfreeze`
--

DROP TABLE IF EXISTS `stockcheckfreeze`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcheckfreeze` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qoh` double NOT NULL DEFAULT '0',
  `stockcheckdate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`stockid`,`loccode`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcheckfreeze_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockcheckfreeze_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockcheckfreeze`
--

LOCK TABLES `stockcheckfreeze` WRITE;
/*!40000 ALTER TABLE `stockcheckfreeze` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockcheckfreeze` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockcounts`
--

DROP TABLE IF EXISTS `stockcounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qtycounted` double NOT NULL DEFAULT '0',
  `reference` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcounts_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockcounts_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockcounts`
--

LOCK TABLES `stockcounts` WRITE;
/*!40000 ALTER TABLE `stockcounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockcounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockitemproperties`
--

DROP TABLE IF EXISTS `stockitemproperties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockitemproperties` (
  `stockid` varchar(20) NOT NULL,
  `stkcatpropid` int(11) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`stockid`,`stkcatpropid`),
  KEY `stockid` (`stockid`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockitemproperties`
--

LOCK TABLES `stockitemproperties` WRITE;
/*!40000 ALTER TABLE `stockitemproperties` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockitemproperties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockmaster`
--

DROP TABLE IF EXISTS `stockmaster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockmaster` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` mediumtext NOT NULL,
  `units` varchar(20) NOT NULL DEFAULT 'each',
  `mbflag` char(1) NOT NULL DEFAULT 'B',
  `lastcurcostdate` date NOT NULL DEFAULT '1800-01-01',
  `actualcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `lastcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `materialcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `labourcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `overheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `lowestlevel` smallint(6) NOT NULL DEFAULT '0',
  `discontinued` tinyint(4) NOT NULL DEFAULT '0',
  `controlled` tinyint(4) NOT NULL DEFAULT '0',
  `eoq` double NOT NULL DEFAULT '0',
  `volume` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `kgs` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `barcode` varchar(50) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `taxcatid` tinyint(4) NOT NULL DEFAULT '1',
  `serialised` tinyint(4) NOT NULL DEFAULT '0',
  `appendfile` varchar(40) NOT NULL DEFAULT 'none',
  `perishable` tinyint(1) NOT NULL DEFAULT '0',
  `decimalplaces` tinyint(4) NOT NULL DEFAULT '0',
  `nextserialno` bigint(20) NOT NULL DEFAULT '0',
  `pansize` double NOT NULL DEFAULT '0',
  `shrinkfactor` double NOT NULL DEFAULT '0',
  `netweight` decimal(20,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`stockid`),
  KEY `CategoryID` (`categoryid`),
  KEY `Description` (`description`),
  KEY `LastCurCostDate` (`lastcurcostdate`),
  KEY `MBflag` (`mbflag`),
  KEY `StockID` (`stockid`,`categoryid`),
  KEY `Controlled` (`controlled`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `taxcatid` (`taxcatid`),
  CONSTRAINT `stockmaster_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `stockmaster_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockmaster`
--

LOCK TABLES `stockmaster` WRITE;
/*!40000 ALTER TABLE `stockmaster` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockmaster` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockmoves`
--

DROP TABLE IF EXISTS `stockmoves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockmoves` (
  `stkmoveno` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `transno` int(11) NOT NULL DEFAULT '0',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '0000-00-00',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `prd` smallint(6) NOT NULL DEFAULT '0',
  `reference` varchar(40) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT '1',
  `discountpercent` double NOT NULL DEFAULT '0',
  `standardcost` double NOT NULL DEFAULT '0',
  `show_on_inv_crds` tinyint(4) NOT NULL DEFAULT '1',
  `newqoh` double NOT NULL DEFAULT '0',
  `hidemovt` tinyint(4) NOT NULL DEFAULT '0',
  `narrative` mediumtext,
  PRIMARY KEY (`stkmoveno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `LocCode` (`loccode`),
  KEY `Prd` (`prd`),
  KEY `StockID_2` (`stockid`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  KEY `Show_On_Inv_Crds` (`show_on_inv_crds`),
  KEY `Hide` (`hidemovt`),
  KEY `reference` (`reference`),
  CONSTRAINT `stockmoves_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockmoves_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `stockmoves_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockmoves_ibfk_4` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockmoves`
--

LOCK TABLES `stockmoves` WRITE;
/*!40000 ALTER TABLE `stockmoves` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockmoves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockmovestaxes`
--

DROP TABLE IF EXISTS `stockmovestaxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockmovestaxes` (
  `stkmoveno` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0',
  `taxcalculationorder` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkmoveno`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  KEY `calculationorder` (`taxcalculationorder`),
  CONSTRAINT `stockmovestaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockmovestaxes`
--

LOCK TABLES `stockmovestaxes` WRITE;
/*!40000 ALTER TABLE `stockmovestaxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockmovestaxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockserialitems`
--

DROP TABLE IF EXISTS `stockserialitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockserialitems` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `expirationdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `quantity` double NOT NULL DEFAULT '0',
  `qualitytext` mediumtext NOT NULL,
  PRIMARY KEY (`stockid`,`serialno`,`loccode`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `stockserialitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockserialitems_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockserialitems`
--

LOCK TABLES `stockserialitems` WRITE;
/*!40000 ALTER TABLE `stockserialitems` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockserialitems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockserialmoves`
--

DROP TABLE IF EXISTS `stockserialmoves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL AUTO_INCREMENT,
  `stockmoveno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkitmmoveno`),
  KEY `StockMoveNo` (`stockmoveno`),
  KEY `StockID_SN` (`stockid`,`serialno`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `stockserialmoves_ibfk_1` FOREIGN KEY (`stockmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockserialmoves_ibfk_2` FOREIGN KEY (`stockid`, `serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockserialmoves`
--

LOCK TABLES `stockserialmoves` WRITE;
/*!40000 ALTER TABLE `stockserialmoves` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockserialmoves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppallocs`
--

DROP TABLE IF EXISTS `suppallocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppallocs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amt` double NOT NULL DEFAULT '0',
  `datealloc` date NOT NULL DEFAULT '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL DEFAULT '0',
  `transid_allocto` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  KEY `DateAlloc` (`datealloc`),
  CONSTRAINT `suppallocs_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `supptrans` (`id`),
  CONSTRAINT `suppallocs_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `supptrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppallocs`
--

LOCK TABLES `suppallocs` WRITE;
/*!40000 ALTER TABLE `suppallocs` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppallocs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliercontacts`
--

DROP TABLE IF EXISTS `suppliercontacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliercontacts` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `position` varchar(30) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `mobile` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `ordercontact` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`supplierid`,`contact`),
  KEY `Contact` (`contact`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `suppliercontacts_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliercontacts`
--

LOCK TABLES `suppliercontacts` WRITE;
/*!40000 ALTER TABLE `suppliercontacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliercontacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `suppname` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `supptype` tinyint(4) NOT NULL DEFAULT '1',
  `lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `lng` float(10,6) NOT NULL DEFAULT '0.000000',
  `currcode` char(3) NOT NULL DEFAULT '',
  `suppliersince` date NOT NULL DEFAULT '0000-00-00',
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `lastpaid` double NOT NULL DEFAULT '0',
  `lastpaiddate` datetime DEFAULT NULL,
  `bankact` varchar(30) NOT NULL DEFAULT '',
  `bankref` varchar(12) NOT NULL DEFAULT '',
  `bankpartics` varchar(12) NOT NULL DEFAULT '',
  `remittance` tinyint(4) NOT NULL DEFAULT '1',
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '1',
  `factorcompanyid` int(11) NOT NULL DEFAULT '1',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `phn` varchar(50) NOT NULL DEFAULT '',
  `port` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`supplierid`),
  KEY `CurrCode` (`currcode`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SupplierID` (`supplierid`),
  KEY `SuppName` (`suppname`),
  KEY `taxgroupid` (`taxgroupid`),
  KEY `suppliers_ibfk_4` (`factorcompanyid`),
  CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `suppliers_ibfk_3` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliertype`
--

DROP TABLE IF EXISTS `suppliertype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliertype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliertype`
--

LOCK TABLES `suppliertype` WRITE;
/*!40000 ALTER TABLE `suppliertype` DISABLE KEYS */;
INSERT INTO `suppliertype` VALUES (1,'Default');
/*!40000 ALTER TABLE `suppliertype` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supptrans`
--

DROP TABLE IF EXISTS `supptrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supptrans` (
  `transno` int(11) NOT NULL DEFAULT '0',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `suppreference` varchar(20) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '0000-00-00',
  `duedate` date NOT NULL DEFAULT '0000-00-00',
  `inputdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `settled` tinyint(4) NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '1',
  `ovamount` double NOT NULL DEFAULT '0',
  `ovgst` double NOT NULL DEFAULT '0',
  `diffonexch` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  `transtext` mediumtext,
  `hold` tinyint(4) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TypeTransNo` (`transno`,`type`),
  KEY `DueDate` (`duedate`),
  KEY `Hold` (`hold`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Settled` (`settled`),
  KEY `SupplierNo_2` (`supplierno`,`suppreference`),
  KEY `SuppReference` (`suppreference`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  CONSTRAINT `supptrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `supptrans_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supptrans`
--

LOCK TABLES `supptrans` WRITE;
/*!40000 ALTER TABLE `supptrans` DISABLE KEYS */;
/*!40000 ALTER TABLE `supptrans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supptranstaxes`
--

DROP TABLE IF EXISTS `supptranstaxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`supptransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `supptranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `supptranstaxes_ibfk_2` FOREIGN KEY (`supptransid`) REFERENCES `supptrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supptranstaxes`
--

LOCK TABLES `supptranstaxes` WRITE;
/*!40000 ALTER TABLE `supptranstaxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `supptranstaxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `systypes`
--

DROP TABLE IF EXISTS `systypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `systypes` (
  `typeid` smallint(6) NOT NULL DEFAULT '0',
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`typeid`),
  KEY `TypeNo` (`typeno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `systypes`
--

LOCK TABLES `systypes` WRITE;
/*!40000 ALTER TABLE `systypes` DISABLE KEYS */;
INSERT INTO `systypes` VALUES (0,'Journal - GL',0),(1,'Payment - GL',0),(2,'Receipt - GL',0),(3,'Standing Journal',0),(10,'Sales Invoice',0),(11,'Credit Note',0),(12,'Receipt',0),(15,'Journal - Debtors',0),(16,'Location Transfer',0),(17,'Stock Adjustment',0),(18,'Purchase Order',0),(19,'Picking List',1),(20,'Purchase Invoice',0),(21,'Debit Note',0),(22,'Creditors Payment',0),(23,'Creditors Journal',0),(25,'Purchase Order Delivery',0),(26,'Work Order Receipt',0),(28,'Work Order Issue',0),(29,'Work Order Variance',0),(30,'Sales Order',0),(31,'Shipment Close',0),(32,'Contract Close',1),(35,'Cost Update',0),(36,'Exchange Difference',0),(40,'Work Order',0),(41,'Asset Addition',1),(42,'Asset Category Change',1),(43,'Delete w/down asset',1),(44,'Depreciation',1),(49,'Import Fixed Assets',1),(50,'Opening Balance',0),(500,'Auto Debtor Number',0);
/*!40000 ALTER TABLE `systypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `tagref` tinyint(4) NOT NULL AUTO_INCREMENT,
  `tagdescription` varchar(50) NOT NULL,
  PRIMARY KEY (`tagref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxauthorities`
--

DROP TABLE IF EXISTS `taxauthorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxauthorities` (
  `taxid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `description` varchar(20) NOT NULL DEFAULT '',
  `taxglcode` int(11) NOT NULL DEFAULT '0',
  `purchtaxglaccount` int(11) NOT NULL DEFAULT '0',
  `bank` varchar(50) NOT NULL DEFAULT '',
  `bankacctype` varchar(20) NOT NULL DEFAULT '',
  `bankacc` varchar(50) NOT NULL DEFAULT '',
  `bankswift` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxid`),
  KEY `TaxGLCode` (`taxglcode`),
  KEY `PurchTaxGLAccount` (`purchtaxglaccount`),
  CONSTRAINT `taxauthorities_ibfk_1` FOREIGN KEY (`taxglcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `taxauthorities_ibfk_2` FOREIGN KEY (`purchtaxglaccount`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxauthorities`
--

LOCK TABLES `taxauthorities` WRITE;
/*!40000 ALTER TABLE `taxauthorities` DISABLE KEYS */;
INSERT INTO `taxauthorities` VALUES (1,'Australian GST',2300,2310,'','','',''),(5,'Sales Tax',2300,2310,'','','',''),(11,'Canadian GST',2300,2310,'','','',''),(12,'Ontario PST',2300,2310,'','','',''),(13,'UK VAT',2300,2310,'','','','');
/*!40000 ALTER TABLE `taxauthorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxauthrates`
--

DROP TABLE IF EXISTS `taxauthrates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxauthrates` (
  `taxauthority` tinyint(4) NOT NULL DEFAULT '1',
  `dispatchtaxprovince` tinyint(4) NOT NULL DEFAULT '1',
  `taxcatid` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`taxauthority`,`dispatchtaxprovince`,`taxcatid`),
  KEY `TaxAuthority` (`taxauthority`),
  KEY `dispatchtaxprovince` (`dispatchtaxprovince`),
  KEY `taxcatid` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_1` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `taxauthrates_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_3` FOREIGN KEY (`dispatchtaxprovince`) REFERENCES `taxprovinces` (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxauthrates`
--

LOCK TABLES `taxauthrates` WRITE;
/*!40000 ALTER TABLE `taxauthrates` DISABLE KEYS */;
INSERT INTO `taxauthrates` VALUES (1,1,1,0.1),(1,1,2,0),(1,1,5,0),(5,1,1,0.2),(5,1,2,0.35),(5,1,5,0),(11,1,1,0.07),(11,1,2,0.12),(11,1,5,0),(12,1,1,0.05),(12,1,2,0.075),(12,1,5,0),(13,1,1,0),(13,1,2,0),(13,1,5,0);
/*!40000 ALTER TABLE `taxauthrates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxcategories`
--

DROP TABLE IF EXISTS `taxcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxcategories` (
  `taxcatid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `taxcatname` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxcatid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxcategories`
--

LOCK TABLES `taxcategories` WRITE;
/*!40000 ALTER TABLE `taxcategories` DISABLE KEYS */;
INSERT INTO `taxcategories` VALUES (1,'Taxable supply'),(2,'Luxury Items'),(4,'Exempt'),(5,'Freight');
/*!40000 ALTER TABLE `taxcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxgroups`
--

DROP TABLE IF EXISTS `taxgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `taxgroupdescription` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxgroupid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxgroups`
--

LOCK TABLES `taxgroups` WRITE;
/*!40000 ALTER TABLE `taxgroups` DISABLE KEYS */;
INSERT INTO `taxgroups` VALUES (1,'Default tax group'),(2,'Ontario'),(3,'UK Inland Revenue');
/*!40000 ALTER TABLE `taxgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxgrouptaxes`
--

DROP TABLE IF EXISTS `taxgrouptaxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxgrouptaxes` (
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `calculationorder` tinyint(4) NOT NULL DEFAULT '0',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`taxgroupid`,`taxauthid`),
  KEY `taxgroupid` (`taxgroupid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `taxgrouptaxes_ibfk_1` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`),
  CONSTRAINT `taxgrouptaxes_ibfk_2` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxgrouptaxes`
--

LOCK TABLES `taxgrouptaxes` WRITE;
/*!40000 ALTER TABLE `taxgrouptaxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxgrouptaxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxprovinces`
--

DROP TABLE IF EXISTS `taxprovinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxprovinces` (
  `taxprovinceid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `taxprovincename` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxprovinceid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxprovinces`
--

LOCK TABLES `taxprovinces` WRITE;
/*!40000 ALTER TABLE `taxprovinces` DISABLE KEYS */;
INSERT INTO `taxprovinces` VALUES (1,'Default Tax province');
/*!40000 ALTER TABLE `taxprovinces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unitsofmeasure`
--

DROP TABLE IF EXISTS `unitsofmeasure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `unitname` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`unitid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unitsofmeasure`
--

LOCK TABLES `unitsofmeasure` WRITE;
/*!40000 ALTER TABLE `unitsofmeasure` DISABLE KEYS */;
INSERT INTO `unitsofmeasure` VALUES (1,'each'),(2,'metres'),(3,'kgs'),(4,'litres'),(5,'length'),(6,'pack');
/*!40000 ALTER TABLE `unitsofmeasure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usermenurights`
--

DROP TABLE IF EXISTS `usermenurights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usermenurights` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `menuid` int(11) NOT NULL DEFAULT '0',
  `access` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`userid`,`menuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usermenurights`
--

LOCK TABLES `usermenurights` WRITE;
/*!40000 ALTER TABLE `usermenurights` DISABLE KEYS */;
INSERT INTO `usermenurights` VALUES ('admin',1,1),('admin',2,1),('admin',3,1),('admin',4,1),('admin',5,1),('admin',6,1),('admin',7,1),('admin',8,1),('admin',9,1),('admin',10,1),('admin',11,1),('admin',12,1),('admin',13,1),('admin',14,1),('admin',15,1),('admin',16,1),('admin',17,1),('admin',18,1),('admin',19,1),('admin',20,1),('admin',21,1),('admin',22,1),('admin',23,1),('admin',24,1),('admin',25,1),('admin',26,1),('admin',27,1),('admin',28,1),('admin',29,1),('admin',30,1),('admin',31,1),('admin',32,1),('admin',33,1),('admin',34,1),('admin',35,1),('admin',36,1),('admin',37,1),('admin',38,1),('admin',39,1),('admin',40,1),('admin',41,1),('admin',42,1),('admin',43,1),('admin',44,1),('admin',45,1),('admin',46,1),('admin',47,1),('admin',48,1),('admin',49,1),('admin',50,1),('admin',51,1),('admin',52,1),('admin',53,1),('admin',54,1),('admin',55,1),('admin',56,1),('admin',57,1),('admin',58,1),('admin',59,1),('admin',60,1),('admin',61,1),('admin',62,1),('admin',63,1),('admin',64,1),('admin',65,1),('admin',66,1),('admin',67,1),('admin',68,1),('admin',69,1),('admin',70,1),('admin',71,1),('admin',72,1),('admin',73,1),('admin',74,1),('admin',75,1),('admin',76,1),('admin',77,1),('admin',78,1),('admin',79,1),('admin',80,1),('admin',81,1),('admin',82,1),('admin',83,1),('admin',84,1),('admin',85,1),('admin',86,1),('admin',87,1),('admin',88,1),('admin',89,1),('admin',90,1),('admin',91,1),('admin',92,1),('admin',93,1),('admin',94,1),('admin',95,1),('admin',96,1),('admin',97,1),('admin',98,1),('admin',99,1),('admin',100,1),('admin',101,1),('admin',102,1),('admin',103,1),('admin',104,1),('admin',105,1),('admin',106,1),('admin',107,1),('admin',108,1),('admin',109,1),('admin',110,1),('admin',111,1),('admin',112,1),('admin',113,1),('admin',114,1),('admin',115,1),('admin',116,1),('admin',117,1),('admin',118,1),('admin',119,1),('admin',120,1),('admin',121,1),('admin',122,1),('admin',123,1),('admin',124,1),('admin',125,1),('admin',126,1),('admin',127,1),('admin',128,1),('admin',129,1),('admin',130,1),('admin',131,1),('admin',132,1),('admin',133,1),('admin',134,1),('admin',135,1),('admin',136,1),('admin',137,1),('admin',138,1),('admin',139,1),('admin',140,1),('admin',141,1),('admin',142,1),('admin',143,1),('admin',144,1),('admin',145,1),('admin',146,1),('admin',147,1),('admin',148,1),('admin',149,1),('admin',150,1),('admin',151,1),('admin',152,1),('admin',153,1),('admin',154,1),('admin',155,1),('admin',156,1),('admin',157,1),('admin',158,1),('admin',159,1),('admin',160,1),('admin',161,1),('admin',162,1),('admin',163,1),('admin',164,1),('admin',165,1),('admin',166,1),('admin',167,1),('admin',168,1),('admin',169,1),('admin',170,1),('admin',171,1),('admin',172,1),('admin',173,1),('admin',174,1),('admin',175,1),('admin',176,1),('admin',177,1),('admin',178,1),('admin',179,1),('admin',180,1),('admin',181,1),('admin',182,1),('admin',183,1),('admin',184,1),('admin',185,1),('admin',186,1),('admin',187,1),('admin',188,1),('admin',189,1),('admin',190,1),('admin',191,1),('admin',192,1),('admin',193,1),('admin',194,1),('admin',195,1),('admin',196,1),('admin',197,1),('admin',198,1),('admin',199,1),('admin',200,1),('admin',201,1),('admin',202,1),('admin',203,1),('admin',204,1),('admin',205,1),('admin',206,1),('admin',207,1),('admin',208,1),('admin',209,1),('admin',210,1),('admin',211,1),('admin',212,1),('admin',213,1),('admin',214,1),('admin',215,1),('admin',216,1),('admin',217,1),('admin',218,1),('admin',219,1),('admin',220,1),('admin',221,1),('admin',222,1),('admin',223,1),('admin',224,1),('admin',225,1),('admin',226,1),('admin',227,1);
/*!40000 ALTER TABLE `usermenurights` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `woitems`
--

DROP TABLE IF EXISTS `woitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `woitems` (
  `wo` int(11) NOT NULL,
  `stockid` char(20) NOT NULL DEFAULT '',
  `qtyreqd` double NOT NULL DEFAULT '1',
  `qtyrecd` double NOT NULL DEFAULT '0',
  `stdcost` double NOT NULL,
  `nextlotsnref` varchar(20) DEFAULT '',
  PRIMARY KEY (`wo`,`stockid`),
  KEY `stockid` (`stockid`),
  CONSTRAINT `woitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `woitems_ibfk_2` FOREIGN KEY (`wo`) REFERENCES `workorders` (`wo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `woitems`
--

LOCK TABLES `woitems` WRITE;
/*!40000 ALTER TABLE `woitems` DISABLE KEYS */;
/*!40000 ALTER TABLE `woitems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `worequirements`
--

DROP TABLE IF EXISTS `worequirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worequirements` (
  `wo` int(11) NOT NULL,
  `parentstockid` varchar(20) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `qtypu` double NOT NULL DEFAULT '1',
  `stdcost` double NOT NULL DEFAULT '0',
  `autoissue` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wo`,`parentstockid`,`stockid`),
  KEY `stockid` (`stockid`),
  KEY `worequirements_ibfk_3` (`parentstockid`),
  CONSTRAINT `worequirements_ibfk_1` FOREIGN KEY (`wo`) REFERENCES `workorders` (`wo`),
  CONSTRAINT `worequirements_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `worequirements_ibfk_3` FOREIGN KEY (`wo`, `parentstockid`) REFERENCES `woitems` (`wo`, `stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `worequirements`
--

LOCK TABLES `worequirements` WRITE;
/*!40000 ALTER TABLE `worequirements` DISABLE KEYS */;
/*!40000 ALTER TABLE `worequirements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workcentres`
--

DROP TABLE IF EXISTS `workcentres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workcentres` (
  `code` char(5) NOT NULL DEFAULT '',
  `location` char(5) NOT NULL DEFAULT '',
  `description` char(20) NOT NULL DEFAULT '',
  `capacity` double NOT NULL DEFAULT '1',
  `overheadperhour` decimal(10,0) NOT NULL DEFAULT '0',
  `overheadrecoveryact` int(11) NOT NULL DEFAULT '0',
  `setuphrs` decimal(10,0) NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`),
  KEY `Description` (`description`),
  KEY `Location` (`location`),
  CONSTRAINT `workcentres_ibfk_1` FOREIGN KEY (`location`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workcentres`
--

LOCK TABLES `workcentres` WRITE;
/*!40000 ALTER TABLE `workcentres` DISABLE KEYS */;
/*!40000 ALTER TABLE `workcentres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workorders`
--

DROP TABLE IF EXISTS `workorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workorders` (
  `wo` int(11) NOT NULL,
  `loccode` char(5) NOT NULL DEFAULT '',
  `requiredby` date NOT NULL DEFAULT '0000-00-00',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `costissued` double NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wo`),
  KEY `LocCode` (`loccode`),
  KEY `StartDate` (`startdate`),
  KEY `RequiredBy` (`requiredby`),
  CONSTRAINT `worksorders_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workorders`
--

LOCK TABLES `workorders` WRITE;
/*!40000 ALTER TABLE `workorders` DISABLE KEYS */;
/*!40000 ALTER TABLE `workorders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `woserialnos`
--

DROP TABLE IF EXISTS `woserialnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `woserialnos` (
  `wo` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `serialno` varchar(30) NOT NULL,
  `quantity` double NOT NULL DEFAULT '1',
  `qualitytext` mediumtext NOT NULL,
  PRIMARY KEY (`wo`,`stockid`,`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `woserialnos`
--

LOCK TABLES `woserialnos` WRITE;
/*!40000 ALTER TABLE `woserialnos` DISABLE KEYS */;
/*!40000 ALTER TABLE `woserialnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `www_users`
--

DROP TABLE IF EXISTS `www_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `www_users` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `password` mediumtext NOT NULL,
  `realname` varchar(35) NOT NULL DEFAULT '',
  `customerid` varchar(10) NOT NULL DEFAULT '',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `salesman` char(3) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `fullaccess` int(11) NOT NULL DEFAULT '1',
  `lastvisitdate` datetime DEFAULT NULL,
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `pagesize` varchar(20) NOT NULL DEFAULT 'A4',
  `modulesallowed` varchar(40) NOT NULL DEFAULT '0,0,0,0,0,0,0,0,0,0,0,',
  `blocked` tinyint(4) NOT NULL DEFAULT '0',
  `displayrecordsmax` int(11) NOT NULL DEFAULT '0',
  `theme` varchar(30) NOT NULL DEFAULT 'fresh',
  `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `pdflanguage` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`),
  KEY `CustomerID` (`customerid`),
  KEY `DefaultLocation` (`defaultlocation`),
  CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `www_users`
--

LOCK TABLES `www_users` WRITE;
/*!40000 ALTER TABLE `www_users` DISABLE KEYS */;
INSERT INTO `www_users` VALUES ('admin','weberp','Demonstration user','','','','','','MEL',8,'2011-01-15 14:59:12','','A4','1,1,1,1,1,1,1,1,1,1,',0,50,'silverwolf','en_GB.utf8',0);
/*!40000 ALTER TABLE `www_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-01-15 15:01:49
