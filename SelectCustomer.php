<?php
/* $Id$*/

include ('includes/session.inc');
$title = _('Search Customers');
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/CustomerSearch.php');
if (isset($_GET['Select'])) {
	$_SESSION['CustomerID'] = $_GET['Select'];
}
if (!isset($_SESSION['CustomerID'])) { //initialise if not already done
	$_SESSION['CustomerID'] = '';
}
if (isset($_GET['Area'])) {
	$_POST['Area']=$_GET['Area'];
	$_POST['Search']='Search';
	$_POST['Keywords']='';
	$_POST['CustCode']='';
	$_POST['CustPhone']='';
	$_POST['CustAdd']='';
	$_POST['CustType']='';
}
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customers') . '</p>';
if (!isset($_SESSION['CustomerType'])) { //initialise if not already done
	$_SESSION['CustomerType'] = "";
}
// only run geocode if integration is turned on and customer has been selected
if ($_SESSION['geocode_integration'] == 1 AND $_SESSION['CustomerID'] != "") {
	$sql = "SELECT geocode_key,
					center_long,
					center_lat,
					map_height,
					map_width,
					map_host
				FROM geocode_param WHERE 1";
	$ErrMsg = _('An error occurred in retrieving the information');
	$result = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_array($result);
	$sql = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.lat,
					custbranch.lng
				FROM debtorsmaster LEFT JOIN custbranch
					ON debtorsmaster.debtorno = custbranch.debtorno
				WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'
				ORDER BY debtorsmaster.debtorno";
	$ErrMsg = _('An error occurred in retrieving the information');
	$result2 = DB_query($sql, $db, $ErrMsg);
	$myrow2 = DB_fetch_array($result2);
	$lat = $myrow2['lat'];
	$lng = $myrow2['lng'];
	$api_key = $myrow['geocode_key'];
	$center_long = $myrow['center_long'];
	$center_lat = $myrow['center_lat'];
	$map_height = $myrow['map_height'];
	$map_width = $myrow['map_width'];
	$map_host = $myrow['map_host'];
	echo '<script src="http://maps.google.com/maps?file=api&v=2&key=' . $api_key . '"';
	echo ' type="text/javascript"></script>';
	echo ' <script type="text/javascript">';
	echo 'function load() {
		if (GBrowserIsCompatible()) {
			var map = new GMap2(document.getElementById("map"));
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());';
	echo 'map.setCenter(new GLatLng(' . $lat . ', ' . $lng . '), 11);';
	echo 'var marker = new GMarker(new GLatLng(' . $lat . ', ' . $lng . '));';
	echo 'map.addOverlay(marker);
		GEvent.addListener(marker, "click", function() {
		marker.openInfoWindowHtml(WINDOW_HTML);
		});
		marker.openInfoWindowHtml(WINDOW_HTML);
		}
		}
		</script>';
	echo '<body onload="load()" onunload="GUnload()">';
}
unset($result);
$msg = '';
$result=CustomerSearchSQL($db);
if (!isset($_POST['Search']) and !isset($_POST['Next']) and !isset($_POST['Previous']) and !isset($_POST['Go1']) and !isset($_POST['Go2']) and isset($_POST['JustSelectedACustomer']) and empty($_SESSION['CustomerID'])){
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i=0; $i< count($_POST); $i++){ //loop through the returned customers
		if(isset($_POST['SubmitCustomerSelection'.$i])){
			break;
		}
	}
	if ($i==count($_POST)){
		prnMsg(_('Unable to identify the selected customer'),'error');
	} else {
		$_SESSION['CustomerID'] = $_POST['SelectedCustomer'.$i];
		$_SESSION['BranchID'] = $_POST['SelectedBranch'.$i];
		unset($_POST['Search']);
	}
}

