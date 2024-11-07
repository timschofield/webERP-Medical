<?php
include ('includes/DefineTenderClass.php');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/session.php');
if (isset($_POST['RequiredByDate'])){$_POST['RequiredByDate'] = ConvertSQLDate($_POST['RequiredByDate']);};

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other supplier tender sessions on the same machine  */
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}

if (isset($_GET['New']) and isset($_SESSION['tender' . $identifier])) {
	unset($_SESSION['tender' . $identifier]);
}

if (isset($_GET['New']) and $_SESSION['CanCreateTender'] == 0) {
	$Title = _('Authorisation Problem');
	include ('includes/header.php');
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . $Title . '" alt="" />  ' . $Title . '</p>';
	prnMsg(_('You do not have authority to create supplier tenders for this company.') . '<br />' . _('Please see your system administrator'), 'warn');
	include ('includes/footer.php');
	exit;
}

if (isset($_GET['Edit']) and $_SESSION['CanCreateTender'] == 0) {
	$Title = _('Authorisation Problem');
	include ('includes/header.php');
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . $Title . '" alt="" />  ' . $Title . '</p>';
	prnMsg(_('You do not have authority to amend supplier tenders for this company.') . '<br />' . _('Please see your system administrator'), 'warn');
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['Close'])) {
	$SQL = "UPDATE tenders SET closed=1 WHERE tenderid='" . $_SESSION['tender' . $identifier]->TenderId . "'";
	$Result = DB_query($SQL);
	$_GET['Edit'] = 'Yes';
	unset($_SESSION['tender' . $identifier]);
}

$ShowTender = 0;

if (isset($_GET['ID'])) {
	$SQL = "SELECT tenderid,
					location,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					telephone,
					requiredbydate
				FROM tenders
				INNER JOIN locationusers ON locationusers.loccode=tenders.location AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE tenderid='" . $_GET['ID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if (isset($_SESSION['tender' . $identifier])) {
		unset($_SESSION['tender' . $identifier]);
	}
	$_SESSION['tender' . $identifier] = new Tender();
	$_SESSION['tender' . $identifier]->TenderId = $MyRow['tenderid'];
	$_SESSION['tender' . $identifier]->Location = $MyRow['location'];
	$_SESSION['tender' . $identifier]->DelAdd1 = $MyRow['address1'];
	$_SESSION['tender' . $identifier]->DelAdd2 = $MyRow['address2'];
	$_SESSION['tender' . $identifier]->DelAdd3 = $MyRow['address3'];
	$_SESSION['tender' . $identifier]->DelAdd4 = $MyRow['address4'];
	$_SESSION['tender' . $identifier]->DelAdd5 = $MyRow['address5'];
	$_SESSION['tender' . $identifier]->DelAdd6 = $MyRow['address6'];
	$_SESSION['tender' . $identifier]->RequiredByDate = FormatDateForSQL(ConvertSQLDate($MyRow['requiredbydate']));

	$SQL = "SELECT tenderid,
					tendersuppliers.supplierid,
					suppliers.suppname,
					tendersuppliers.email
				FROM tendersuppliers
				LEFT JOIN suppliers
					ON tendersuppliers.supplierid=suppliers.supplierid
				WHERE tenderid='" . $_GET['ID'] . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$_SESSION['tender' . $identifier]->add_supplier_to_tender($MyRow['supplierid'], $MyRow['suppname'], $MyRow['email']);
	}

	$SQL = "SELECT tenderid,
					tenderitems.stockid,
					tenderitems.quantity,
					stockmaster.description,
					tenderitems.units,
					stockmaster.decimalplaces
				FROM tenderitems
				LEFT JOIN stockmaster
					ON tenderitems.stockid=stockmaster.stockid
				WHERE tenderid='" . $_GET['ID'] . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$_SESSION['tender' . $identifier]->add_item_to_tender($_SESSION['tender' . $identifier]->LinesOnTender, $MyRow['stockid'], $MyRow['quantity'], $MyRow['description'], $MyRow['units'], $MyRow['decimalplaces'], DateAdd(date($_SESSION['DefaultDateFormat']), 'm', 3));
	}
	$ShowTender = 1;
}

