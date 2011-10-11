<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('Departments.php',11), array('script', 'security'), array('Departments.php',11), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('StockTransfers_departments.php',11), array('script', 'security'), array('StockTransfers_departments.php',11), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('PDFStockTransfer_departments.php',11), array('script', 'security'), array('PDFStockTransfer_departments.php',11), $db);

CreateTable('departments', "CREATE TABLE departments (
`departmentid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`description` VARCHAR (100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

CreateTable('dispatch', "CREATE TABLE dispatch (
`dispatchid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`loccode` VARCHAR (5) NOT NULL DEFAULT '',
`departmentid` INT NOT NULL DEFAULT 0,
`despatchdate` DATE NOT NULL DEFAULT '0000-00-00',
`narrative` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

CreateTable('dispatchitems', "CREATE TABLE dispatchitems (
`dispatchitemsid` INT NOT NULL DEFAULT 0,
`dispatchid` INT NOT NULL DEFAULT 0,
`itemid` VARCHAR (30) NOT NULL DEFAULT '',
`quantity` DOUBLE NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

UpdateDBNo(95, $db);

?>