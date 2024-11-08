<?php
include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/CustomerSearch.php');
$Title = _('Update Patient Details');
include ('includes/header.php');

if (isset($_GET['PatientNumber'])) {
	$Patient[0] = $_GET['PatientNumber'];
	$Patient[1] = $_GET['BranchCode'];
}

if (!isset($_POST['Search']) and !isset($_POST['Next']) and !isset($_POST['Previous']) and !isset($_POST['Go1']) and !isset($_POST['Go2']) and isset($_POST['JustSelectedACustomer']) and empty($_POST['Patient'])) {
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i = 0;$i < count($_POST);$i++) { //loop through the returned customers
		if (isset($_POST['SubmitCustomerSelection' . $i])) {
			break;
		}
	}
	if ($i == count($_POST)) {
		prnMsg(_('Unable to identify the selected customer'), 'error');
	} else {
		$Patient[0] = $_POST['SelectedCustomer' . $i];
		$Patient[1] = $_POST['SelectedBranch' . $i];
		unset($_POST['Search']);
	}
} //end of if search
if (!isset($Patient)) {
	ShowCustomerSearchFields($RootPath, $_SESSION['Theme']);
}

if (isset($_POST['Search']) or isset($_POST['Go1']) or isset($_POST['Go2']) or isset($_POST['Next']) or isset($_POST['Previous'])) {

	$PatientResult = CustomerSearchSQL();
	if (DB_num_rows($PatientResult) == 0) {
		prnMsg(_('No patient records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	}
} //end of if search
if (isset($PatientResult)) {
	ShowReturnedCustomers($PatientResult);
}

if (isset($_POST['Update'])) {

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Search') . '" alt="" />' . $Title . '</p>';

	$SalesAreaSQL = "SELECT areacode FROM areas";
	$SalesAreaResult = DB_query($SalesAreaSQL);
	$SalesAreaRow = DB_fetch_array($SalesAreaResult);

	$SalesManSQL = "SELECT salesmancode FROM salesman";
	$SalesManResult = DB_query($SalesManSQL);
	$SalesManRow = DB_fetch_array($SalesManResult);

	if (!isset($_POST['Employer'])) {
		$_POST['Employer'] = $SalesManRow['salesmancode'];
	}

	$SQL = "UPDATE debtorsmaster SET name='" . $_POST['Name'] . "',
									address1='" . $_POST['Address1'] . "',
									address2='" . $_POST['Address2'] . "',
									address3='" . $_POST['Address3'] . "',
									address4='" . $_POST['Address4'] . "',
									address5='" . $_POST['Address5'] . "',
									address6='" . $_POST['Address6'] . "',
									currcode='" . $_POST['CurrCode'] . "',
									salestype='" . $_POST['SalesType'] . "',
									clientsince='" . FormatDateForSQL($_POST['DateOfBirth']) . "',
									gender='" . $_POST['Sex'] . "'
								WHERE debtorno='" . $_POST['FileNumber'] . "'";
	$Result = DB_query($SQL);

	if ($_POST['ExistingInsurance'] == $_POST['Insurance']) {
		if ($_POST['Insurance'] == 'CASH') {
			$SQL = "UPDATE custbranch SET brname='" . $_POST['Insurance'] . "',
										area='" . $_POST['Area'] . "',
										phoneno='" . $_POST['Telephone'] . "',
										defaultlocation='" . $_SESSION['DefaultFactoryLocation'] . "'
									WHERE debtorno='" . $_POST['FileNumber'] . "'
										AND branchcode='" . $_POST['Insurance'] . "'";
		} else {
			$SQL = "UPDATE custbranch SET brname='" . $_POST['Insurance'] . "',
										area='" . $_POST['Area'] . "',
										salesman='" . $_POST['Employer'] . "',
										phoneno='" . $_POST['Telephone'] . "',
										defaultlocation='" . $_SESSION['DefaultFactoryLocation'] . "'
									WHERE debtorno='" . $_POST['FileNumber'] . "'
										AND branchcode='" . $_POST['Insurance'] . "'";
		}
		$Result = DB_query($SQL);
	} else {
		$SQL = "INSERT INTO custbranch (branchcode,
									debtorno,
									brname,
									area,
									salesman,
									phoneno,
									defaultlocation,
									taxgroupid)
								VALUES (
									'" . $_POST['Insurance'] . "',
									'" . $_POST['FileNumber'] . "',
									'" . $_POST['Insurance'] . "',
									'" . $SalesAreaRow['areacode'] . "',
									'" . $_POST['Employer'] . "',
									'" . $_POST['Telephone'] . "',
									'" . $_SESSION['DefaultFactoryLocation'] . "',
									'1'
								)";
		$Result = DB_query($SQL);

	}
	prnMsg(_('The patient record') . ' ' . $_POST['FileNumber'] . ' ' . _('has been successfully updated'), 'success');
	unset($_POST['FileNumber']);
}

if (isset($Patient)) {

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/PatientFile.png" title="' . _('Search') . '" alt="" />' . $Title . '</p>';

	$SQL = "SELECT name,
				address1,
				address2,
				address3,
				address4,
				address5,
				address6,
				currcode,
				salestype,
				clientsince,
				gender,
				paymentterms,
				custbranch.phoneno,
				custbranch.area,
				custbranch.salesman
			FROM debtorsmaster
			LEFT JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			WHERE debtorsmaster.debtorno='" . $Patient[0] . "'
			AND branchcode='" . $Patient[1] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="FileNumber" value="' . $Patient[0] . '" />';
	echo '<input type="hidden" name="CurrCode" value="' . $MyRow['currcode'] . '" />';
	echo '<input type="hidden" name="ExistingInsurance" value="' . $Patient[1] . '" />';
	echo '<input type="hidden" name="Area" value="' . $MyRow['area'] . '" />';
	echo '<input type="hidden" name="Salesman" value="' . $MyRow['salesman'] . '" />';

	echo '<table class=selection>';

	echo '<tr>
			<th colspan="2">' . _('Update Patient Details') . '</th>
		</tr>';

	echo '<tr>
			<td>' . _('File Number') . ':</td>
			<td>' . $Patient[0] . '</td>
		</tr>';

	echo '<tr>
			<td>' . _('Name') . ':</td>
			<td><input type="text" size="20" name="Name" value="' . trim($MyRow['name']) . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Address') . ':</td>
			<td><input type="text" size="20" name="Address1" value="' . $MyRow['address1'] . '" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="text" size="20" name="Address2" value="' . $MyRow['address2'] . '" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="text" size="20" name="Address3" value="' . $MyRow['address3'] . '" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="text" size="20" name="Address4" value="' . $MyRow['address4'] . '" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="text" size="20" name="Address5" value="' . $MyRow['address5'] . '" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="text" size="20" name="Address6" value="' . $MyRow['address6'] . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Telephone Number') . ':</td>
			<td><input type="text" size="12" name="Telephone" value="' . $MyRow['phoneno'] . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Date Of Birth') . ':</td>
			<td><input type="text" placeholder="' . $_SESSION['DefaultDateFormat'] . '" name="DateOfBirth" maxlength="10" size="11" value="' . ConvertSQLDate($MyRow['clientsince']) . '" /></td>
		</tr>';

	$TypeResult = DB_query("SELECT typeabbrev, sales_type FROM salestypes");
	if (DB_num_rows($TypeResult) == 0) {
		$DataError = 1;
		echo '<a href="SalesTypes.php?" target="_parent">Setup Types</a>';
		echo '<tr><td colspan=2>' . prnMsg(_('No sales types/price lists defined'), 'error') . '</td></tr>';
	} else {
		echo '<tr><td>' . _('Price List') . ':</td>
				<td><select name="SalesType">';
		echo '<option value=""></option>';

		while ($TypeRow = DB_fetch_array($TypeResult)) {
			if ($TypeRow['typeabbrev'] == $MyRow['salestype']) {
				echo '<option selected="selected" value="' . $TypeRow['typeabbrev'] . '">' . $TypeRow['sales_type'] . '</option>';
			} else {
				echo '<option value="' . $TypeRow['typeabbrev'] . '">' . $TypeRow['sales_type'] . '</option>';
			}
		} //end while loopre
		echo '</select></td></tr>';
	}

	$Gender['m'] = _('Male');
	$Gender['f'] = _('Female');
	echo '<tr>
			<td>' . _('Sex') . ':</td>
			<td><select name="Sex">';
	echo '<option value=""></option>';
	foreach ($Gender as $Code => $Name) {
		if ($MyRow['gender'] == $Code) {
			echo '<option selected="selected" value="' . $Code . '">' . $Name . '</option>';
		} else {
			echo '<option value="' . $Code . '">' . $Name . '</option>';
		}
	}
	echo '</select>
			</td>
		</tr>';

	$SQL = "SELECT debtorno,
				name
				FROM debtorsmaster
				LEFT JOIN debtortype
				ON debtorsmaster.typeid=debtortype.typeid
				WHERE debtortype.typename like '%Insurance%'";
	$InsuranceResult = DB_query($SQL);

	echo '<tr><td>' . _('Insurance Company') . ':</td>';
	echo '<td><select name="Insurance">';
	echo '<option value="CASH"></option>';
	while ($InsuranceRow = DB_fetch_array($InsuranceResult)) {
		if ($InsuranceRow['debtorno'] == $Patient[1]) {
			echo '<option selected="selected" value="' . $InsuranceRow['debtorno'] . '">' . $InsuranceRow['name'] . '</option>';
		} else {
			echo '<option value="' . $InsuranceRow['debtorno'] . '">' . $InsuranceRow['name'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	if ((isset($_POST['Insurance']) and $_POST['Insurance'] != '') or $Patient[1] != 'CASH') {
		$SQL = "SELECT salesmancode,
						salesmanname
					FROM salesman";
		$EmployerResult = DB_query($SQL);

		echo '<tr><td>' . _('Employer Company') . ':</td>';
		echo '<td><select name="Employer">';
		echo '<option value=""></option>';
		while ($EmployerRow = DB_fetch_array($EmployerResult)) {
			if (isset($MyRow['salesman']) and ($MyRow['salesman'] == $EmployerRow['salesmancode'])) {
				echo '<option selected="selected" value="' . $EmployerRow['salesmancode'] . '">' . $EmployerRow['salesmanname'] . '</option>';
			} else {
				echo '<option value="' . $EmployerRow['salesmancode'] . '">' . $EmployerRow['salesmanname'] . '</option>';
			}
		}
		echo '</select></td></tr>';
	}
	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="Update" value="' . _('Update Details') . '" />
		</div>';
	echo '</form>';
}

include ('includes/footer.php');
?>