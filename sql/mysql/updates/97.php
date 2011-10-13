<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('doc/Manual/ManualContents.php',1), array('script', 'security'), array('doc/Manual/ManualContents.php',1), $db);

RenameTable('dispatch', 'stockrequest', $db);
RenameTable('dispatchitems', 'stockrequestitems', $db);

AddColumn('decimalplaces', 'stockrequestitems', 'int', 'not null', 0, 'quantity', $db);
AddColumn('uom', 'stockrequestitems', 'varchar(20)', 'not null', '', 'decimalplaces', $db);

ChangeColumnType('dispatchid', 'stockrequest', 'INT (11)', 'NOT NULL', 0, $db);

InsertRecord('systypes', array('typeid'), array('38'), array('typeid', 'typename', 'typeno'), array('38', 'Stock Requests', '0'), $db);

UpdateDBNo(97, $db);

?>