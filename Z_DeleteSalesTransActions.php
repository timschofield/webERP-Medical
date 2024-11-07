<?php
/*Script to Delete all sales transactions*/

include ('includes/session.php');
$Title = _('Delete Sales Transactions');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php'); ;
include ('includes/header.php');

if (isset($_POST['ProcessDeletions'])) {

	if ($_POST['SalesAnalysis'] == 'on') {

		prnMsg(_('Deleting sales analysis records'), 'info');

		$SQL = "DELETE FROM salesanalysis";
		$ErrMsg = _('The SQL to delete Sales Analysis records failed because');
		$Result = DB_query($SQL, $ErrMsg);
	}
	if ($_POST['DebtorTrans'] == 'on') {

		prnMsg(_('Deleting customer statement transactions and allocation records'), 'info');

		$ErrMsg = _('The SQL to delete customer transaction records failed because');

		$Result = DB_query("DELETE FROM custallocns", $ErrMsg);
		$Result = DB_query("DELETE FROM debtortranstaxes", $ErrMsg);
		$Result = DB_query("DELETE FROM debtortrans", $ErrMsg);
		$Result = DB_query("DELETE FROM stockserialmoves", $ErrMsg);
		$Result = DB_query("DELETE FROM stockmovestaxes", $ErrMsg);
		$Result = DB_query("DELETE FROM stockmoves WHERE type=10 OR type=11", $ErrMsg);

		$ErrMsg = _('The SQL to update the transaction numbers for all sales transactions because');
		$SQL = "UPDATE systypes SET typeno =0
						WHERE typeid =10
						OR typeid=11
						OR typeid=15
						OR typeid=12";
		$Result = DB_query($SQL, $ErrMsg);

	}
	if ($_POST['SalesOrders'] == 'on') {

		prnMsg(_('Deleting all sales order records'), 'info');

		$ErrMsg = _('The SQL to delete sales order detail records failed because');
		$Result = DB_query('DELETE FROM salesorderdetails');

		$Result = DB_query('DELETE FROM orderdeliverydifferenceslog');

		$ErrMsg = _('The SQL to delete sales order header records failed because');
		$Result = DB_query('DELETE FROM salesorders', $ErrMsg);

		$SQL = 'UPDATE systypes SET typeno =0 WHERE typeid =30';
		$ErrMsg = _('The SQL to update the transaction number of sales orders has failed') . ', ' . _('the SQL statement was');
		$Result = DB_query($SQL, $ErrMsg);

	}
	if ($_POST['ZeroStock'] == 'on') {

		prnMsg(_('Making stock for all parts and locations nil'), 'info');
		$ErrMsg = _('The SQL to make all stocks zero failed because');

		$Result = DB_query("DELETE FROM pickserialdetails", $ErrMsg);
		$Result = DB_query("DELETE FROM stockserialmoves", $ErrMsg);
		$Result = DB_query("DELETE FROM stockserialitems", $ErrMsg);
		$Result = DB_query("DELETE FROM stockmovestaxes", $ErrMsg);
		$Result = DB_query("DELETE FROM stockmoves", $ErrMsg);
		$Result = DB_query("UPDATE locstock SET quantity=0", $ErrMsg);

	}
	if ($_POST['ZeroSalesOrders'] == 'on') {

		prnMsg(_('Making the quantity invoiced zero on all orders'), 'info');

		$SQL = "UPDATE salesorderdetails SET qtyinvoiced=0, completed=0";
		$ErrMsg = _('The SQL to un-invoice all sales orders failed');
		$Result = DB_query($SQL, $ErrMsg);

	}
	if ($_POST['SalesGL'] == 'on') {

		prnMsg(_('Deleting all sales related GL Transactions'), 'info');
		$SQL = "DELETE FROM gltrans WHERE type>=10 AND type <=15";
		$ErrMsg = _('The SQL to delete sales related GL Transactions failed');
		$Result = DB_query($SQL, $ErrMsg);
	}

	if ($_POST['StockGL'] == 'on') {

		prnMsg(_('Deleting all stock related GL Transactions'), 'info');

		$SQL = "DELETE FROM gltrans WHERE type=25 OR type=17 OR type=26 OR type=28";
		$ErrMsg = _('The SQL to delete stock related GL Transactions failed');
		$Result = DB_query($SQL, $ErrMsg);

	}
	if ($_POST['ZeroPurchOrders'] == 'on') {

		prnMsg(_('Zeroing all purchase order quantities received and uncompleting all purchase orders'), 'info');

		$SQL = 'UPDATE purchorderdetails SET quantityrecd=0, completed=0';
		$ErrMsg = _('The SQL to zero quantity received for all purchase orders line items and uncompleted all purchase order line items because');
		$Result = DB_query($SQL, $ErrMsg);

	}
	if ($_POST['GRNs'] == 'on') {

		prnMsg(_('Deleting all GRN records'), 'info');

		$ErrMsg = _('The SQL to delete Invoice/GRN records failed because');
		$Result = DB_query("DELETE FROM suppinvstogrn", $ErrMsg);

		$ErrMsg = _('The SQL to delete GRN records failed because');
		$Result = DB_query("DELETE FROM grns", $ErrMsg);

		$ErrMsg = _('The SQL to update the transaction number of stock receipts has failed because');
		$Result = DB_query("UPDATE systypes SET typeid =1 WHERE typeno =25", $ErrMsg);
	}
	if ($_POST['PurchOrders'] == 'on') {

		prnMsg(_('Deleting all Purchase Orders'), 'info');

		$ErrMsg = _('The SQL to delete all purchase order details failed, the SQL statement was');
		$Result = DB_query("DELETE FROM purchorderdetails", $ErrMsg);

		$ErrMsg = _('The SQL to delete all purchase orders failed because');
		$Result = DB_query("DELETE FROM purchorders", $ErrMsg);

		$ErrMsg = _('The SQL to update the transaction number of stock receipts has failed because');
		$Result = DB_query("UPDATE systypes SET typeno=0 WHERE typeid =18", $ErrMsg);

	}

	prnMsg(_('It is necessary to re-post the remaining general ledger transactions for the general ledger to get back in sync with the transactions that remain. This is an option from the Z_index.php page'), 'warn');
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', _('Select Transactions to Delete'), '</legend>
	<field>
		<label>' . _('Delete All Sales Analysis') . '</label>
		<input type="checkbox" name="SalesAnalysis" />
	</field>
	<field><label>' . _('Delete All statement transactions') . '</label>
		<input type="checkbox" name="DebtorTrans" />
	</field>
	<field><label>' . _('Zero All stock balances') . '</label>
		<input type="checkbox" name="ZeroStock" />
	</field>
	<field><label>' . _('Make Invoiced Qty Of All Sales Orders Nil') . '</label>
		<input type="checkbox" name="ZeroSalesOrders" />
	</field>
	<field><label>' . _('Delete All Sales Orders') . '</label>
		<input type="checkbox" name="SalesOrders" />
	</field>
	<field><label>' . _('Zero Received Qty of all purchase orders') . '</label>
		<input type="checkbox" name="ZeroPurchOrders" />
	</field>
	<field><label>' . _('Delete All Purchase Orders') . '</label>
		<input type="checkbox" name="PurchOrders" />
	</field>
	<field><label>' . _('Delete All Sales related stock movements') . '</label>
		<input type="checkbox" name="SalesStockMoves" />
	</field>
	<field><label>' . _('Delete All Stock Receipt stock movements') . '</label>
		<input type="checkbox" name="ReceiptStockMoves" />
	</field>
	<field><label>' . _('Delete All Sales GL Transactions') . '</label>
		<input type="checkbox" name="SalesGL" />
	</field>
	<field><label>' . _('Delete All Stock GL Transactions') . '</label>
		<input type="checkbox" name="StockGL" />
	</field>
	<field><label>' . _('Delete All PO Goods Received (GRNs)') . '</label>
		<input type="checkbox" name="GRNs" />
	</field>
</fieldset>';

echo '<div class="centre">';
echo '<input type="submit" name="ProcessDeletions" value="' . _('Process') . '"  onclick="return confirm(\'' . _('Are You Really REALLY Sure?') . '\');" />';
echo '</div>';
echo '</form>';

include ('includes/footer.php');
?>