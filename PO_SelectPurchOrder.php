<?php

include ('includes/session.php');
$Title = _('Search Purchase Orders');
$ViewTopic = 'PurchaseOrdering';
$BookMark = '';
include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Purchase Orders') . '" alt=""  />' . ' ' . _('Purchase Orders') .
	'</p>';

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['OrderNumber'])) {
	$OrderNumber = $_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])) {
	$OrderNumber = $_POST['OrderNumber'];
}
if (isset($_GET['SelectedSupplier'])) {
	$SelectedSupplier = $_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])) {
	$SelectedSupplier = $_POST['SelectedSupplier'];
}
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}
if (isset($OrderNumber) AND $OrderNumber != '') {
	if (!is_numeric($OrderNumber)) {
		prnMsg(_('The Order Number entered') . ' <U>' . _('MUST') . '</U> ' . _('be numeric'), 'error');
		unset($OrderNumber);
	} else {
		echo _('Order Number') . ' - ' . $OrderNumber;
	}
} else {
	if (isset($SelectedSupplier)) {
		echo _('For supplier') . ': ' . $SelectedSupplier . ' ' . _('and') . ' ';
		echo '<input type="hidden" name="SelectedSupplier" value="' . $SelectedSupplier . '" />';
	}
}
if (isset($_POST['SearchParts'])) {
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) as qoh,
				stockmaster.units,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
			FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid = locstock.stockid INNER JOIN purchorderdetails
			ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.description " . LIKE  . " '" . $SearchString ."'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	} elseif ($_POST['StockCode']) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord,
				stockmaster.units
			FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.stockid " . LIKE  . " '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	} elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
			FROM stockmaster INNER JOIN locstock ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	}
	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL, $ErrMsg, $DbgMsg);
}
/* Not appropriate really to restrict search by date since user may miss older
* ouststanding orders
* $OrdersAfterDate = Date("d/m/Y",Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
*/
if (!isset($OrderNumber) or $OrderNumber == "") {
	echo '<fieldset>
			<legend>', _('Order Selection Options'), '</legend>';
	if (isset($SelectedStockItem)) {
		echo _('For the part') . ':<b>' . $SelectedStockItem . '</b> ' . _('and') . ' <input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';
	}
	echo '<field>
			<label for="OrderNumber">', _('Order Number') . ':</label>
			<input class="integer" name="OrderNumber" autofocus="autofocus" maxlength="8" size="9" />
		</field>';

	echo '<field>
			<label for="StockLocation">' . _('Into Stock Location') . ':</label>
			<select name="StockLocation"> ';

	$sql = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$resultStkLocs = DB_query($sql);
	while ($myrow = DB_fetch_array($resultStkLocs)) {
		if (isset($_POST['StockLocation'])) {
			if ($myrow['loccode'] == $_POST['StockLocation']) {
				echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			}
		} elseif ($myrow['loccode'] == $_SESSION['UserStockLocation']) {
			echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Status">' . _('Order Status:') .'</label>
			<select name="Status">';
 	if (!isset($_POST['Status']) OR $_POST['Status']=='Pending_Authorised_Completed'){
		echo '<option selected="selected" value="Pending_Authorised_Completed">' . _('Pending/Authorised/Completed') . '</option>';
	} else {
		echo '<option value="Pending_Authorised_Completed">' . _('Pending/Authorised/Completed') . '</option>';
	}
	if (isset($_POST['Status']) AND $_POST['Status']=='Pending'){
		echo '<option selected="selected" value="Pending">' . _('Pending') . '</option>';
	} else {
		echo '<option value="Pending">' . _('Pending') . '</option>';
	}
 	if (isset($_POST['Status']) AND $_POST['Status']=='Authorised'){
		echo '<option selected="selected" value="Authorised">' . _('Authorised') . '</option>';
	} else {
		echo '<option value="Authorised">' . _('Authorised') . '</option>';
	}
	if (isset($_POST['Status']) AND $_POST['Status']=='Completed'){
		echo '<option selected="selected" value="Completed">' . _('Completed') . '</option>';
	} else {
		echo '<option value="Completed">' . _('Completed') . '</option>';
	}
	if (isset($_POST['Status']) AND $_POST['Status']=='Cancelled'){
		echo '<option selected="selected" value="Cancelled">' . _('Cancelled') . '</option>';
	} else {
		echo '<option value="Cancelled">' . _('Cancelled') . '</option>';
	}
	if (isset($_POST['Status']) AND $_POST['Status']=='Rejected'){
		echo '<option selected="selected" value="Rejected">' . _('Rejected') . '</option>';
	} else {
		echo '<option value="Rejected">' . _('Rejected') . '</option>';
	}
 	echo '</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SearchOrders" value="' . _('Search Purchase Orders') . '" />
		</div>';
}
$SQL = "SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$result1 = DB_query($SQL);
echo '<fieldset>
		<legend>', _('To search for purchase orders for a specific part use the part selection facilities below') . '</legend>';
echo '<field>
		<label foe="StockCat">' . _('Select a stock category') . ':</label>
		<select name="StockCat">';
while ($myrow1 = DB_fetch_array($result1)) {
	if (isset($_POST['StockCat']) and $myrow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Keywords">' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</label>
		<input type="text" name="Keywords" size="20" maxlength="25" />
	</field>
	<h1>' . _('OR') . '</h1>
	<field>
		<label for="StockCode">' . _('Enter extract of the') . '<b> ' . _('Stock Code') . '</b>:</label>
		<input type="text" name="StockCode" size="15" maxlength="18" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="SearchParts" value="' . _('Search Parts Now') . '" />
		<input type="submit" name="ResetPart" value="' . _('Show All') . '" />
	</div>';

if (isset($StockItemsResult)) {
	echo '<table class="selection">
		<thead>
			<tr>
				<th class="ascending">' . _('Code') . '</th>
				<th class="ascending">' . _('Description') . '</th>
				<th class="ascending">' . _('On Hand') . '</th>
				<th class="ascending">' . _('Orders') . '<br />' . _('Outstanding') . '</th>
				<th class="ascending">' . _('Units') . '</th>
			</tr>
		</thead>
		<tbody>';

	while ($myrow = DB_fetch_array($StockItemsResult)) {
		echo '<tr class="striped_row">
			<td><input type="submit" name="SelectedStockItem" value="' . $myrow['stockid'] . '"</td>
			<td>' . $myrow['description'] . '</td>
			<td class="number">' . locale_number_format($myrow['qoh'],$myrow['decimalplaces']) . '</td>
			<td class="number">' . locale_number_format($myrow['qord'],$myrow['decimalplaces']) . '</td>
			<td>' . $myrow['units'] . '</td>
			</tr>';
	}
	//end of while loop
	echo '</tbody></table>';
}
//end if stock search results to show
else {
	//figure out the SQL required from the inputs available

	if (!isset($_POST['Status']) OR $_POST['Status']=='Pending_Authorised_Completed'){
		$StatusCriteria = " AND (purchorders.status='Pending' OR purchorders.status='Authorised' OR purchorders.status='Printed' OR purchorders.status='Completed') ";
	}elseif ($_POST['Status']=='Authorised'){
		$StatusCriteria = " AND (purchorders.status='Authorised' OR purchorders.status='Printed')";
	}elseif ($_POST['Status']=='Pending'){
		$StatusCriteria = " AND purchorders.status='Pending' ";
	}elseif ($_POST['Status']=='Rejected'){
		$StatusCriteria = " AND purchorders.status='Rejected' ";
	}elseif ($_POST['Status']=='Cancelled'){
		$StatusCriteria = " AND purchorders.status='Cancelled' ";
	} elseif($_POST['Status']=='Completed'){
		$StatusCriteria = " AND purchorders.status='Completed' ";
	}
	if (isset($OrderNumber) AND $OrderNumber != '') {
		$SQL = "SELECT purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.deliverydate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						purchorders.status,
						suppliers.currcode,
						currencies.decimalplaces AS currdecimalplaces,
						SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
					FROM purchorders
					INNER JOIN purchorderdetails
					ON purchorders.orderno = purchorderdetails.orderno
					INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
					INNER JOIN currencies
					ON suppliers.currcode=currencies.currabrev
					WHERE purchorders.orderno='" . filter_number_format($OrderNumber) . "'
					GROUP BY purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						purchorders.status,
						suppliers.currcode,
						currencies.decimalplaces";
	} else {
		/* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */
		if (empty($_POST['StockLocation'])) {
			$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
		}
		if (isset($SelectedSupplier)) {
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.deliverydate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								purchorders.status,
								suppliers.currcode,
								currencies.decimalplaces AS currdecimalplaces,
								SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorders.orderno = purchorderdetails.orderno
							INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
							INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE  purchorderdetails.itemcode='" . $SelectedStockItem . "'
							AND purchorders.supplierno='" . $SelectedSupplier . "'
							AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
							" . $StatusCriteria . "
							GROUP BY purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								suppliers.currcode,
								currencies.decimalplaces";
			} else {
				$SQL = "SELECT purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.deliverydate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								purchorders.status,
								suppliers.currcode,
								currencies.decimalplaces AS currdecimalplaces,
								SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorders.orderno = purchorderdetails.orderno
							INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
							INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE purchorders.supplierno='" . $SelectedSupplier . "'
							AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
							" . $StatusCriteria . "
							GROUP BY purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								suppliers.currcode,
								currencies.decimalplaces";
			}
		} else { //no supplier selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.deliverydate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								purchorders.status,
								suppliers.currcode,
								currencies.decimalplaces AS currdecimalplaces,
								SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorders.orderno = purchorderdetails.orderno
							INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
							INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE purchorderdetails.itemcode='" . $SelectedStockItem . "'
							AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
							" . $StatusCriteria . "
							GROUP BY purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								suppliers.currcode,
								currencies.decimalplaces";
			} else {
				$SQL = "SELECT purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.deliverydate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								purchorders.status,
								suppliers.currcode,
								currencies.decimalplaces AS currdecimalplaces,
								SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorders.orderno = purchorderdetails.orderno
							INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
							INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
							" . $StatusCriteria . "
							GROUP BY purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								suppliers.currcode,
								currencies.decimalplaces";
			}
		} //end selected supplier

	} //end not order number selected
	$ErrMsg = _('No orders were returned by the SQL because');
	$PurchOrdersResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($PurchOrdersResult) > 0) {
		/*show a table of the orders returned by the SQL */
		echo '<table cellpadding="2" width="90%" class="selection">
			<thead>
				<tr>
					<th class="ascending">' . _('View') . '</th>
					<th class="ascending">' . _('Supplier') . '</th>
					<th class="ascending">' . _('Currency') . '</th>
					<th class="ascending">' . _('Requisition') . '</th>
					<th class="ascending">' . _('Order Date') . '</th>
					<th class="ascending">' . _('Delivery Date') . '</th>
					<th class="ascending">' . _('Initiator') . '</th>
					<th class="ascending">' . _('Order Total') . '</th>
					<th class="ascending">' . _('Status') . '</th>
				</tr>
			</thead>
			</tbody>';

		while ($myrow = DB_fetch_array($PurchOrdersResult)) {
			$ViewPurchOrder = $RootPath . '/PO_OrderDetails.php?OrderNo=' . $myrow['orderno'];
			$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
			$FormatedDeliveryDate = ConvertSQLDate($myrow['deliverydate']);
			$FormatedOrderValue = locale_number_format($myrow['ordervalue'], $myrow['currdecimalplaces']);

			echo '<tr class="striped_row">
					<td><a href="' . $ViewPurchOrder . '">' . $myrow['orderno'] . '</a></td>
					<td>' . $myrow['suppname'] . '</td>
					<td>' . $myrow['currcode'] . '</td>
					<td>' . $myrow['requisitionno'] . '</td>
					<td>' . $FormatedOrderDate . '</td>
					<td>' . $FormatedDeliveryDate . '</td>
					<td>' . $myrow['initiator'] . '</td>
					<td class="number">' . $FormatedOrderValue . '</td>
					<td>' . _($myrow['status']) .  '</td>
					</tr>';
				//$myrow['status'] is a string which has gettext translations from PO_Header.php script
		}
		//end of while loop
		echo '</tbody></table>';
	} // end if purchase orders to show
}
echo '</div>
      </form>';
include ('includes/footer.php');
?>
