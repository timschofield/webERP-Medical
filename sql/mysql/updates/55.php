<?php

/* Split the cash sales customer field into debtorno and branchcode
 * fields
 */

ChangeColumnSize('cashsalecustomer', 'locations', 'varchar(10)', 'NOT NULL', '', 10, $db);
AddColumn('cashsalebranch', 'locations', 'varchar(10)', 'NOT NULL', '', 'cashsalecustomer', $db);

$sql="SELECT loccode, cashsalecustomer FROM locations WHERE cashsalecustomer!=''";
$result=DB_query($sql, $db);
while ($myrow=DB_fetch_array($result, $db)) {
	$CustArray=explode('-', $myrow['cashsalecustomer']);
	$UpdateSQL="UPDATE locations
				SET cashsalecustomer='".$CustArray[0]."',
					cashsalebranch='".$CustArray[1]."'
				WHERE loccode='".$myrow['loccode']."'";
	$UpdateRow=DB_query($UpdateSQL, $db);
}

UpdateDBNo(55, $db);

?>