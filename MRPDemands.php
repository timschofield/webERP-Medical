<?php


// Add, Edit, Delete, and List MRP demand records. Table is mrpdemands.
// Have separate functions for each routine. Use pass-by-reference - (&$StockID) -
// to pass value of $StockID to functions.

include('includes/session.php');
if (isset($_POST['Duedate'])){$_POST['Duedate'] = ConvertSQLDate($_POST['Duedate']);};
$Title = _('MRP Demands');
$ViewTopic= 'MRP';
$BookMark = 'MRP_MasterSchedule';
include('includes/header.php');

if (isset($_POST['DemandID'])){
	$DemandID =$_POST['DemandID'];
} elseif (isset($_GET['DemandID'])){
	$DemandID =$_GET['DemandID'];
}

if (isset($_POST['StockID'])){
	$StockID =trim(mb_strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID =trim(mb_strtoupper($_GET['StockID']));
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
	_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['Search'])) {
	search($StockID);
} elseif (isset($_POST['submit'])) {
	submit($StockID,$DemandID);
} elseif (isset($_GET['delete'])) {
	delete($DemandID,'',$StockID);
} elseif (isset($_POST['deletesome'])) {
	delete('',$_POST['MRPDemandtype'],$StockID);
} elseif (isset($_GET['listall'])) {
	listall('','');
} elseif (isset($_POST['listsome'])) {
	listall($StockID,$_POST['MRPDemandtype']);
} else {
	display($StockID,$DemandID);
}

function search(&$StockID) { //####SEARCH_SEARCH_SEARCH_SEARCH_SEARCH_SEARCH_SEARCH_#####

// Search by partial part number or description. Display the part number and description from
// the stockmaster so user can select one. If the user clicks on a part number
// MRPDemands.php is called again, and it goes to the display() routine.

	// Work around to auto select
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$_POST['StockCode']='%';
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$msg=_('At least one stock description keyword or an extract of a stock code must be entered for the search');
	} else {
		if (mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			$sql = "SELECT stockmaster.stockid,
						stockmaster.description
					FROM stockmaster
					WHERE  stockmaster.description " . LIKE . " '" . $SearchString ."'
					ORDER BY stockmaster.stockid";

		} elseif (mb_strlen($_POST['StockCode'])>0){
			$sql = "SELECT stockmaster.stockid,
						stockmaster.description
					FROM stockmaster
					WHERE  stockmaster.stockid " . LIKE  . "'%" . $_POST['StockCode'] . "%'
					ORDER BY stockmaster.stockid";

		}

		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$result = DB_query($sql,$ErrMsg);

	} //one of keywords or StockCode was more than a zero length string

	// If the SELECT found records, display them
	if (DB_num_rows($result) > 0) {
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
        echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table cellpadding="2" class="selection">';
		$TableHeader = '<tr><th>' . _('Code') . '</th>
							<th>' . _('Description') . '</th>
						</tr>';
		echo $TableHeader;

		$j = 1;

		while ($myrow=DB_fetch_array($result)) {
			$tabindex=$j+4;
			echo '<tr class="striped_row">
				<td><input tabindex="' . $tabindex . '" type="submit" name="StockID" value="' . $myrow['stockid'] .'" /></td>
				<td>' . $myrow['description'] . '</td>
				</tr>';
			$j++;
	}  //end of while loop

	echo '</table>';
    echo '</div>';
	echo '</form>';

} else {
	prnMsg(_('No record found in search'),'error');
	unset ($StockID);
	display($StockID,$DemandID);
}


} // End of function search()


