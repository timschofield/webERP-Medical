<?php
$PageSecurity=1;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
$title = _('Update Patient Details');
include('includes/header.inc');

if (isset($_GET['PatientNumber'])) {
	$_POST['FileNumber']=$_GET['PatientNumber'].' '.$_GET['BranchCode'];
}

if (isset($_POST['Search']) OR isset($_POST['CSV']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] AND (($_POST['CustCode']) OR ($_POST['CustPhone']))) {
		$msg = _('Search Result: Customer Name has been used in search') . '<br>';
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
	}
	if ($_POST['CustCode'] AND $_POST['CustPhone'] == "" AND isset($_POST['CustType']) AND $_POST['Keywords'] == "") {
		$msg = _('Search Result: Customer Code has been used in search') . '<br>';
	}
	if (($_POST['CustPhone'])) {
		$msg = _('Search Result: Customer Phone has been used in search') . '<br>';
	}
	if (($_POST['CustAdd'])) {
		$msg = _('Search Result: Customer Address has been used in search') . '<br>';
	}
	if ($_POST['CustPhone'] == "" AND $_POST['CustCode'] == "" AND $_POST['Keywords'] == "" AND $_POST['CustAdd'] == "") {
		$msg = _('Search Result: Customer Type has been used in search') . '<br>';
	}
	if (($_POST['Keywords'] == "") AND ($_POST['CustCode'] == "") AND ($_POST['CustPhone'] == "") AND ($_POST['CustAdd'] == "")) {
		$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.typeid = debtortype.typeid";
	} else {
		if (strlen($_POST['Keywords']) > 0) {
			//using the customer name
			$_POST['Keywords'] = strtoupper(trim($_POST['Keywords']));
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.name " . LIKE . " '$SearchString'
			AND debtorsmaster.typeid = debtortype.typeid";
		} elseif (strlen($_POST['CustCode']) > 0) {
			$_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
			AND debtorsmaster.typeid = debtortype.typeid";
		} elseif (strlen($_POST['CustPhone']) > 0) {
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
			AND debtorsmaster.typeid = debtortype.typeid";
			// Added an option to search by address. I tried having it search address1, address2, address3, and address4, but my knowledge of MYSQL is limited.  This will work okay if you select the CSV Format then you can search though the address1 field. I would like to extend this to all 4 address fields. Gilles Deacur

		} elseif (strlen($_POST['CustAdd']) > 0) {
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE CONCAT_WS(debtorsmaster.address1,debtorsmaster.address2,debtorsmaster.address3,debtorsmaster.address4) " . LIKE . " '%" . $_POST['CustAdd'] . "%'
			AND debtorsmaster.typeid = debtortype.typeid";
			// End added search feature. Gilles Deacur

		}
	} //one of keywords or custcode or custphone was more than a zero length string
	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL.= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$SQL.= ' ORDER BY debtorsmaster.name';
	$ErrMsg = _('The searched patient records requested cannot be retrieved because');

	$PatientResult = DB_query($SQL, $db, $ErrMsg);
	if (DB_num_rows($PatientResult) == 0) {
		prnMsg(_('No patient records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	}
} //end of if search

if (!isset($_POST['FileNumber'])) {
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Patients').'</p>';
	echo '<table cellpadding=3 colspan=4 class=selection>';
	echo '<tr><td colspan=2>' . _('Enter a partial Name') . ':</td><td>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="Text" name="Keywords" value="' . $_POST['Keywords'] . '" size=20 maxlength=25>';
	} else {
		echo '<input type="Text" name="Keywords" size=20 maxlength=25>';
	}
	echo '</td><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Code') . ':</td><td>';
	if (isset($_POST['CustCode'])) {
		echo '<input type="Text" name="CustCode" value="' . $_POST['CustCode'] . '" size=15 maxlength=18>';
	} else {
		echo '<input type="Text" name="CustCode" size=15 maxlength=18>';
	}
	echo '</td></tr><tr><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Phone Number') . ':</td><td>';
	if (isset($_POST['CustPhone'])) {
		echo '<input type="Text" name="CustPhone" value="' . $_POST['CustPhone'] . '" size=15 maxlength=18>';
	} else {
		echo '<input type="Text" name="CustPhone" size=15 maxlength=18>';
	}
	echo '</td>';
	echo '<td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter part of the Address') . ':</td><td>';
	if (isset($_POST['CustAdd'])) {
		echo '<input type="Text" name="CustAdd" value="' . $_POST['CustAdd'] . '" size=20 maxlength=25>';
	} else {
		echo '<input type="Text" name="CustAdd" size=20 maxlength=25>';
	}
	echo '</td></tr>';

	echo '</td></tr></table><br />';
	echo '<div class="centre"><button type=submit name="Search">' . _('Search Now') . '</button></div></form>';
}

if (isset($PatientResult)) {
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	unset($_SESSION['CustomerID']);
	$ListCount = DB_num_rows($PatientResult);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
	if ($ListPageMax > 1) {
		echo '<p><div class=centre>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset1">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
			} else {
				echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
				<input type=submit name="Go1" value="' . _('Go') . '">
				<input type=submit name="Previous" value="' . _('Previous') . '">
				<input type=submit name="Next" value="' . _('Next') . '">';
		echo '</div>';
	}
	echo '<br /><table cellpadding=2 colspan=7 class=selection>';
	$TableHeader = '<tr>
				<th>' . _('Code') . '</th>
				<th>' . _('Patient Name') . '</th>
				<th>' . _('Phone') . '</th>
			</tr>';
	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	if (DB_num_rows($PatientResult) <> 0) {
		if (!isset($_POST['CSV'])) {
			DB_data_seek($PatientResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($myrow = DB_fetch_array($PatientResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '<td><font size=1><input type=submit name="FileNumber" value="' . $myrow['debtorno'].' '.$myrow['branchcode'] . '"></font></td>
				<td><font size=1>' . $myrow['name'] . '</font></td>
				<td><font size=1>' . $myrow['phoneno'] . '</font></td></tr>';
			$j++;
			if ($j == 11 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $TableHeader;
			}
			$RowIndex++;
			//end of page full new headings if

		}
		//end of while loop
		echo '</table></form>';
	}
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
		$sql = "UPDATE custbranch SET brname='".$_POST['Insurance']."',
									area='".$_POST['Area']."',
									salesman='".$_POST['Employer']."',
									phoneno='".$_POST['Telephone']."',
									defaultlocation='".$_SESSION['DefaultFactoryLocation']."'
								WHERE debtorno='".$_POST['FileNumber']."'
								AND branchcode='".$_POST['Insurance']."'";
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

if (isset($_POST['FileNumber'])) {

	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/PatientFile.png" title="' . _('Search') . '" alt="" />' . $title.'</p>';
	$Patient=explode(' ', $_POST['FileNumber']);

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
	echo '<td><input type="text" size="20" name="Name" value="'.$myrow['name'].'" /></td></tr>';

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
				WHERE debtortype.typename='Insurance'";
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
						salesmanname,
						smantel,
						smanfax
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