if (isset($_GET['Edit'])) {
	$Title = _('Edit an Existing Supplier Tender Request');
	$ViewTopic = 'SupplierTenders';
	$BookMark = 'EditTender';
	include ('includes/header.php');
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Purchase Order Tendering') . '" alt="" />  ' . $Title . '</p>';
	$SQL = "SELECT tenderid,
					location,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					telephone
				FROM tenders
				INNER JOIN locationusers ON locationusers.loccode=tenders.location AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1
				WHERE closed=0";
	$Result = DB_query($SQL);
	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Tender ID') . '</th>
			<th>' . _('Location') . '</th>
			<th>' . _('Address 1') . '</th>
			<th>' . _('Address 2') . '</th>
			<th>' . _('Address 3') . '</th>
			<th>' . _('Address 4') . '</th>
			<th>' . _('Address 5') . '</th>
			<th>' . _('Address 6') . '</th>
			<th>' . _('Telephone') . '</th>
		</tr>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $MyRow['tenderid'] . '</td>
				<td>' . $MyRow['location'] . '</td>
				<td>' . $MyRow['address1'] . '</td>
				<td>' . $MyRow['address2'] . '</td>
				<td>' . $MyRow['address3'] . '</td>
				<td>' . $MyRow['address4'] . '</td>
				<td>' . $MyRow['address5'] . '</td>
				<td>' . $MyRow['address6'] . '</td>
				<td>' . $MyRow['telephone'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '&amp;ID=' . $MyRow['tenderid'] . '">' . _('Edit') . '</a></td>
			</tr>';
	}
	echo '</table>';
	include ('includes/footer.php');
	exit;
} else if (isset($_GET['ID']) or (isset($_SESSION['tender' . $identifier]->TenderId))) {
	$Title = _('Edit an Existing Supplier Tender Request');
	$ViewTopic = 'SupplierTenders';
	$BookMark = 'EditTender';
	include ('includes/header.php');
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Purchase Order Tendering') . '" alt="" />' . $Title . '</p>';
} else {
	$Title = _('Create a New Supplier Tender Request');
	$ViewTopic = 'SupplierTenders';
	$BookMark = 'CreateTender';
	include ('includes/header.php');
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Purchase Order Tendering') . '" alt="" />' . $Title . '</p>';
}

if (isset($_POST['Save'])) {
	$_SESSION['tender' . $identifier]->RequiredByDate = $_POST['RequiredByDate'];
	$_SESSION['tender' . $identifier]->save();
	$_SESSION['tender' . $identifier]->EmailSuppliers();
	prnMsg(_('The tender has been successfully saved'), 'success');
	include ('includes/footer.php');
	exit;
}

if (isset($_GET['DeleteSupplier'])) {
	$_SESSION['tender' . $identifier]->remove_supplier_from_tender($_GET['DeleteSupplier']);
	$ShowTender = 1;
}

if (isset($_GET['DeleteItem'])) {
	$_SESSION['tender' . $identifier]->remove_item_from_tender($_GET['DeleteItem']);
	$ShowTender = 1;
}

if (isset($_POST['SelectedSupplier'])) {
	$SQL = "SELECT suppname,
					email
				FROM suppliers
				WHERE supplierid='" . $_POST['SelectedSupplier'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if (mb_strlen($MyRow['email']) > 0) {
		$_SESSION['tender' . $identifier]->add_supplier_to_tender($_POST['SelectedSupplier'], $MyRow['suppname'], $MyRow['email']);
	} else {
		prnMsg(_('The supplier must have an email set up or they cannot be part of a tender'), 'warn');
	}
	$ShowTender = 1;
}