function submit(&$StockID,&$DemandID)  //####SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
{
// In this section if hit submit button. Do edit checks. If all checks pass, see if record already
// exists for StockID/Duedate/MRPDemandtype combo; that means do an Update, otherwise, do INSERT.
//initialise no input errors assumed initially before we test
	// echo "<br/>Submit - DemandID = $DemandID<br/>";
	$FormatedDuedate = FormatDateForSQL($_POST['Duedate']);
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_numeric(filter_number_format($_POST['Quantity']))) {
		$InputError = 1;
		prnMsg(_('Quantity must be numeric'),'error');
	}
	if (filter_number_format($_POST['Quantity']) <= 0) {
		$InputError = 1;
		prnMsg(_('Quantity must be greater than 0'),'error');
	}
	if (!Is_Date($_POST['Duedate'])) {
		$InputError = 1;
		prnMsg(_('Invalid due date'),'error');
	}
	$sql = "SELECT * FROM mrpdemandtypes
			WHERE mrpdemandtype='" . $_POST['MRPDemandtype'] . "'";
	$result = DB_query($sql);

	if (DB_num_rows($result) == 0){
		$InputError = 1;
		prnMsg(_('Invalid demand type'),'error');
	}
// Check if valid part number - Had done a Select Count(*), but that returned a 1 in DB_num_rows
// even if there was no record.
	$sql = "SELECT * FROM stockmaster
			WHERE stockid='" . $StockID . "'";
	$result = DB_query($sql);

	if (DB_num_rows($result) == 0){
			$InputError = 1;
			prnMsg($StockID . ' ' . _('is not a valid item code'),'error');
			unset ($_POST['StockID']);
			unset($StockID);
	}
// Check if part number/demand type/due date combination already exists
	$sql = "SELECT * FROM mrpdemands
			WHERE stockid='" . $StockID . "'
			AND mrpdemandtype='" . $_POST['MRPDemandtype'] . "'
			AND duedate='" . $FormatedDuedate . "'
			AND demandid <> '" . $DemandID . "'";
	$result = DB_query($sql);

	if (DB_num_rows($result) > 0){
		$InputError = 1;
		prnMsg(_('Record already exists for part number/demand type/date'),'error');
	}

	if ($InputError !=1){
		$sql = "SELECT COUNT(*) FROM mrpdemands
				   WHERE demandid='" . $DemandID . "'
				   GROUP BY demandid";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);

		if ($myrow[0]>0) {
			//If $myrow[0] > 0, it means this is an edit, so do an update
			$sql = "UPDATE mrpdemands SET quantity = '" . filter_number_format($_POST['Quantity']) . "',
							mrpdemandtype = '" . trim(mb_strtoupper($_POST['MRPDemandtype'])) . "',
							duedate = '" . $FormatedDuedate . "'
					WHERE demandid = '" . $DemandID . "'";
			$msg = _("The MRP demand record has been updated for").' '.$StockID;
		} else {

	// If $myrow[0] from SELECT count(*) is zero, this is an entry of a new record
			$sql = "INSERT INTO mrpdemands (stockid,
							mrpdemandtype,
							quantity,
							duedate)
						VALUES ('" . $StockID . "',
							'" . trim(mb_strtoupper($_POST['MRPDemandtype'])) . "',
							'" . filter_number_format($_POST['Quantity']) . "',
							'" . $FormatedDuedate . "'
						)";
			$msg = _('A new MRP demand record has been added to the database for') . ' ' . $StockID;
		}


		$result = DB_query($sql,_('The update/addition of the MRP demand record failed because'));
		prnMsg($msg,'success');
		echo '<br />';
		unset ($_POST['MRPDemandtype']);
		unset ($_POST['Quantity']);
		unset ($_POST['StockID']);
		unset ($_POST['Duedate']);
		unset ($StockID);
		unset ($DemandID);
	} // End of else where DB_num_rows showed there was a valid stockmaster record

	display($StockID,$DemandID);
} // End of function submit()


