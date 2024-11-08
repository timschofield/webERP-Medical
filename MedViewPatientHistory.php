<?php
include ('includes/session.php');
$Title = _('View Patient History');
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/CustomerSearch.php');

if (isset($_POST['DebtorNo'])) {
	$Patient[0] = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])) {
	$Patient[0] = stripslashes($_GET['DebtorNo']);
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
}

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

if (isset($Patient)) {
	$NameSql = "SELECT * FROM debtorsmaster
			WHERE debtorno='" . $Patient[0] . "'";
	$Result = DB_query($NameSql);
	$MyRow = DB_fetch_array($Result);
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/PatientData.png" title="' . _('Search') . '" alt="" />' . _('View History Of Patient') . ': <b>' . $Patient[0] . ' - ' . $MyRow['name'] . '</b></p>';

	$PatientHistory = array();
	$i = 0;

	/* Firstly get the stock movements */
	$SQL = "SELECT trandate,
					stockmoves.stockid,
					stocktype,
					description,
					qty
				FROM stockmoves
				INNER JOIN stockmaster
					ON stockmoves.stockid=stockmaster.stockid
				INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				WHERE debtorno='" . $Patient[0] . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$PatientHistory[$i][0] = $MyRow['stocktype'];
		$PatientHistory[$i][1] = $MyRow['trandate'];
		$PatientHistory[$i][2] = $MyRow['stockid'];
		$PatientHistory[$i][3] = $MyRow['description'];
		$PatientHistory[$i][4] = - $MyRow['qty'];
		++$i;
	}

	/* Secondly get the patient notes */
	$SQL = "SELECT noteid,
					debtorno,
					note,
					date,
					priority,
					realname
				FROM custnotes
				INNER JOIN www_users
					ON custnotes.userid=www_users.userid
				WHERE debtorno='" . $Patient[0] . "'
				ORDER BY date DESC";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		$PatientHistory[$i][0] = 'Z';
		$PatientHistory[$i][1] = $MyRow['date'];
		$PatientHistory[$i][2] = $MyRow['realname'];
		$PatientHistory[$i][3] = $MyRow['note'];
		$PatientHistory[$i][4] = $MyRow['priority'];
		++$i;
	}

	usort($PatientHistory, 'CompareTransactionDates');

	/* Show the history */
	echo '<table class="selection">';
	foreach ($PatientHistory as $Record) {
		switch ($Record[0]) {
			case 'Z':
				$Style = 'background: #90EE90';
			break;
			case 'X':
				$Style = 'background: #FDFDCD';
			break;
			case 'T':
				$Style = 'background: #BFBFBF';
			break;
			case 'P':
				$Style = 'background: #ADD8E6';
			break;
			case 'S':
				$Style = 'background: #FFE9ED';
			break;
			default:
				$Style = 'background: #ffffff';
		}
		if ($Record[0] != 'Z') {
			echo '<tr style="' . $Style . '">
					<td>' . ConvertSQLDate($Record[1]) . '</td>
					<td>' . $Record[2] . '</td>
					<td>' . $Record[3] . '</td>
					<td class="number">' . $Record[4] . '</td>
				</tr>';
		} else {
			echo '<tr style="' . $Style . '">
					<td>' . ConvertSQLDate($Record[1]) . '</td>
					<td>' . $Record[2] . '</td>
					<td colspan="2">' . $Record[3] . '</td>
				</tr>';
		}
	}
	echo '</table>';
}

function CompareTransactionDates($Array1, $Array2) {
	return ($Array1[1] > $Array2[1]);
} // sort by date


include ('includes/footer.php');

?>