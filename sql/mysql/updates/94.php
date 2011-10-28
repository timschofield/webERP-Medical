<?php

CreateTable('stockorderitemproperties', "CREATE TABLE `stockorderitemproperties` (
`stockid` varchar(20) NOT NULL,
`orderno` int(11) NOT NULL,
`orderlineno` int(11) NOT NULL,
`stkcatpropid` int(11) NOT NULL,
`value` varchar(50) NOT NULL,
PRIMARY KEY (`stockid`,`orderno`,`orderlineno`,`stkcatpropid`),
KEY `stockid` (`stockid`),
KEY `orderno` (`orderno`),
KEY `stkcatpropid` (`stkcatpropid`),
KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;", $db);



UpdateDBNo(94, $db);

?>