function delete($DemandID,$DemandType,$StockID) { //####DELETE_DELETE_DELETE_DELETE_DELETE_DELETE_####

// If wanted to have a Confirm routine before did actually deletion, could check if
// deletion = "yes"; if it did, display link that redirects back to this page
// like this - <a href=" ' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&delete=confirm&StockID=' . "$StockID" . ' ">
// that sets delete=confirm. If delete=confirm, do actually deletion.
//  This deletes an individual record by DemandID if called from a listall that shows
// edit/delete or deletes all of a particular demand type if press Delete Demand Type button.
	$where = " ";
	if ($DemandType) {
		$where = " WHERE mrpdemandtype ='"  .  $DemandType . "'";
	}
	if ($DemandID) {
		$where = " WHERE demandid ='"  .  $DemandID . "'";
	}
	$sql="DELETE FROM mrpdemands
		   $where";
	$result = DB_query($sql);
	if ($DemandID) {
		prnMsg(_('The MRP demand record for') .' '. $StockID .' '. _('has been deleted'),'succes');
	} else {
		prnMsg(_('All records for demand type') .' '. $DemandType .' ' . _('have been deleted'),'succes');
	}
	unset ($DemandID);
	unset ($StockID);
	display($stockID,$DemandID);

} // End of function delete()


function listall($part,$DemandType)  {//####LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_####

// List all mrpdemands records, with anchors to Edit or Delete records if hit List All anchor
// Lists some in hit List Selection submit button, and uses part number if it is entered or
// demandtype

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  .'" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$where = " ";
	if ($DemandType) {
		$where = " WHERE mrpdemandtype ='"  .  $DemandType . "'";
	}
	if ($part) {
		$where = " WHERE mrpdemands.stockid ='"  .  $part . "'";
	}
	// If part is entered, it overrides demandtype
	$sql = "SELECT mrpdemands.demandid,
				   mrpdemands.stockid,
				   mrpdemands.mrpdemandtype,
				   mrpdemands.quantity,
				   mrpdemands.duedate,
				   stockmaster.description,
				   stockmaster.decimalplaces
			FROM mrpdemands
			LEFT JOIN stockmaster on mrpdemands.stockid = stockmaster.stockid" .
			 $where	. " ORDER BY mrpdemands.stockid, mrpdemands.duedate";

	$ErrMsg = _('The SQL to find the parts selected failed with the message');
	$result = DB_query($sql,$ErrMsg);

	echo '<table class="selection">
		<tr>
			<th>' . _('Part Number') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Demand Type') . '</th>
			<th>' . _('Quantity') . '</th>
			<th>' . _('Due Date') . '</th>
			</tr>';
	$ctr = 0;
	while ($myrow = DB_fetch_array($result)) {
		$displaydate = ConvertSQLDate($myrow[4]);
		$ctr++;
		echo '<tr><td>' . $myrow['stockid'] . '</td>
				<td>' . $myrow['description'] . '</td>
				<td>' . $myrow['mrpdemandtype'] . '</td>
				<td>' . locale_number_format($myrow['quantity'],$myrow['decimalplaces']) . '</td>
				<td>' . $displaydate . '</td>
				<td><a href="' .htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?DemandID=' . $myrow['demandid'] . '&amp;StockID=' . $myrow['stockid'] . '">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?DemandID=' . $myrow['demandid'] . '&amp;StockID=' . $myrow['stockid'].'&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this demand?') . '\');">' . _('Delete')  . '</a></td>
				</tr>';
	}

	//END WHILE LIST LOOP
	echo '<tr><td>' . _('Number of Records') . '</td>
				<td>' . $ctr . '</td></tr>';
	echo '</table>';
    echo '</div>';
	echo '</form><br/><br/><br/><br/>';
	unset ($StockID);
	display($StockID,$DemandID);

} // End of function listall()


