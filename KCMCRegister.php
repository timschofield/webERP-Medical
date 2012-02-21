<?php
$PageSecurity=1;

include('includes/session.inc');
$title = _('Register a Patient');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="'
	. _('Search') . '" alt="" />' . $title.'</p>';

if (isset($_POST['Create'])) {

	$SalesAreaSQL = "SELECT areacode FROM areas";
	$SalesAreaResult = DB_query($SalesAreaSQL, $db);
	$SalesAreaRow = DB_fetch_array($SalesAreaResult);

	$SalesManSQL = "SELECT salesmancode FROM salesman";
	$SalesManResult = DB_query($SalesManSQL, $db);
	$SalesManRow = DB_fetch_array($SalesManResult);

	$sql = "INSERT INTO debtorsmaster (debtorno,
										name,
										currcode,
										salestype,
										clientsince,
										holdreason,
										paymentterms)
									VALUES (
										'".$_POST['FileNumber']."',
										'".$_POST['Name']."',
										'".$_SESSION['CompanyRecord']['currencydefault']."',
										'".$_POST['SalesType']."',
										'".date('Y-m-d')."',
										'1',
										'20'
									)";

	$result=DB_query($sql, $db);

	$sql = "INSERT INTO custbranch (branchcode,
									debtorno,
									brname,
									area,
									salesman,
									phoneno,
									defaultlocation,
									taxgroupid)
								VALUES (
									'CASH',
									'".$_POST['FileNumber']."',
									'CASH',
									'".$SalesAreaRow['areacode'] . "',
									'".$SalesManRow['salesmancode']."',
									'".$_POST['Telephone']."',
									'".$_SESSION['DefaultFactoryLocation']."',
									'1'
								)";
	$result=DB_query($sql, $db);

	if ($_POST['Insurance'] != '') {
		$sql = "INSERT INTO custbranch (branchcode,
									debtorno,
									brname,
									area,
									salesman,
									phoneno,
									defaultlocation,
									taxgroupid)
								VALUES (
									'".$_POST['Insurance']."',
									'".$_POST['FileNumber']."',
									'".$_POST['Insurance']."',
									'".$SalesAreaRow['areacode'] . "',
									'".$SalesManRow['salesmancode']."',
									'".$_POST['Telephone']."',
									'".$_SESSION['DefaultFactoryLocation']."',
									'1'
								)";
		$result=DB_query($sql, $db);

		prnMsg( _('The patient has been successfully registered'), 'success');
	}

} else {

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table cellpadding=3 colspan=4 class=selection>';

	echo '<tr><th colspan="2"><font size="3" color="navy">'._('New Patient Details') . '</font></th></tr>';
	echo '<tr><td>'._('File Number').':</td>';
	echo '<td><input type="text" size="10" name="FileNumber" value="" /></td></tr>';

	echo '<tr><td>'._('Name').':</td>';
	echo '<td><input type="text" size="20" name="Name" value="" /></td></tr>';

	echo '<tr><td>'._('Telephone Number').':</td>';
	echo '<td><input type="text" size="12" name="Telephone" value="" /></td></tr>';

	$result=DB_query("SELECT typeabbrev, sales_type FROM salestypes",$db);
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<a href="SalesTypes.php?" target="_parent">Setup Types</a>';
		echo '<tr><td colspan=2>' . prnMsg(_('No sales types/price lists defined'),'error') . '</td></tr>';
	} else {
		echo '<tr><td>' . _('Price List') . ':</td>
				<td><select tabindex=9 name="SalesType">';
		echo '<option value=""></option>';

		while ($myrow = DB_fetch_array($result)) {
			echo '<option value="'. $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
		} //end while loopre
		DB_data_seek($result,0);
		echo '</select></td></tr>';
	}
	$sql="SELECT debtorno,
				name
				FROM debtorsmaster
				LEFT JOIN debtortype
				ON debtorsmaster.typeid=debtortype.typeid
				WHERE debtortype.typename='Insurance'";
	$result=DB_query($sql, $db);

	echo '<tr><td>'._('Insurance Company').':</td>';
	echo '<td><select name="Insurance">';
	echo '<option value=""></option>';
	while ($myrow=DB_fetch_array($result)) {
		echo '<option value="'.$myrow['debtorno'].'">'.$myrow['name'].'</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo '<br /><div class="centre"><input type="submit" name="Create" value="Register the patient" /></div>';
	echo '</form>';
}

include('includes/footer.inc');
?>