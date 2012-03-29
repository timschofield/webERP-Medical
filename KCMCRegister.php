<?php
$PageSecurity=1;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
$title = _('Register a Patient');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/PatientData.png" title="' . _('Search') . '" alt="" />' . $title.'</p>';

if (isset($_POST['Create'])) {

	$SalesAreaSQL = "SELECT areacode FROM areas";
	$SalesAreaResult = DB_query($SalesAreaSQL, $db);
	$SalesAreaRow = DB_fetch_array($SalesAreaResult);

	$SalesManSQL = "SELECT salesmancode FROM salesman";
	$SalesManResult = DB_query($SalesManSQL, $db);
	$SalesManRow = DB_fetch_array($SalesManResult);

	if ($_SESSION['AutoDebtorNo'] > 0) {
		/* system assigned, sequential, numeric */
		if ($_SESSION['AutoDebtorNo']== 1) {
			$_POST['FileNumber'] = GetNextTransNo(500, $db);
		}
	}

	$sql = "INSERT INTO debtorsmaster (debtorno,
										name,
										address1,
										address2,
										address3,
										address4,
										address5,
										address6,
										currcode,
										salestype,
										clientsince,
										holdreason,
										paymentterms)
									VALUES (
										'".$_POST['FileNumber']."',
										'".$_POST['Name']."',
										'".$_POST['Address1']."',
										'".$_POST['Address2']."',
										'".$_POST['Address3']."',
										'".$_POST['Address4']."',
										'".$_POST['Address5']."',
										'".$_POST['Address6']."',
										'".$_SESSION['CompanyRecord']['currencydefault']."',
										'".$_POST['SalesType']."',
										'".FormatDateForSQL($_POST['DateOfBirth'])."',
										'".$_POST['Sex']."',
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

	}
	prnMsg( _('The patient') . ' ' . $_POST['FileNumber'] . ' ' . _('has been successfully registered'), 'success');

} else {

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table cellpadding=3 colspan=4 class=selection>';

	echo '<tr><th colspan="2"><font size="3" color="navy">'._('New Patient Details') . '</font></th></tr>';

	if ($_SESSION['AutoDebtorNo'] == 0) {
		echo '<tr><td>'._('File Number').':</td>';
		if (isset($_POST['FileNumber'])) {
			echo '<td><input type="text" size="10" name="FileNumber" value="'.$_POST['FileNumber'].'" /></td></tr>';
		} else {
			echo '<td><input type="text" size="10" name="FileNumber" value="" /></td></tr>';
		}
	}

	echo '<tr><td>'._('Name').':</td>';
	if (isset($_POST['Name'])) {
		echo '<td><input type="text" size="20" name="Name" value="'.$_POST['Name'].'" /></td></tr>';
	} else {
		echo '<td><input type="text" size="20" name="Name" value="" /></td></tr>';
	}

	echo '<tr><td>'._('Address').':</td>';
	if (isset($_POST['Address1'])) {
		echo '<td><input type="text" size="20" name="Address1" value="'.$_POST['Address1'].'" /></td></tr>';
	} else {
		echo '<td><input type="text" size="20" name="Address1" value="" /></td></tr>';
	}
	if (isset($_POST['Address2'])) {
		echo '<td></td><td><input type="text" size="20" name="Address2" value="'.$_POST['Address2'].'" /></td></tr>';
	} else {
		echo '<td></td><td><input type="text" size="20" name="Address2" value="" /></td></tr>';
	}
	if (isset($_POST['Address3'])) {
		echo '<td></td><td><input type="text" size="20" name="Address3" value="'.$_POST['Address3'].'" /></td></tr>';
	} else {
		echo '<td></td><td><input type="text" size="20" name="Address3" value="" /></td></tr>';
	}
	if (isset($_POST['Address4'])) {
		echo '<td></td><td><input type="text" size="20" name="Address4" value="'.$_POST['Address4'].'" /></td></tr>';
	} else {
		echo '<td></td><td><input type="text" size="20" name="Address4" value="" /></td></tr>';
	}
	if (isset($_POST['Address5'])) {
		echo '<td></td><td><input type="text" size="20" name="Address5" value="'.$_POST['Address5'].'" /></td></tr>';
	} else {
		echo '<td></td><td><input type="text" size="20" name="Address5" value="" /></td></tr>';
	}
	if (isset($_POST['Address6'])) {
		echo '<td></td><td><input type="text" size="20" name="Address6" value="'.$_POST['Address6'].'" /></td></tr>';
	} else {
		echo '<td></td><td><input type="text" size="20" name="Address6" value="" /></td></tr>';
	}

	echo '<tr><td>'._('Telephone Number').':</td>';
	if (isset($_POST['Telephone'])) {
		echo '<td><input type="text" size="12" name="Telephone" value="'.$_POST['Telephone'].'" /></td></tr>';
	} else {
		echo '<td><input type="text" size="12" name="Telephone" value="" /></td></tr>';
	}

	echo '<tr><td>'._('Date Of Birth').':</td>';
	if (isset($_POST['DateOfBirth'])) {
		echo '<td><input type="text" placeholder="'.$_SESSION['DefaultDateFormat'].'" name="DateOfBirth" maxlength="10" size="11" value="'.$_POST['DateOfBirth'].'" /></td></tr>';
	} else {
		echo '<td><input type="text" placeholder="'.$_SESSION['DefaultDateFormat'].'" name="DateOfBirth" maxlength="10" size="11" value="" /></td></tr>';
	}

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
			if (isset($_POST['SalesType']) and $_POST['SalesType']==$myrow['typeabbrev']) {
				echo '<option selected="selected" value="'. $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			} else {
				echo '<option value="'. $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			}
		} //end while loopre
		DB_data_seek($result,0);
		echo '</select></td></tr>';
	}
	$sql="SELECT reasoncode,
				reasondescription
				FROM holdreasons";
	$result=DB_query($sql, $db);

	echo '<tr><td>'._('Sex').':</td>';
	echo '<td><select name="Sex">';
	echo '<option value=""></option>';
	while ($myrow=DB_fetch_array($result)) {
		if (isset($_POST['Sex']) and $_POST['Sex']==$myrow['reasoncode']) {
			echo '<option selected="selected" value="'.$myrow['reasoncode'].'">'.$myrow['reasondescription'].'</option>';
		} else {
			echo '<option value="'.$myrow['reasoncode'].'">'.$myrow['reasondescription'].'</option>';
		}
	}
	echo '</select></td></tr>';

	$sql="SELECT debtorno,
				name
				FROM debtorsmaster
				LEFT JOIN debtortype
				ON debtorsmaster.typeid=debtortype.typeid
				WHERE debtortype.typename='Insurance'";
	$result=DB_query($sql, $db);

	echo '<tr><td>'._('Insurance Company').':</td>';
	echo '<td><select name="Insurance" onChange="ReloadForm(ChangeInsurance)">';
	echo '<option value=""></option>';
	while ($myrow=DB_fetch_array($result)) {
		if (isset($_POST['Insurance']) and $_POST['Insurance']==$myrow['debtorno']) {
			echo '<option selected="selected" value="'.$myrow['debtorno'].'">'.$myrow['name'].'</option>';
		} else {
			echo '<option value="'.$myrow['debtorno'].'">'.$myrow['name'].'</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<input type="submit" name="ChangeInsurance" style="visibility: hidden" value=" " />';

	if (isset($_POST['Insurance'])) {
		$sql = "SELECT salesmancode,
						salesmanname,
						smantel,
						smanfax
					FROM salesman";
		$result = DB_query($sql,$db);

		echo '<tr><td>'._('Employer Company').':</td>';
		echo '<td><select name="Employer">';
		echo '<option value=""></option>';
		while ($myrow=DB_fetch_array($result)) {
			echo '<option value="'.$myrow['salesmancode'].'">'.$myrow['salesmanname'].'</option>';
		}
		echo '</select></td></tr>';
	}

	echo '</table>';
	echo '<br /><div class="centre"><input type="submit" name="Create" value="Register the patient" /></div>';
	echo '</form>';
}

include('includes/footer.inc');
?>