function display(&$StockID,&$DemandID) { //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####

// Display Seach fields at top and Entry form below that. This function is called the first time
// the page is called, and is also invoked at the end of all of the other functions.
// echo "<br/>DISPLAY - DemandID = $DemandID<br/>";
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if (!isset($StockID)) {
		echo'<fieldset>
				<legend>', _('Select Stock Item'), '</legend>
					<field>
						<label for"Keywords">' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</label>
						<input tabindex="1" type="text" name="Keywords" size="20" maxlength="25" />
					</field>
					<b>' . _('OR') . '</b>
					<field>
						<label for="StockCode">' . _('Enter extract of the') . ' <b>' . _('Stock Code') . '</b>:</label>
						<input tabindex="2" type="text" name="StockCode" size="15" maxlength="20" />
					</field>
					<field>
						<b>' . _('OR') . '</b>
						<a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?listall=yes">' . _('List All Demands')  . '</a>
					</field>
				</fieldset>';

		echo '<div class="centre">
				<input tabindex="3" type="submit" name="Search" value="' . _('Search Now') . '" />
			</div>';
	} else {
		if (isset($DemandID)) {
		//editing an existing MRP demand

			$sql = "SELECT demandid,
					stockid,
					mrpdemandtype,
					quantity,
					duedate
				FROM mrpdemands
				WHERE demandid='" . $DemandID . "'";
			$result = DB_query($sql);
			$myrow = DB_fetch_array($result);

			if (DB_num_rows($result) > 0){
				$_POST['DemandID'] = $myrow['demandid'];
				$_POST['StockID'] = $myrow['stockid'];
				$_POST['MRPDemandtype'] = $myrow['mrpdemandtype'];
				$_POST['Quantity'] = locale_number_format($myrow['quantity'],'Variable');
				$_POST['Duedate']  = ConvertSQLDate($myrow['duedate']);
			}

			echo '<input type="hidden" name="DemandID" value="' . $_POST['DemandID'] . '" />';
			echo '<input type="hidden" name="StockID" value="' . $_POST['StockID'] . '" />';
			echo '<fieldset>
					<legend>', _('Amend MRP Demand'), '</legend>
					<field>
						<label for="StockID">' ._('Part Number') . ':</label>
						<fieldtext>' . $_POST['StockID'] . '</fieldtext>
					</field>';

		} else {
			if (!isset($_POST['StockID'])) {
				$_POST['StockID'] = '';
			}
			echo '<fieldset>
					<legend>', _('Create New MRP Demand'), '</legend>
					<field>
						<label for="StockID">' . _('Part Number') . ':</label>
						<input type="text" name="StockID" size="21" maxlength="20" value="' . $_POST['StockID'] . '" />
					</field>';
		}


		if (!isset($_POST['Quantity'])) {
			$_POST['Quantity']=0;
		}

		if (!isset($_POST['Duedate'])) {
			$_POST['Duedate']=date($_SESSION['DefaultDateFormat']);
		}

		echo '<field>
				<label for="Quantity">' . _('Quantity') . ':</label>
				<input type="text" name="Quantity" class="number" size="6" maxlength="6" value="' . $_POST['Quantity'] . '" />
			</field>
			<field>
				<label for="Duedate">' . _('Due Date') . ':</label>
				<input type="date" name="Duedate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['Duedate']) . '" />
			</field>';
		// Generate selections for Demand Type
		echo '<field>
				<label for="MRPDemandtype">' . _('Demand Type') . '</label>
				<select name="MRPDemandtype">';

		$sql = "SELECT mrpdemandtype,
						description
				FROM mrpdemandtypes";
		$result = DB_query($sql);
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_POST['MRPDemandtype']) and $myrow['mrpdemandtype']==$_POST['MRPDemandtype']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $myrow['mrpdemandtype'] . '">' . $myrow['mrpdemandtype'] . ' - ' .$myrow['description'] . '</option>';
		} //end while loop
		echo '</select>
			</field>
			</fieldset>
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Enter Information') . '" />&nbsp;&nbsp;
				<input type="submit" name="listsome" value="' . _('List Selection') . '" />&nbsp;&nbsp;
				<input type="submit" name="deletesome" value="' . _('Delete Demand Type') . '" />';
		// If mrpdemand record exists, display option to delete it
		if ((isset($DemandID)) AND (DB_num_rows($result) > 0)) {
			echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?delete=yes&amp;StockID='.$StockID.'&amp;DemandID=' . $DemandID . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this demand?') . '\');">' . _('Or Delete Record') . '</a>';
		}
		echo '</div>';
	}
	echo '</form>';

} // End of function display()

include('includes/footer.php');
?>