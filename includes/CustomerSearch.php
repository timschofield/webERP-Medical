<?php

function ShowCustomerSearchFields($rootpath, $theme, $db) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Customers').'</p>';
	echo '<table cellpadding="3" class="selection">';
	echo '<tr><td colspan="2">' . _('Enter a partial Name') . ':</td><td>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" name="Keywords" size="20" maxlength="25" />';
	}
	echo '</td><td><font size="3"><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Code') . ':</td><td>';
	if (isset($_POST['CustCode'])) {
		echo '<input type="text" name="CustCode" value="' . $_POST['CustCode'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="CustCode" size="15" maxlength="18" />';
	}
	echo '</td></tr><tr><td><font size="3"><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Phone Number') . ':</td><td>';
	if (isset($_POST['CustPhone'])) {
		echo '<input type="text" name="CustPhone" value="' . $_POST['CustPhone'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="CustPhone" size="15" maxlength="18" />';
	}
	echo '</td>';
	echo '<td><font size="3"><b>' . _('OR') . '</b></font></td><td>' . _('Enter part of the Address') . ':</td><td>';
	if (isset($_POST['CustAdd'])) {
		echo '<input type="text" name="CustAdd" value="' . $_POST['CustAdd'] . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" name="CustAdd" size="20" maxlength="25" />';
	}
	echo '</td></tr>';
	/* End addded search feature. Gilles Deacur */
	echo '<tr><td><font size="3"><b>' . _('OR') . '</b></font></td><td>' . _('Choose a Type') . ':</td><td>';
	if (isset($_POST['CustType'])) {
		// Show Customer Type drop down list
		$result2 = DB_query("SELECT typeid, typename FROM debtortype", $db);
		// Error if no customer types setup
		if (DB_num_rows($result2) == 0) {
			$DataError = 1;
			echo '<a href="CustomerTypes.php?" target="_parent">Setup Types</a>';
			echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
		} else {
			// If OK show select box with option selected
			echo '<select name="CustType">';
			echo '<option value="ALL">' . _('Any') . '</option>';
			while ($myrow = DB_fetch_array($result2)) {
				if ($_POST['CustType'] == $myrow['typename']) {
					echo '<option selected="True" value="' . $myrow['typename'] . '">' . $myrow['typename']  . '</option>';
				} else {
					echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename']  . '</option>';
				}
			} //end while loop
			DB_data_seek($result2, 0);
			echo '</select></td>';
		}
	} else {
		// No option selected yet, so show Customer Type drop down list
		$result2 = DB_query("SELECT typeid, typename FROM debtortype", $db);
		// Error if no customer types setup
		if (DB_num_rows($result2) == 0) {
			$DataError = 1;
			echo '<a href="CustomerTypes.php?" target="_parent">Setup Types</a>';
			echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
		} else {
			// if OK show select box with available options to choose
			echo '<select name="CustType">';
			echo '<option value="ALL">' . _('Any'). '</option>';
			while ($myrow = DB_fetch_array($result2)) {
				echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
			} //end while loop
			DB_data_seek($result2, 0);
			echo '</select></td>';
		}
	}

	/* Option to select a sales area */
	echo '<td><font size="3"><b>' . _('OR') . '</b></font></td><td>' . _('Choose an Area') . ':</td><td>';
	$result2 = DB_query("SELECT areacode, areadescription FROM areas", $db);
	// Error if no sales areas setup
	if (DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="Areas.php?" target="_parent">Setup Types</a>';
		echo '<tr><td colspan="2">' . prnMsg(_('No Sales Areas defined'), 'error') . '</td></tr>';
	} else {
		// if OK show select box with available options to choose
		echo '<select name="Area">';
		echo '<option value="ALL">' . _('Any') . '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			if (isset($_POST['Area']) and $_POST['Area']==$myrow['areacode']) {
				echo '<option selected="True" value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
			} else {
				echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
			}
		} //end while loop
		DB_data_seek($result2, 0);
		echo '</select></td></tr>';
	}

	echo '</td></tr></table><br />';
	echo '<div class="centre"><button type="submit" name="Search">' . _('Search Now') . '</button></div><br />';
	if (isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin'] != '') {
		prnMsg(_('Your account enables you to see only customers allocated to you'), 'warn', _('Note: Sales-person Login'));
	}
}

