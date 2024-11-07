<?php
include('includes/session.php');
$Title = _('Supplier Invoice and GRN inquiry');
$ViewTopic = 'AccountsPayable';
$BookMark = '';
include('includes/header.php');
if (isset($_GET['SelectedSupplier'])) {
	$SupplierID= $_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])){
	$SupplierID = $_POST['SelectedSupplier'];
} else {
	prnMsg(_('The page must be called from suppliers selected interface, please click following link to select the supplier'),'error');
	echo '<a href="' . $RootPath . '/SelectSupplier.php">'. _('Select Supplier') . '</a>';
	include('includes/footer.php');
	exit;
}
if (isset($_GET['SupplierName'])) {
	$SupplierName = $_GET['SupplierName'];
}
if (!isset($_POST['SupplierRef']) OR trim($_POST['SupplierRef'])=='') {
	$_POST['SupplierRef'] = '';
	if (empty($_POST['GRNBatchNo']) AND empty($_POST['InvoiceNo'])) {
		$_POST['GRNBatchNo'] = '';
		$_POST['InvoiceNo'] = '';
	} elseif (!empty($_POST['GRNBatchNo']) AND !empty($_POST['InvoiceNo'])) {
		$_POST['InvoiceNo'] = '';
	}
} elseif (isset($_POST['GRNBatchNo']) OR isset($_POST['InvoiceNo'])) {
	$_POST['GRNBatchNo'] = '';
	$_POST['InvoiceNo'] = '';
}
echo '<p class="page_title_text">' . _('Supplier Invoice and Delivery Note Inquiry') . '<img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" alt="" />' . _('Supplier') . ': ' . $SupplierName . '</p>';
echo '<div class="page_help_text">' . _('The supplier\'s delivery note is prefer to GRN No, and GRN No is preferred to Invoice No').'</div>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="SelectedSupplier" value="' . $SupplierID . '" />';

echo '<fieldset>
		<legend>', _('Inquiry Criteria'), '</legend>
		<field>
			<label>' . _('Part of Supplier\'s Delivery Note') . ':</label>
			<input type="text" name="SupplierRef" value="' . $_POST['SupplierRef'] . '" size="20" maxlength="30" >
		</field>
		<field>
			<label>' . _('GRN No') . ':</label>
			<input type="text" name="GRNBatchNo" value="' . $_POST['GRNBatchNo'] . '" size="6" maxlength="6" />
		</field>
		<field>
			<label>' . _('Invoice No') . ':</label>
			<input type="text" name="InvoiceNo" value="' . $_POST['InvoiceNo'] . '" size="11" maxlength="11" />
		</field>
	</fieldset>';
echo '<div class="centre">
		<input type="submit" name="Submit" value="' . _('Submit') . '" />
	</div>';
if (isset($_POST['Submit'])) {
	$Where = '';
	if (isset($_POST['SupplierRef']) AND trim($_POST['SupplierRef']) != '') {
		$SupplierRef = trim($_POST['SupplierRef']);
		$WhereSupplierRef = " AND grns.supplierref LIKE '%" . $SupplierRef . "%'";
		$Where .= $WhereSupplierRef;
	} elseif (isset($_POST['GRNBatchNo']) AND trim($_POST['GRNBatchNo']) != '') {
		$GRNBatchNo = trim($_POST['GRNBatchNo']);
		$WhereGRN = " AND grnbatch LIKE '%" . $GRNBatchNo . "%'";
		$Where .= $WhereGRN;
	} elseif (isset($_POST['InvoiceNo']) AND (trim($_POST['InvoiceNo']) != '')) {
		$InvoiceNo = trim($_POST['InvoiceNo']);
		$WhereInvoiceNo = " AND suppinv LIKE '%" . $InvoiceNo . "%'";
		$Where .= $WhereInvoiceNo;
	}
	$sql = "SELECT grnbatch, grns.supplierref, suppinv,purchorderdetails.orderno
		FROM grns INNER JOIN purchorderdetails ON grns.podetailitem=purchorderdetails.podetailitem
		LEFT JOIN suppinvstogrn ON grns.grnno=suppinvstogrn.grnno
		WHERE supplierid='" . $SupplierID . "'" . $Where;
	$ErrMsg = _('Failed to retrieve supplier invoice and grn data');
	$result = DB_query($sql,$ErrMsg);
	if (DB_num_rows($result)>0) {
		echo '<table class="selection">
			<thead>
			<tr>
					<th class="ascending">' . _('Supplier Delivery Note') . '</th>
					<th class="ascending">' . _('GRN Batch No') . '</th>
					<th class="ascending">' . _('PO No') . '</th>
					<th class="ascending">' . _('Invoice No') . '</th>
				</tr>
			</thead>
			<tbody>';

		while ($myrow = DB_fetch_array($result)){
			echo '<tr class="striped_row">
				<td>' . $myrow['supplierref'] . '</td>
				<td><a href="' . $RootPath .'/PDFGrn.php?GRNNo=' . $myrow['grnbatch'] . '&amp;PONo=' . $myrow['orderno'] . '">' . $myrow['grnbatch']. '</td>
				<td>' . $myrow['orderno'] . '</td>
				<td>' . $myrow['suppinv'] . '</td>
				</tr>';

		}
		echo '</tbody></table><br/>';

	}

}
include('includes/footer.php');
?>