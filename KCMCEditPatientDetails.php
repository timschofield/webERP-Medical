<?php

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CustomerSearch.php');
$title = _('Update Patient Details');
include('includes/header.inc');

if (isset($_GET['PatientNumber'])) {
	$_POST['FileNumber']=$_GET['PatientNumber'].' '.$_GET['BranchCode'];
}

if (!isset($_POST['Search']) and !isset($_POST['Next']) and !isset($_POST['Previous']) and !isset($_POST['Go1']) and !isset($_POST['Go2']) and isset($_POST['JustSelectedACustomer']) and empty($_POST['Patient'])){
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i=0; $i< count($_POST); $i++){ //loop through the returned customers
		if(isset($_POST['SubmitCustomerSelection'.$i])){
			break;
		}
	}
	if ($i==count($_POST)){
		prnMsg(_('Unable to identify the selected customer'),'error');
	} else {
		$Patient[0] = $_POST['SelectedCustomer'.$i];
		$Patient[1] = $_POST['SelectedBranch'.$i];
		unset($_POST['Search']);
	}
} //end of if search

if (!isset($Patient)) {
	ShowCustomerSearchFields($rootpath, $theme, $db);
}

if (isset($_POST['Search']) OR isset($_POST['Go1']) OR isset($_POST['Go2']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {

	$PatientResult = CustomerSearchSQL($db);
	if (DB_num_rows($PatientResult) == 0) {
		prnMsg(_('No patient records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	}
} //end of if search

if (isset($PatientResult)) {
	ShowReturnedCustomers($PatientResult);
}

if (isset($_POST['Update'])) {

	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _('Search') . '" alt="" />' . $title.'</p>';

	$SalesAreaSQL = "SELECT areacode FROM areas";
	$SalesAreaResult = DB_query($SalesAreaSQL, $db);
	$SalesAreaRow = DB_fetch_array($SalesAreaResult);

	$SalesManSQL = "SELECT salesmancode FROM salesman";
	$SalesManResult = DB_query($SalesManSQL, $db);
	$SalesManRow = DB_fetch_array($SalesManResult);

	$sql = "UPDATE debtorsmaster SET name='".$_POST['Name']."',
									address1='".$_POST['Address1']."',
									address2='".$_POST['Address2']."',
									address3='".$_POST['Address3']."',
									address4='".$_POST['Address4']."',
									address5='".$_POST['Address5']."',
									address6='".$_POST['Address6']."',
									currcode='".$_POST['CurrCode']."',
									salestype='".$_POST['SalesType']."',
									clientsince='".FormatDateForSQL($_POST['DateOfBirth'])."',
									holdreason='".$_POST['Sex']."'
								WHERE debtorno='".$_POST['FileNumber']."'";
	$result=DB_query($sql, $db);

	if ($_POST['ExistingInsurance']==$_POST['Insurance']) {
		if ($_POST['Insurance']=='CASH') {
			$sql = "UPDATE custbranch SET brname='".$_POST['Insurance']."',
										area='".$_POST['Area']."',
										phoneno='".$_POST['Telephone']."',
										defaultlocation='".$_SESSION['DefaultFactoryLocation']."'
									WHERE debtorno='".$_POST['FileNumber']."'
										AND branchcode='".$_POST['Insurance']."'";
		} else {
			$sql = "UPDATE custbranch SET brname='".$_POST['Insurance']."',
										area='".$_POST['Area']."',
										salesman='".$_POST['Employer']."',
										phoneno='".$_POST['Telephone']."',
										defaultlocation='".$_SESSION['DefaultFactoryLocation']."'
									WHERE debtorno='".$_POST['FileNumber']."'
										AND branchcode='".$_POST['Insurance']."'";
		}
		$result=DB_query($sql, $db);
	} else {
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
									'".$_POST['Employer']."',
									'".$_POST['Telephone']."',
									'".$_SESSION['DefaultFactoryLocation']."',
									'1'
								)";
		$result=DB_query($sql, $db);

	}
	prnMsg( _('The patient record') . ' ' . $_POST['FileNumber'] . ' ' . _('has been successfully updated'), 'success');
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Update another customer') . '</a></div>';
	unset($_POST['FileNumber']);
}

if (isset($Patient)) {

	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/PatientFile.png" title="' . _('Search') . '" alt="" />' . $title.'</p>';

	$sql="SELECT name,
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
				paymentterms,
				custbranch.phoneno,
				custbranch.area,
				custbranch.salesman
			FROM debtorsmaster
			LEFT JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			WHERE debtorsmaster.debtorno='".$Patient[0]."'
			AND branchcode='".$Patient[1]."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="FileNumber" value="'.$Patient[0].'" />';
	echo '<input type="hidden" name="CurrCode" value="'.$myrow['currcode'].'" />';
	echo '<input type="hidden" name="ExistingInsurance" value="'.$Patient[1].'" />';
	echo '<input type="hidden" name="Area" value="'.$myrow['area'].'" />';
	echo '<input type="hidden" name="Salesman" value="'.$myrow['salesman'].'" />';

	echo '<table cellpadding=3 colspan=4 class=selection>';

	echo '<tr><th colspan="2" class="header">'._('Update Patient Details') . '</th></tr>';

	echo '<tr><td>'._('File Number').':</td>';
	echo '<td>'.$Patient[0].'</td></tr>';

	echo '<tr><td>'._('Name').':</td>';
	echo '<td><input type="text" size="20" name="Name" value="'.trim($myrow['name']).'" /></td></tr>';

	echo '<tr><td>'._('Address').':</td>';
	echo '<td><input type="text" size="20" name="Address1" value="'.$myrow['address1'].'" /></td></tr>';
	echo '<td></td><td><input type="text" size="20" name="Address2" value="'.$myrow['address2'].'" /></td></tr>';
	echo '<td></td><td><input type="text" size="20" name="Address3" value="'.$myrow['address3'].'" /></td></tr>';
	echo '<td></td><td><input type="text" size="20" name="Address4" value="'.$myrow['address4'].'" /></td></tr>';
	echo '<td></td><td><input type="text" size="20" name="Address5" value="'.$myrow['address5'].'" /></td></tr>';
	echo '<td></td><td><input type="text" size="20" name="Address6" value="'.$myrow['address6'].'" /></td></tr>';

	echo '<tr><td>'._('Telephone Number').':</td>';
	echo '<td><input type="text" size="12" name="Telephone" value="'.$myrow['phoneno'].'" /></td></tr>';

	echo '<tr><td>'._('Date Of Birth').':</td>';
	echo '<td><input type="text" placeholder="'.$_SESSION['DefaultDateFormat'].'" name="DateOfBirth" maxlength="10" size="11" value="'.ConvertSQLDate($myrow['clientsince']).'" /></td></tr>';

	$TypeResult=DB_query("SELECT typeabbrev, sales_type FROM salestypes",$db);
	if (DB_num_rows($TypeResult)==0){
		$DataError =1;
		echo '<a href="SalesTypes.php?" target="_parent">Setup Types</a>';
		echo '<tr><td colspan=2>' . prnMsg(_('No sales types/price lists defined'),'error') . '</td></tr>';
	} else {
		echo '<tr><td>' . _('Price List') . ':</td>
				<td><select tabindex="9" name="SalesType">';
		echo '<option value=""></option>';

		while ($TypeRow = DB_fetch_array($TypeResult)) {
			if ($TypeRow['typeabbrev']==$myrow['salestype']) {
				echo '<option selected="selected" value="'. $TypeRow['typeabbrev'] . '">' . $TypeRow['sales_type'] . '</option>';
			} else {
				echo '<option value="'. $TypeRow['typeabbrev'] . '">' . $TypeRow['sales_type'] . '</option>';
			}
		} //end while loopre
		DB_data_seek($TypeResultesult,0);
		echo '</select></td></tr>';
	}
	$sql="SELECT reasoncode,
				reasondescription
				FROM holdreasons";
	$SexResult=DB_query($sql, $db);

	echo '<tr><td>'._('Sex').':</td>';
	echo '<td><select name="Sex">';
	echo '<option value=""></option>';
	while ($SexRow=DB_fetch_array($SexResult)) {
		if ($SexRow['reasoncode']==$myrow['holdreason']) {
			echo '<option selected="selected" value="'.$SexRow['reasoncode'].'">'.$SexRow['reasondescription'].'</option>';
		} else {
			echo '<option value="'.$SexRow['reasoncode'].'">'.$SexRow['reasondescription'].'</option>';
		}
	}
	echo '</select></td></tr>';

	$sql="SELECT debtorno,
				name
				FROM debtorsmaster
				LEFT JOIN debtortype
				ON debtorsmaster.typeid=debtortype.typeid
				WHERE debtortype.typename like '%Insurance%'";
	$InsuranceResult=DB_query($sql, $db);

	echo '<tr><td>'._('Insurance Company').':</td>';
	echo '<td><select name="Insurance">';
	echo '<option value="CASH"></option>';
	while ($InsuranceRow=DB_fetch_array($InsuranceResult)) {
		if ($InsuranceRow['debtorno']==$Patient[1]) {
			echo '<option selected="selected" value="'.$InsuranceRow['debtorno'].'">'.$InsuranceRow['name'].'</option>';
		} else {
			echo '<option value="'.$InsuranceRow['debtorno'].'">'.$InsuranceRow['name'].'</option>';
		}
	}
	echo '</select></td></tr>';
	if (isset($_POST['Insurance']) or $Patient[1]!='CASH') {
		$sql = "SELECT salesmancode,
						salesmanname
					FROM salesman";
		$EmployerResult = DB_query($sql,$db);

		echo '<tr><td>'._('Employer Company').':</td>';
		echo '<td><select name="Employer">';
		echo '<option value=""></option>';
		while ($EmployerRow=DB_fetch_array($EmployerResult)) {
			if (isset($myrow['salesman']) and ($myrow['salesman']==$EmployerRow['salesmancode'])) {
				echo '<option selected="selected" value="'.$EmployerRow['salesmancode'].'">'.$EmployerRow['salesmanname'].'</option>';
			} else {
				echo '<option value="'.$EmployerRow['salesmancode'].'">'.$EmployerRow['salesmanname'].'</option>';
			}
		}
		echo '</select></td></tr>';
	}
	echo '</table>';
	echo '<br /><div class="centre"><button type="submit" name="Update">' . _('Update Details') . '</button></div><br />';
	echo '</form>';
}

include('includes/footer.inc');
?>