function CustomerSearchSQL($db) {
	if (isset($_POST['Search']) OR isset($_POST['Go1']) OR isset($_POST['Go2']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
		if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
			$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
			$_POST['Go'] = '';
		}
		if (!isset($_POST['PageOffset'])) {
			$_POST['PageOffset'] = 1;
		}
		if ($_POST['Keywords'] AND (($_POST['CustCode']) OR ($_POST['CustPhone']) OR ($_POST['CustType']))) {
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		}
		if (($_POST['Keywords'] == '') AND ($_POST['CustCode'] == '') AND ($_POST['CustPhone'] == '') AND (		$_POST['CustType'] == 'ALL') AND ($_POST['Area'] == 'ALL') AND ($_POST['CustAdd'] == '')) {
			//no criteria set then default to all customers
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
						FROM debtorsmaster
						LEFT JOIN custbranch
							ON debtorsmaster.debtorno = custbranch.debtorno
						INNER JOIN debtortype
							ON debtorsmaster.typeid = debtortype.typeid";
			$CountSQL = "SELECT COUNT(debtorsmaster.debtorno) as totaldebtors
							FROM debtorsmaster
							LEFT JOIN custbranch
								ON debtorsmaster.debtorno = custbranch.debtorno
							INNER JOIN debtortype
								ON debtorsmaster.typeid = debtortype.typeid";

		} else {
			$SearchKeywords = mb_strtoupper(trim(str_replace(' ', '%', $_POST['Keywords'])));
			$_POST['CustCode'] = mb_strtoupper(trim($_POST['CustCode']));
			$_POST['CustPhone'] = trim($_POST['CustPhone']);
			$_POST['CustAdd'] = trim($_POST['CustAdd']);
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
						FROM debtorsmaster
						INNER JOIN debtortype
							ON debtorsmaster.typeid = debtortype.typeid
						INNER JOIN custbranch
							ON debtorsmaster.debtorno = custbranch.debtorno
						WHERE debtorsmaster.name " . LIKE . " '%" . $SearchKeywords . "%'
							AND debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
							AND custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
							AND (debtorsmaster.address1 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address2 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address3 "  . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address4 "  . LIKE . " '%" . $_POST['CustAdd'] . "%')";

			$CountSQL = "SELECT COUNT(debtorsmaster.debtorno) as totaldebtors
						FROM debtorsmaster
						INNER JOIN debtortype
							ON debtorsmaster.typeid = debtortype.typeid
						INNER JOIN custbranch
							ON debtorsmaster.debtorno = custbranch.debtorno
						WHERE debtorsmaster.name " . LIKE . " '%" . $SearchKeywords . "%'
							AND debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
							AND custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
							AND (debtorsmaster.address1 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address2 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address3 "  . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address4 "  . LIKE . " '%" . $_POST['CustAdd'] . "%')";

			if (mb_strlen($_POST['CustType']) > 0 AND $_POST['CustType']!='ALL') {
				$SQL .= " AND debtortype.typename = '" . $_POST['CustType'] . "'";
			}
			if (mb_strlen($_POST['Area']) > 0 AND $_POST['Area']!='ALL') {
				$SQL .= " AND custbranch.area = '" . $_POST['Area'] . "'";
			}
		} //one of keywords or custcode or custphone was more than a zero length string
		if ($_SESSION['SalesmanLogin'] != '') {
			$SQL.= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
		}
		$CountResult=DB_query($CountSQL, $db);
		$CountRow=DB_fetch_array($CountResult);
		$_SESSION['ListCount']=$CountRow['totaldebtors'];
		$_SESSION['ListPageMax'] = ceil($_SESSION['ListCount'] / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $_SESSION['ListPageMax']) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		$SQL.= " ORDER BY debtorsmaster.name";
		$SQL.= " LIMIT ".(($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']).", ".$_SESSION['DisplayRecordsMax'];
		$ErrMsg = _('The searched customer records requested cannot be retrieved because');

		$result = DB_query($SQL, $db, $ErrMsg);
		if (DB_num_rows($result) == 0) {
			prnMsg(_('No customer records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
			echo '<br />';
		}
		return $result;
	} //end of if search
}

function ShowReturnedCustomers($result) {
	unset($_SESSION['CustomerID']);
	echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
	if ($_SESSION['ListPageMax'] > 1) {
		echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $_SESSION['ListPageMax'] . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset1">';
		$ListPage = 1;
		while ($ListPage <= $_SESSION['ListPageMax']) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
			} else {
				echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<button type="submit" name="Go1">' . _('Go') . '</button>
			<button type="submit" name="Previous">' . _('Previous') . '</button>
			<button type="submit" name="Next">' . _('Next') . '</button>';
		echo '</div>';
	}
	echo '<br /><table cellpadding="2" class="selection">';
	$TableHeader = '<tr>
			<th>' . _('Code') . '</th>
			<th>' . _('Customer Name') . '</th>
			<th>' . _('Branch') . '</th>
			<th>' . _('Contact') . '</th>
			<th>' . _('Type') . '</th>
			<th>' . _('Phone') . '</th>
			<th>' . _('Fax') . '</th>
		</tr>';
	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	if (DB_num_rows($result) <> 0) {
		$i=0; //counter for input controls
		while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '<td><button type="submit" name="SubmitCustomerSelection' . $i .'" value="' . htmlentities($myrow['debtorno'].' '.$myrow['branchcode'],ENT_QUOTES,'UTF-8') . '">' . htmlentities($myrow['debtorno'].' '.$myrow['branchcode'],ENT_QUOTES,'UTF-8') . '</button></td>
				<input type="hidden" name="SelectedCustomer' . $i .'" value="'.$myrow['debtorno'].'" />
				<input type="hidden" name="SelectedBranch' . $i .'" value="'. $myrow['branchcode'].'" />
				<td><font size="1">' . $myrow['name'] . '</font></td>
				<td><font size="1">' . $myrow['brname'] . '</font></td>
				<td><font size="1">' . $myrow['contactname'] . '</font></td>
				<td><font size="1">' . $myrow['typename'] . '</font></td>
				<td><font size="1">' . $myrow['phoneno'] . '</font></td>
				<td><font size="1">' . $myrow['faxno'] . '</font></td></tr>';
			$i++;
			$j++;//row counter
			//end of page full new headings if

		}
		//end of while loop
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	}

	//end if results to show
	if (isset($_SESSION['ListPageMax']) and $_SESSION['ListPageMax'] > 1) {
		echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $_SESSION['ListPageMax'] . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset2">';
		$ListPage = 1;
		while ($ListPage <= $_SESSION['ListPageMax']) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
			} else {
				echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<button type="submit" name="Go2">' . _('Go') . '</button>
			<button type="submit" name="Previous">' . _('Previous') . '</button>
			<button type="submit" name="Next">' . _('Next') . '</button>';
	}
	//end if results to show
	echo '</div></form>';
	unset($_SESSION['ListCount']);
	unset($_SESSION['ListPageMax']);
}

?>