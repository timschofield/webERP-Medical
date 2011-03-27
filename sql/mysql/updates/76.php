<?php

AddColumn('forpreprint', 'paymentmethods', 'tinyint(1)', 'NOT NULL', '0', 'receipttype', $db);

UpdateDBNo(76, $db);

?>