if (isset($_POST['NewItem']) and !isset($_POST['Refresh'])) {
	foreach ($_POST as $key => $value) {
		if (mb_substr($key, 0, 7) == 'StockID') {
			$Index = mb_substr($key, 7, mb_strlen($key) - 7);
			$StockID = $value;
			$Quantity = filter_number_format($_POST['Qty' . $Index]);
			$UOM = $_POST['UOM' . $Index];
			$SQL = "SELECT description,
							decimalplaces
						FROM stockmaster
						WHERE stockid='" . $StockID . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$_SESSION['tender' . $identifier]->add_item_to_tender($_SESSION['tender' . $identifier]->LinesOnTender, $StockID, $Quantity, $MyRow['description'], $UOM, $MyRow['decimalplaces'], DateAdd(date($_SESSION['DefaultDateFormat']), 'm', 3));
			unset($UOM);
		}
	}
	$ShowTender = 1;
}

if (!isset($_SESSION['tender' . $identifier]) or isset($_POST['LookupDeliveryAddress']) or $ShowTender == 1) {

	/* Show Tender header screen */
	if (!isset($_SESSION['tender' . $identifier])) {
		$_SESSION['tender' . $identifier] = new Tender();
	}
	if (!isset($_SESSION['tender' . $identifier]->RequiredByDate)) {
		$_SESSION['tender' . $identifier]->RequiredByDate = FormatDateForSQL(date($_SESSION['DefaultDateFormat']));
	}
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post" class="noPrint">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>';
	echo '<legend>' . _('Tender header details') . '</legend>
		<field>
			<label for="RequiredByDate">' . _('Delivery Must Be Made Before') . '</label>
			<input type="date" required="required" name="RequiredByDate" autofocus="autofocus" size="11" value="' . $_SESSION['tender' . $identifier]->RequiredByDate . '" />
		</field>';

	if (!isset($_POST['StkLocation']) or $_POST['StkLocation'] == '') {
		/* If this is the first time
		 * the form loaded set up defaults */

		$_POST['StkLocation'] = $_SESSION['UserStockLocation'];

		$SQL = "SELECT deladd1,
						deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6,
						tel,
						contact
					FROM locations
					INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1
					WHERE locations.loccode='" . $_POST['StkLocation'] . "'";

		$LocnAddrResult = DB_query($SQL);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_array($LocnAddrResult);
			$_POST['DelAdd1'] = $LocnRow['deladd1'];
			$_POST['DelAdd2'] = $LocnRow['deladd2'];
			$_POST['DelAdd3'] = $LocnRow['deladd3'];
			$_POST['DelAdd4'] = $LocnRow['deladd4'];
			$_POST['DelAdd5'] = $LocnRow['deladd5'];
			$_POST['DelAdd6'] = $LocnRow['deladd6'];
			$_POST['Tel'] = $LocnRow['tel'];
			$_POST['Contact'] = $LocnRow['contact'];

			$_SESSION['tender' . $identifier]->Location = $_POST['StkLocation'];
			$_SESSION['tender' . $identifier]->DelAdd1 = $_POST['DelAdd1'];
			$_SESSION['tender' . $identifier]->DelAdd2 = $_POST['DelAdd2'];
			$_SESSION['tender' . $identifier]->DelAdd3 = $_POST['DelAdd3'];
			$_SESSION['tender' . $identifier]->DelAdd4 = $_POST['DelAdd4'];
			$_SESSION['tender' . $identifier]->DelAdd5 = $_POST['DelAdd5'];
			$_SESSION['tender' . $identifier]->DelAdd6 = $_POST['DelAdd6'];
			$_SESSION['tender' . $identifier]->Telephone = $_POST['Tel'];
			$_SESSION['tender' . $identifier]->Contact = $_POST['Contact'];

		} else {
			/*The default location of the user is crook */
			prnMsg(_('The default stock location set up for this user is not a currently defined stock location') . '. ' . _('Your system administrator needs to amend your user record'), 'error');
		}

	} elseif (isset($_POST['LookupDeliveryAddress'])) {

		$SQL = "SELECT deladd1,
						deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6,
						tel,
						contact
					FROM locations
					INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1
					WHERE locations.loccode='" . $_POST['StkLocation'] . "'";

		$LocnAddrResult = DB_query($SQL);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_array($LocnAddrResult);
			$_POST['DelAdd1'] = $LocnRow['deladd1'];
			$_POST['DelAdd2'] = $LocnRow['deladd2'];
			$_POST['DelAdd3'] = $LocnRow['deladd3'];
			$_POST['DelAdd4'] = $LocnRow['deladd4'];
			$_POST['DelAdd5'] = $LocnRow['deladd5'];
			$_POST['DelAdd6'] = $LocnRow['deladd6'];
			$_POST['Tel'] = $LocnRow['tel'];
			$_POST['Contact'] = $LocnRow['contact'];

			$_SESSION['tender' . $identifier]->Location = $_POST['StkLocation'];
			$_SESSION['tender' . $identifier]->DelAdd1 = $_POST['DelAdd1'];
			$_SESSION['tender' . $identifier]->DelAdd2 = $_POST['DelAdd2'];
			$_SESSION['tender' . $identifier]->DelAdd3 = $_POST['DelAdd3'];
			$_SESSION['tender' . $identifier]->DelAdd4 = $_POST['DelAdd4'];
			$_SESSION['tender' . $identifier]->DelAdd5 = $_POST['DelAdd5'];
			$_SESSION['tender' . $identifier]->DelAdd6 = $_POST['DelAdd6'];
			$_SESSION['tender' . $identifier]->Telephone = $_POST['Tel'];
			$_SESSION['tender' . $identifier]->Contact = $_POST['Contact'];
		}
	}
	echo '<field>
			<label for="StkLocation">' . _('Warehouse') . ':</label>
			<select name="StkLocation" onchange="ReloadForm(form1.LookupDeliveryAddress)">';

	$SQL = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1";
	$LocnResult = DB_query($SQL);

	while ($LocnRow = DB_fetch_array($LocnResult)) {
		if ((isset($_SESSION['tender' . $identifier]->Location) and $_SESSION['tender' . $identifier]->Location == $LocnRow['loccode'])) {
			echo '<option selected="selected" value="' . $LocnRow['loccode'] . '">' . $LocnRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $LocnRow['loccode'] . '">' . $LocnRow['locationname'] . '</option>';
		}
	}

	echo '</select>
		<input type="submit" name="LookupDeliveryAddress" value="' . _('Select') . '" />
	</field>';

	/* Display the details of the delivery location
	*/
	echo '<field>
			<label for="Contact">' . _('Delivery Contact') . ':</label>
			<input type="text" name="Contact" size="41"  value="' . $_SESSION['tender' . $identifier]->Contact . '" readonly />
		</field>';
	echo '<field>
			<label for="DelAdd1">' . _('Address') . ' 1 :</label>
			<input type="text" name="DelAdd1" pattern=".{1,40}" title="" size="41" maxlength="40" value="' . $_POST['DelAdd1'] . '" />
			<fieldhelp>' . _('The address should not be over 40 characters') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="DelAdd2">' . _('Address') . ' 2 :</label>
			<input type="text" name="DelAdd2" pattern=".{1,40}" title="" size="41" size="41" maxlength="40" value="' . $_POST['DelAdd2'] . '" />
			<fieldhelp>' . _('The address should not be over 40 characters') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="DelAdd3">' . _('Address') . ' 3 :</label>
			<input type="text" name="DelAdd3" pattern=".{1,40}" title="" size="41" size="41" maxlength="40" value="' . $_POST['DelAdd3'] . '" />
			<fieldhelp>' . _('The address should not be over 40 characters') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="DelAdd4">' . _('Address') . ' 4 :</label>
			<input type="text" name="DelAdd4" pattern=".{1,40}" title=""  size="41" maxlength="40" value="' . $_POST['DelAdd4'] . '" />
			<fieldhelp>' . _('The characters should not be over 20 characters') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="DelAdd5">' . _('Address') . ' 5 :</label>
			<input type="text" name="DelAdd5" pattern=".{1,20}" title="" size="21" maxlength="20" value="' . $_POST['DelAdd5'] . '" />
			<fieldhelp>' . _('The characters should not be over 20 characters') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="DelAdd6">' . _('Address') . ' 6 :</label>
			<input type="text" name="DelAdd6" pattern=".{1,15}" title=""  size="16" maxlength="15" value="' . $_POST['DelAdd6'] . '" />
			<fieldhelp>' . _('The characters should not be over 15 characters') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="Tel">' . _('Phone') . ':</label>
			<input type="tel" name="Tel" pattern="[\d+)(\s]{1,25}" size="31" title="" maxlength="25" value="' . $_SESSION['tender' . $identifier]->Telephone . '" />
			<fieldhelp>' . _('The input should be telephone number and should not be over 25 charaters') . '</fieldhelp>
		</field>';
	echo '</fieldset>';

	/* Display the supplier/item details
	*/
	echo '<table>';

	/* Supplier Details
	*/
	echo '<tr>
			<td valign="top">
			<table class="selection">';
	echo '<tr>
			<th colspan="4"><h3>' . _('Suppliers To Send Tender') . '</h3></th>
		</tr>';
	echo '<tr>
			<th>' . _('Supplier Code') . '</th>
			<th>' . _('Supplier Name') . '</th>
			<th>' . _('Email Address') . '</th>
		</tr>';
	foreach ($_SESSION['tender' . $identifier]->Suppliers as $Supplier) {
		echo '<tr>
				<td>' . $Supplier->SupplierCode . '</td>
				<td>' . $Supplier->SupplierName . '</td>
				<td>' . $Supplier->EmailAddress . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?identifier=' . $identifier, ENT_QUOTES, 'UTF-8') . '&amp;DeleteSupplier=' . $Supplier->SupplierCode . '">' . _('Delete') . '</a></td>
			</tr>';
	}
	echo '</table></td>';
	/* Item Details
	*/
	echo '<td valign="top"><table class="selection">
		<thead>
			<tr>
			<th colspan="6"><h3>' . _('Items in Tender') . '</h3></th>
		</tr>
		<tr>
			<th class="ascending">' . _('Stock ID') . '</th>
			<th class="ascending">' . _('Description') . '</th>
			<th class="ascending">' . _('Quantity') . '</th>
			<th>' . _('UOM') . '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($_SESSION['tender' . $identifier]->LineItems as $LineItems) {
		if ($LineItems->Deleted == False) {
			echo '<tr class="striped_row">
					<td>' . $LineItems->StockID . '</td>
					<td>' . $LineItems->ItemDescription . '</td>
					<td class="number">' . locale_number_format($LineItems->Quantity, $LineItems->DecimalPlaces) . '</td>
					<td>' . $LineItems->Units . '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?identifier=' . $identifier, ENT_QUOTES, 'UTF-8') . '&amp;DeleteItem=' . $LineItems->LineNo . '">' . _('Delete') . '</a></td>
				</tr>';
		}
	}
	echo '</tbody></table>
		</td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="Suppliers" value="' . _('Select Suppliers') . '" />
			<input type="submit" name="Items" value="' . _('Select Item Details') . '" />';

	if ($_SESSION['tender' . $identifier]->LinesOnTender > 0 and $_SESSION['tender' . $identifier]->SuppliersOnTender > 0) {
		echo '<input type="submit" name="Close" value="' . _('Close This Tender') . '" />';
	}
	echo '</div>
		<br />';
	if ($_SESSION['tender' . $identifier]->LinesOnTender > 0 and $_SESSION['tender' . $identifier]->SuppliersOnTender > 0) {

		echo '<div class="centre">
				<input type="submit" name="Save" value="' . _('Save Tender') . '" />
			</div>';
	}
	echo '</div>
		</form>';
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['SearchSupplier']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {

	if (mb_strlen($_POST['Keywords']) > 0 and mb_strlen($_POST['SupplierCode']) > 0) {
		prnMsg('<br />' . _('Supplier name keywords have been used in preference to the Supplier code extract entered'), 'info');
	}
	if ($_POST['Keywords'] == '' and $_POST['SupplierCode'] == '') {
		$SQL = "SELECT supplierid,
						suppname,
						currcode,
						address1,
						address2,
						address3,
						address4
					FROM suppliers
					WHERE email<>''
					ORDER BY suppname";
	} else {
		if (mb_strlen($_POST['Keywords']) > 0) {
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT supplierid,
							suppname,
							currcode,
							address1,
							address2,
							address3,
							address4
						FROM suppliers
						WHERE suppname " . LIKE . " '$SearchString'
							AND email<>''
						ORDER BY suppname";
		} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
			$_POST['SupplierCode'] = mb_strtoupper($_POST['SupplierCode']);
			$SQL = "SELECT supplierid,
							suppname,
							currcode,
							address1,
							address2,
							address3,
							address4
						FROM suppliers
						WHERE supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'
							AND email<>''
						ORDER BY supplierid";
		}
	} //one of keywords or SupplierCode was more than a zero length string
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 1) {
		$MyRow = DB_fetch_array($Result);
		$SingleSupplierReturned = $MyRow['supplierid'];
	}
} //end of if search
if (isset($SingleSupplierReturned)) { /*there was only one supplier returned */
	$_SESSION['SupplierID'] = $SingleSupplierReturned;
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
}

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if (isset($_POST['Suppliers'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post" class="noPrint">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Suppliers') . '</p>
		<fieldset>
			<legend class="search">', _('Supplier Search Criteria'), '</legend>
			<field>
				<label for="Keywords">' . _('Enter a partial Name') . ':</label>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="text" placeholder="' . _('Left it blank to show all') . '" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" placeholder="' . _('Left it blank to show all') . '" name="Keywords" size="20" maxlength="25" />';
	}
	echo '</field>';

	echo '<h3>' . _('OR') . '</h3>';

	echo '<field>
			<label for="SupplierCode">' . _('Enter a partial Code') . ':</label>';
	if (isset($_POST['SupplierCode'])) {
		echo '<input type="text" placeholder="' . _('Leave it blank to show all') . '" name="SupplierCode" value="' . $_POST['SupplierCode'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" placeholder="' . _('Leave it blank to show all') . '" name="SupplierCode" size="15" maxlength="18" />';
	}
	echo '</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="SearchSupplier" value="' . _('Search Now') . '" />
		</div>';
	echo '</form>';
}

if (isset($_POST['SearchSupplier'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post" class="noPrint">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($Result);
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
	if ($ListPageMax > 1) {
		echo '<br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			} else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<input type="submit" name="Go" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '<br />';
	}
	echo '<input type="hidden" name="Search" value="' . _('Search Now') . '" />';
	echo '<table cellpadding="2">';
	echo '<tr>
	  		<th class="assending">' . _('Code') . '</th>
			<th class="assending">' . _('Supplier Name') . '</th>
			<th class="assending">' . _('Currency') . '</th>
			<th class="assending">' . _('Address 1') . '</th>
			<th class="assending">' . _('Address 2') . '</th>
			<th class="assending">' . _('Address 3') . '</th>
			<th class="assending">' . _('Address 4') . '</th>
		</tr>';
	$j = 1;
	$RowIndex = 0;
	if (DB_num_rows($Result) <> 0) {
		DB_data_seek($Result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	} else {
		prnMsg(_('There are no suppliers data returned, one reason maybe no email addresses set for those suppliers'), 'warn');
	}
	while (($MyRow = DB_fetch_array($Result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
		echo '<tr class="striped_row">
			<td><input type="submit" name="SelectedSupplier" value="' . $MyRow['supplierid'] . '" /></td>
			<td>' . $MyRow['suppname'] . '</td>
			<td>' . $MyRow['currcode'] . '</td>
			<td>' . $MyRow['address1'] . '</td>
			<td>' . $MyRow['address2'] . '</td>
			<td>' . $MyRow['address3'] . '</td>
			<td>' . $MyRow['address4'] . '</td>
			</tr>';
		$RowIndex = $RowIndex + 1;
		//end of page full new headings if

	}
	//end of while loop
	echo '</table>';
	echo '</div>
		</form>';
}

/*The supplier has chosen option 2
*/
if (isset($_POST['Items'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post" class="noPrint">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Inventory Items') . '</p>';
	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		echo '<br /><p class="bad">' . _('Problem Report') . ':</p><br />' . _('There are no stock categories currently defined please use the link below to set them up');
		echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
		exit;
	}
	echo '<fieldset>
			<legend class="search">', _('Item Search Criteria'), '</legend>
			<field>
				<label for="StockCat">' . _('In Stock Category') . ':</label>
				<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = '';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		<field>
			<label for="Keywords">' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</label>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="text" name="Keywords" placeholder="' . _('Leave it bank to show all') . '" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" name="Keywords" placeholder="' . _('Leave it bank to show all') . '" size="20" maxlength="25" />';
	}
	echo '</field>
		<h3>' . _('OR') . ' ' . '</h3>';

	echo '<field>
			<label for="StockCode"' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</label>';
	if (isset($_POST['StockCode'])) {
		echo '<input type="text" name="StockCode" placeholder="' . _('Leave it bank to show all') . '" autofocus="autofocus" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="StockCode" placeholder="' . _('Leave it bank to show all') . '" autofocus="autofocus"  size="15" maxlength="18" />';
	}
	echo '</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Search" value="' . _('Search Now') . '" />
		</div>
		</form>';
}

if (isset($_POST['Search'])) { /*ie seach for stock items */
	echo '<form method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Tenders') . '" alt="" />' . ' ' . _('Select items required on this tender') . '</p>';

	if ($_POST['Keywords'] and $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.mbflag!='G'
					AND stockmaster.discontinued!=1
					AND stockmaster.description " . LIKE . " '$SearchString'
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.mbflag!='G'
					AND stockmaster.discontinued!=1
					AND stockmaster.description " . LIKE . " '$SearchString'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
		}

	} elseif ($_POST['StockCode']) {

		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.mbflag!='G'
					AND stockmaster.discontinued!=1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.mbflag!='G'
					AND stockmaster.discontinued!=1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
		}

	} else {
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.mbflag!='G'
					AND stockmaster.discontinued!=1
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.mbflag!='G'
					AND stockmaster.discontinued!=1
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
		}
	}

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL statement that failed was');
	$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult) == 0 and $debug == 1) {
		prnMsg(_('There are no products to display matching the criteria provided'), 'warn');
	}
	if (DB_num_rows($SearchResult) == 1) {

		$MyRow = DB_fetch_array($SearchResult);
		$_GET['NewItem'] = $MyRow['stockid'];
		DB_data_seek($SearchResult, 0);
	}

	if (isset($SearchResult)) {

		echo '<table cellpadding="1">';
		echo '<tr>
				<th class="assending">' . _('Code') . '</th>
				<th class="assending">' . _('Description') . '</th>
				<th class="assending">' . _('Units') . '</th>
				<th class="assending">' . _('Image') . '</th>
				<th class="assending">' . _('Quantity') . '</th>
			</tr>';

		$i = 0;
		$PartsDisplayed = 0;
		while ($MyRow = DB_fetch_array($SearchResult)) {

			$SupportedImgExt = array('png', 'jpg', 'jpeg');
			$glob = (glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
			$imagefile = reset($glob);
			if (extension_loaded('gd') && function_exists('gd_info') && file_exists($imagefile)) {
				$ImageSource = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC' . '&amp;StockID=' . urlencode($MyRow['stockid']) . '&amp;text=' . '&amp;width=64' . '&amp;height=64' . '" alt="" />';
			} else if (file_exists($imagefile)) {
				$ImageSource = '<img src="' . $imagefile . '" height="64" width="64" />';
			} else {
				$ImageSource = _('No Image');
			}

			echo '<tr class="striped_row">
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $MyRow['units'] . '</td>
					<td>' . $ImageSource . '</td>
					<td><input class="number" type="text" size="6" value="0" name="Qty' . $i . '" /></td>
					<input type="hidden" value="' . $MyRow['units'] . '" name="UOM' . $i . '" />
					<input type="hidden" value="' . $MyRow['stockid'] . '" name="StockID' . $i . '" />
					</tr>';

			$i++;
			#end of page full new headings if

		}
		#end of while loop
		echo '</table>';

		echo '<a name="end"></a>
			<br />
			<div class="centre">
				<input type="submit" name="NewItem" value="' . _('Add to Tender') . '" />
			</div>';
	} #end if SearchResults to show
	echo '</div>
		</form>';

} //end of if search
include ('includes/footer.php');

?>