if ($_SESSION['CustomerID'] != '' AND !isset($_POST['Search'])) {
	$SQL = "SELECT debtorsmaster.name,
					debtorsmaster.currcode,
					custbranch.phoneno
			FROM debtorsmaster INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'
			AND custbranch.branchcode='" . $_SESSION['BranchID'] . "'";

	$ErrMsg = _('The customer name requested cannot be retrieved because');
	$result = DB_query($SQL, $db, $ErrMsg);
	if ($myrow = DB_fetch_array($result)) {
		$CustomerName = $myrow['name'];
		$PhoneNo = $myrow['phoneno'];
		$Currency = $myrow['currcode'];
	}
	unset($result);

	// Adding customer encoding. Not needed for general use. This is not a recommended upgrade submission. Gilles Deacur
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' .
		_('Customer') . ' : ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName . ' - ' . $PhoneNo . _(' has been selected') . '</p>';
	echo '<div class="page_help_text">' . _('Select a menu option to operate using this customer') . '.</div><br />';
	$_POST['Select'] = NULL;
	echo '<table cellpadding="4" width="90%" class="selection"><tr><th width="33%">' . _('Customer Inquiries') . '</th>
			<th width="33%">' . _('Customer Transactions') . '</th>
			<th width="33%">' . _('Customer Maintenance') . '</th></tr>';
	echo '<tr><td valign=top class="select">';
	/* Customer Inquiry Options */
	echo '<a href="' . $rootpath . '/CustomerInquiry.php?CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Customer Transaction Inquiries') . '</a><br />';
	echo '<a href="' . $rootpath . '/Customers.php?DebtorNo=' . $_SESSION['CustomerID'] . '&Modify=No">' . _('View Customer Details') . '</a><br />';
	echo '<a href="' . $rootpath . '/PrintCustStatements.php?FromCust=' . $_SESSION['CustomerID'] . '&ToCust=' . $_SESSION['CustomerID'] . '&PrintPDF=Yes">' . _('Print Customer Statement') . '</a><br />';
	echo '<a href="' . $rootpath . '/SelectCompletedOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Inquiries') . '</a><br />';
	wikiLink('Customer', $_SESSION['CustomerID']);
	echo '</td><td valign=top class="select">';
	echo '<a href="' . $rootpath . '/SelectSalesOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Modify Outstanding Sales Orders') . '</a><br />';
	echo '<a href="' . $rootpath . '/CustomerAllocations.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Allocate Receipts or Credit Notes') . '</a><br />';
	echo '<a href="' . $rootpath . '/CounterSales.php?DebtorNo=' . $_SESSION['CustomerID'] . '&BranchNo=' . $_SESSION['BranchID'] . '">' . _('Create a Counter Sale for this Customer') . '</a><br />';
	echo '</td><td valign=top class="select">';
	echo '<a href="' . $rootpath . '/Customers.php?">' . _('Add a New Customer') . '</a><br />';
	echo '<a href="' . $rootpath . '/Customers.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Modify Customer Details') . '</a><br />';
	echo '<a href="' . $rootpath . '/CustomerBranches.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add/Modify/Delete Customer Branches') . '</a><br />';
	echo '<a href="' . $rootpath . '/SelectProduct.php">' . _('Special Customer Prices') . '</a><br />';
	echo '<a href="' . $rootpath . '/CustEDISetup.php">' . _('Customer EDI Configuration') . '</a><br />';
	echo '<a href="' . $rootpath . '/CustLoginSetup.php">' . _('Customer Login Configuration') . '</a>';
	echo '</td>';
	echo '</tr></table><br />';
} else {
	echo '<table width="90%" class="selection"><tr><th width="33%">' . _('Customer Inquiries') . '</th>
			<th width=33%>' . _('Customer Transactions') . '</th>
			<th width=33%>' . _('Customer Maintenance') . '</th></tr>';
	echo '<tr><td class="select">';
	echo '</td><td class="select">';
	echo '</td><td class="select">';
	if (!isset($_SESSION['SalesmanLogin']) or $_SESSION['SalesmanLogin'] == '') {
		echo '<a href="' . $rootpath . '/Customers.php?">' . _('Add a New Customer') . '</a><br />';
	}
	echo '</td></tr></table>';
}
if (strlen($msg)>1){
   prnMsg($msg, 'info');
}
ShowCustomerSearchFields($rootpath, $theme, $db);
if (isset($result)) {
	ShowReturnedCustomers($result);
}
// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != '') {
	if ($_SESSION['geocode_integration'] == 1) {
		echo '<br />';
		if ($lat == 0) {
			echo '<div class="centre">' . _('Mapping is enabled, but no Mapping data to display for this Customer.') . '</div>';
		} else {
			echo '<tr><td colspan="2">';
			echo '<table width="45%" cellpadding="4" class="selection">';
			echo '<tr><th width="33%">' . _('Customer Mapping') . '</th></tr>';
			echo '</td><td valign="top">'; /* Mapping */
			echo '<div class="centre">' . _('Mapping is enabled, Map will display below.') . '</div>';
			echo '<div align="center" id="map" style="width: ' . $map_width . 'px; height: ' . $map_height . 'px"></div><br />';
			echo '</th></tr></table>';
		}
	}
	// Extended Customer Info only if selected in Configuration
	if ($_SESSION['Extended_CustomerInfo'] == 1) {
		if ($_SESSION['CustomerID'] != "") {
			$sql = "SELECT debtortype.typeid, debtortype.typename
						FROM debtorsmaster, debtortype
			WHERE debtorsmaster.typeid = debtortype.typeid
			AND debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";
			$ErrMsg = _('An error occurred in retrieving the information');
			$result = DB_query($sql, $db, $ErrMsg);
			$myrow = DB_fetch_array($result);
			$CustomerType = $myrow['typeid'];
			$CustomerTypeName = $myrow['typename'];
			// Customer Data
			echo '<br />';
			// Select some basic data about the Customer
			$SQL = "SELECT debtorsmaster.clientsince,
				(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.clientsince))) as customersincedays,
				(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.lastpaiddate))) as lastpaiddays,
				debtorsmaster.paymentterms, debtorsmaster.lastpaid, debtorsmaster.lastpaiddate
					FROM debtorsmaster
					WHERE debtorsmaster.debtorno ='" . $_SESSION['CustomerID'] . "'";
			$DataResult = DB_query($SQL, $db);
			$myrow = DB_fetch_array($DataResult);
			// Select some more data about the customer
			$SQL = "SELECT sum(ovamount+ovgst) AS total FROM debtortrans WHERE debtorno = '" . $_SESSION['CustomerID'] . "' AND type !=12";
			$Total1Result = DB_query($SQL, $db);
			$row = DB_fetch_array($Total1Result);
			echo '<tr><td colspan="2">';
			echo '<table width="45%" cellpadding="4" class="selection">';
			echo '<tr><th width="33%" colspan="3">' . _('Customer Data') . '</th></tr>';
			echo '<tr><td valign="top" class="select">'; /* Customer Data */
			//echo _('Distance to this customer:') . '<b>TBA</b><br />';
			if ($myrow['lastpaiddate'] == 0) {
				echo _('No receipts from this customer.') . '</td><td class="select"></td><td class="select"></td></tr>';
			} else {
				echo _('Last Paid Date:') . '</td><td class="select"> <b>' . ConvertSQLDate($myrow['lastpaiddate']) . '</b> </td>
					<td class="select">' . $myrow['lastpaiddays'] . ' ' . _('days') . '</td></tr>';
			}
			echo '<tr>
					<td class="select">' . _('Last Paid Amount (inc tax):') . '</td>
					<td class="select"> <b>' . locale_money_format($myrow['lastpaid'], $Currency) . '</b></td>
					<td class="select"></td>
				</tr>';
			echo '<tr>
					<td class="select">' . _('Customer since:') . '</td>
					<td class="select"> <b>' . ConvertSQLDate($myrow['clientsince']) . '</b> </td>
					<td class="select">' . $myrow['customersincedays'] . ' ' . _('days') . '</td>
				</tr>';
			if ($row['total'] == 0) {
				echo '<tr>
						<td class="select">' . _('No Spend from this Customer.') . '</b></td>
						<td class="select"></td>
						<td class="select"></td>
					</tr>';
			} else {
				echo '<tr>
						<td class="select">' . _('Total Spend from this Customer (inc tax):') . ' </td>
						<td class="select"><b>' . locale_money_format($row['total'], $Currency) . '</b></td>
						<td class="select"></td>
					</tr>';
			}
			echo '<tr>
					<td class="select">' . _('Customer Type:') . ' </td>
					<td class="select"><b>' . $CustomerTypeName . '</b></td>
					<td class="select"></td>
				</tr>';
			echo '</th></tr></table>';
		}
		// Customer Contacts
		echo '<tr><td colspan="2">';
		$sql = "SELECT contid,
						debtorno,
						contactname,
						role,
						phoneno,
						notes
					FROM custcontacts
					WHERE debtorno='" . $_SESSION['CustomerID'] . "'
					ORDER BY contid";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) <> 0) {
			echo '<table width="45%" class="selection">';
			echo '<br /><th colspan="7"><img src="' . $rootpath . '/css/' . $theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="" />' . ' ' . _('Customer Contacts') . '</th>';
			echo '<tr>
						<th>' . _('Name') . '</th>
						<th>' . _('Role') . '</th>
						<th>' . _('Phone Number') . '</th>
						<th>' . _('Notes') . '</th>
						<th>' . _('Edit') . '</th>
						<th>' . _('Delete') . '</th>
				<th> <a href="AddCustomerContacts.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add New Contact') . '</a> </th></tr>';
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $myrow['contactname'] . '</td>
						<td>' . $myrow['role'] . '</td>
						<td>' . $myrow['phoneno'] . '</td>
						<td>' . $myrow['notes'] . '</td>
						<td><a href="AddCustomerContacts.php?Id=' . $myrow['contid'] . '&DebtorNo=' . $myrow['debtorno'] . '">' . _('Edit') . '</a></td>
						<td><a href="AddCustomerContacts.php?Id=' . $myrow['contid'] . '&DebtorNo=' . $myrow['debtorno'] . '&delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</table>';
		} else {
			if ($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt=""><a href="AddCustomerContacts.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Contact') . '</a></div>';
			}
		}
		// Customer Notes
		echo '<tr><td colspan="2">';
		$sql = "SELECT noteid,
						debtorno,
						href,
						note,
						date,
						priority
					FROM custnotes
					WHERE debtorno='" . $_SESSION['CustomerID'] . "'
					ORDER BY date DESC";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="" />' . ' ' . _('Customer Notes') . '</div><br />';
			echo '<table width="45%" class="selection">';
			echo '<tr>
					<th>' . _('date') . '</th>
					<th>' . _('note') . '</th>
					<th>' . _('hyperlink') . '</th>
					<th>' . _('priority') . '</th>
					<th>' . _('Edit') . '</th>
					<th>' . _('Delete') . '</th>
					<th> <a href="AddCustomerNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Note') . '</a> </th></tr>';
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $myrow['date'] . '</td>
							<td>' . $myrow['note'] . '</td>
							<td>' . $myrow['href'] . '</td>
							<td>' . $myrow['priorities'] . '</td>
							<td><a href="AddCustomerNotes.php?Id=' . $myrow['noteid'] . '&DebtorNo=' . $myrow['debtorno'] . '">' . _('Edit') . '</a></td>
							<td><a href="AddCustomerNotes.php?Id=' . $myrow['noteid'] . '&DebtorNo=' . $myrow['debtorno'] . '&delete=1">' . _('Delete') . '</a></td>
							</tr>';
			} //END WHILE LIST LOOP
			echo '</table>';
		} else {
			if ($_SESSION['CustomerID'] != "") {
				echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt=""><a href="AddCustomerNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Note for this Customer') . '</a></div>';
			}
		}
		// Custome Type Notes
		echo '<tr><td colspan="2">';
		$sql = "SELECT noteid,
						typeid,
						href,
						note,
						date,
						priority
					FROM debtortypenotes
					WHERE typeid='" . $CustomerType . "'
					ORDER BY date DESC";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/folder_add.png" title="' . _('Customer Type (Group) Notes') . '" alt="" />' . ' ' . _('Customer Type (Group) Notes for:' . '<b> ' . $CustomerTypeName . '</b>') . '</div><br />';
			echo '<table width="45%" class="selection">';
			echo '<tr>
					 	<th>' . _('date') . '</th>
					  	<th>' . _('note') . '</th>
					   	<th>' . _('file link / reference / URL') . '</th>
					   	<th>' . _('priority') . '</th>
					   	<th>' . _('Edit') . '</th>
					   	<th>' . _('Delete') . '</th>
					   	<th><a href="AddCustomerTypeNotes.php?DebtorType=' . $CustomerType . '">' . _('Add New Group Note') . '</a></th></tr>';
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $myrow['date'] . '</td>
						<td>' . $myrow['note'] . '</td>
						<td>' . $myrow['href'] . '</td>
						<td>' . $myrow['priority'] . '</td>
						<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow['noteid'] . '&DebtorType=' . $myrow['typeid'] . '">' . _('Edit') . '</a></td>
						<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow['noteid'] . '&DebtorType=' . $myrow['typeid'] . '&delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</table>';
		} else {
			if ($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/folder_add.png" title="' . _('Customer Group Notes') . '" alt=""><a href="AddCustomerTypeNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Group Note') . '</a></div><br />';
			}
		}
	}
}
echo '<script>defaultControl(document.forms[0].CustCode);</script>';
include ('includes/footer.inc');
?>