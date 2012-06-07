<?php

/* Include the new script for printing quotations in Portrait
 */

AddIndex(array('unitname'), 'unitsofmeasure', 'UnitName', $db);

CreateTable('bomheader', "CREATE TABLE bomheader (
`bomid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`productid` VARCHAR(20) NOT NULL DEFAULT '',
`uom` VARCHAR(20) NOT NULL DEFAULT '',
`revisionlevel` INT NOT NULL DEFAULT 1,
KEY `ProductID` (`productid`),
CONSTRAINT `bomheader_ibfk_1` FOREIGN KEY (`productid`) REFERENCES `stockmaster` (`stockid`),
CONSTRAINT `bomheader_ibfk_2` FOREIGN KEY (`uom`) REFERENCES `unitsofmeasure` (`unitname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

CreateTable('bomlines', "CREATE TABLE bomlines (
`lineid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`headerid` INT NOT NULL DEFAULT 0,
`componentid` VARCHAR(20) NOT NULL DEFAULT '',
`uom` VARCHAR(20) NOT NULL DEFAULT '',
`revisionlevel` INT NOT NULL DEFAULT 1,
`quantity` double NOT NULL DEFAULT '1',
`effectivefrom` DATE NOT NULL DEFAULT '0000-00-00',
`effectiveto` DATE NOT NULL DEFAULT '0000-00-00',
`loccode` char(5) NOT NULL DEFAULT '',
`workcentreadded` char(5) NOT NULL DEFAULT '',
`autoissue` tinyint(4) NOT NULL DEFAULT '0',
KEY `ComponentID` (`componentid`),
CONSTRAINT `bomlines_ibfk_1` FOREIGN KEY (`componentid`) REFERENCES `stockmaster` (`stockid`),
CONSTRAINT `bomlines_ibfk_2` FOREIGN KEY (`headerid`) REFERENCES `bomheader` (`bomid`),
CONSTRAINT `bomlines_ibfk_3` FOREIGN KEY (`uom`) REFERENCES `unitsofmeasure` (`unitname`),
CONSTRAINT `bomlines_ibfk_4` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
CONSTRAINT `bomlines_ibfk_5` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

$sql="INSERT INTO `bomheader` (`productid`, `uom`, `revisionlevel`) (SELECT DISTINCT parent, (SELECT units FROM stockmaster WHERE stockid=bom.parent), 1 FROM `bom`)";

$result=DB_query($sql, $db);

$sql="INSERT INTO `bomlines` (`headerid`, `componentid`, `uom`, `revisionlevel`, `quantity`, `effectivefrom`, `effectiveto`, `loccode`, `workcentreadded`, `autoissue`)
	(SELECT (SELECT bomid FROM bomheader WHERE productid=bom.parent), component, (SELECT units FROM stockmaster WHERE stockid=bom.parent), 1, quantity, effectiveafter, effectiveto, loccode, workcentreadded, autoissue FROM `bom`)";

$result=DB_query($sql, $db);

DropTable('bom', 'parent', $db);

$sql="CREATE VIEW bom (`parent`, `component`, `workcentreadded`, `loccode`, `effectiveafter`, `effectiveto`, `quantity`, `autoissue`)
AS SELECT bomheader.productid, bomlines.componentid, bomlines.workcentreadded, bomlines.loccode, bomlines.effectivefrom, bomlines.effectiveto,
sum(bomlines.quantity), bomlines.autoissue FROM bomheader LEFT JOIN bomlines ON bomheader.bomid=bomlines.headerid GROUP BY bomheader.productid, bomlines.componentid";

$result=DB_query($sql, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>