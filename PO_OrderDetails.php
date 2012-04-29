<?php

/* $Id$*/
/* $Revision: 1.15 $ */

include('includes/session.inc');

if (isset($_GET['OrderNo'])) {
	$title = _('Reviewing Purchase Order Number').' ' . $_GET['OrderNo'];
	$_GET['OrderNo']=(int)$_GET['OrderNo'];
} else {
	$title = _('Reviewing A Purchase Order');
}
include('includes/header.inc');

if (isset($_GET['FromGRNNo'])){

	$SQL= "SELECT purchorderdetails.orderno
		FROM purchorderdetails,
			grns
		WHERE purchorderdetails.podetailitem=grns.podetailitem
		AND grns.grnno='" . $_GET['FromGRNNo'] ."'";

	$ErrMsg = _('The search of the GRNs was unsuccessful') . ' - ' . _('the SQL statement returned the error');
	$orderResult = DB_query($SQL, $db, $ErrMsg);

	$orderRow = DB_fetch_row($orderResult);
	$_GET['OrderNo'] = $orderRow[0];
	echo '<br /><font size="4" color="blue">' . _('Order Number') . ' ' . $_GET['OrderNo'] . '</font>';
}

if (!isset($_GET['OrderNo'])) {

	echo '<br /><br />';
	prnMsg( _('This page must be called with a purchase order number to review'), 'error');

	echo '<table class="table_index">
		<tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/PO_SelectPurchOrder.php">' . _('Outstanding Purchase Orders') . '</a></li>
		</td></tr></table>';
	include('includes/footer.inc');
	exit;
}

$ErrMsg = _('The order requested could not be retrieved') . ' - ' . _('the SQL returned the following error');
$OrderHeaderSQL = "SELECT purchorders.*,
			suppliers.supplierid,
			suppliers.suppname,
			suppliers.currcode,
			www_users.realname,
			locations.locationname
		FROM purchorders
		LEFT JOIN www_users
		ON purchorders.initiator=www_users.userid
		LEFT JOIN locations
		ON locations.loccode=purchorders.intostocklocation
		LEFT JOIN suppliers
		ON purchorders.supplierno = suppliers.supplierid
		WHERE purchorders.orderno = '" . $_GET['OrderNo'] ."'";

$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db, $ErrMsg);

if (DB_num_rows($GetOrdHdrResult)!=1) {
	echo '<br /><br />';
	if (DB_num_rows($GetOrdHdrResult) == 0){
		prnMsg ( _('Unable to locate this PO Number') . ' '. $_GET['OrderNo'] . '. ' . _('Please look up another one') . '. ' . _('The order requested could not be retrieved') . ' - ' . _('the SQL returned either 0 or several purchase orders'), 'error');
	} else {
		prnMsg ( _('The order requested could not be retrieved') . ' - ' . _('the SQL returned either several purchase orders'), 'error');
	}
        echo '<table class="table_index">
                <tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/PO_SelectPurchOrder.php">' . _('Outstanding Sales Orders') . '</a></li>
                </td></tr></table>';

	include('includes/footer.inc');
	exit;
}
 // the checks all good get the order now

$myrow = DB_fetch_array($GetOrdHdrResult);

$OrderCurrCode = $myrow['currcode'];

/* SHOW ALL THE ORDER INFO IN ONE PLACE */
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Purchase Order') . '" alt="" />' . ' ' . $title . '</p>';

echo '<table class="selection" cellpadding="2">';
echo '<tr><th colspan="8" class="header">'. _('Order Header Details'). '</th></tr>';
echo '<tr><th style="text-align:left">' . _('Supplier Code'). '</td><td><a href="SelectSupplier.php?SupplierID='.$myrow['supplierid'].'">' . $myrow['supplierid'] . '</a></td>
	<th style="text-align:left">' . _('Supplier Name'). '</td><td><a href="SelectSupplier.php?SupplierID='.$myrow['supplierid'].'">' . $myrow['suppname'] . '</a></td></tr>';

echo '<tr><th style="text-align:left">' . _('Ordered On'). '</td><td>' . ConvertSQLDate($myrow['orddate']) . '</td>
	<th style="text-align:left">' . _('Delivery Address 1'). '</td><td>' . $myrow['deladd1'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Order Currency'). '</td><td>' . $OrderCurrCode . '</td>
	<th style="text-align:left">' . _('Delivery Address 2'). '</td><td>' . $myrow['deladd2'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Exchange Rate'). '</td><td>' . $myrow['rate'] . '</td>
	<th style="text-align:left">' . _('Delivery Address 3'). '</td><td>' . $myrow['deladd3'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Deliver Into Location'). '</td><td>' . $myrow['locationname'] . '</td>
	<th style="text-align:left">' . _('Delivery Address 4'). '</td><td>' . $myrow['deladd4'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Initiator'). '</td><td>' . $myrow['realname'] . '</td>
	<th style="text-align:left">' . _('Delivery Address 5'). '</td><td>' . $myrow['deladd5'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Requisition Ref'). '.</td><td>' . $myrow['requisitionno'] . '</td>
	<th style="text-align:left">' . _('Delivery Address 6'). '</td><td>' . $myrow['deladd6'] . '</td></tr>';


echo '<tr><th style="text-align:left">'. _('Printing') . '</td><td colspan="3">';

if ($myrow['dateprinted'] == ''){
	echo '<i>'. _('Not yet printed') . '</i> &nbsp; &nbsp; ';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print') .'</a>]';
} else {
	echo _('Printed on').' '. ConvertSQLDate($myrow['dateprinted']). '&nbsp; &nbsp;';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print a Copy') .'</a>]';
}

echo  '</td></tr>';
echo '<tr><th style="text-align:left">'. _('Status') . '</td><td>'. _($myrow['status']) . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Comments'). '</td><td colspan="3">' . $myrow['comments'] . '</td></tr>';

echo '</table>';


echo '<br />';
/*Now get the line items */
$ErrMsg = _('The line items of the purchase order could not be retrieved');
$LineItemsSQL = "SELECT itemcode,
						itemdescription,
						quantityord,
						quantityrecd,
						qtyinvoiced,
						unitprice,
						actprice,
						deliverydate,
						stockmaster.decimalplaces
				FROM purchorderdetails
				LEFT JOIN stockmaster
					ON purchorderdetails.itemcode=stockmaster.stockid
				WHERE purchorderdetails.orderno = '" . $_GET['OrderNo'] ."'";

$LineItemsResult = DB_query($LineItemsSQL,$db, $ErrMsg);


echo '<table class="selection" cellpadding="0">';
echo '<tr><th colspan="8" class="header">'. _('Order Line Details'). '</th></tr>';
echo '<tr>
		<th>' . _('Item Code'). '</td>
		<th>' . _('Item Description'). '</td>
		<th>' . _('Ord Qty'). '</td>
		<th>' . _('Qty Recd'). '</td>
		<th>' . _('Qty Inv'). '</td>
		<th>' . _('Ord Price'). '</td>
		<th>' . _('Chg Price'). '</td>
		<th>' . _('Reqd Date'). '</td>
	</tr>';

$k =0;  //row colour counter
$OrderTotal=0;
$RecdTotal=0;

while ($myrow=DB_fetch_array($LineItemsResult)) {

	$OrderTotal += ($myrow['quantityord'] * $myrow['unitprice']);
	$RecdTotal += ($myrow['quantityrecd'] * $myrow['unitprice']);

	$DisplayReqdDate = ConvertSQLDate($myrow['deliverydate']);

	// if overdue and outstanding quantities, then highlight as so
	if (($myrow['quantityord'] - $myrow['quantityrecd'] > 0)
	  	AND Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']), $DisplayReqdDate)){
    	 	echo '<tr class="OsRow">';
	} else {
    		if ($k==1){
    			echo '<tr class="EvenTableRows">';
    			$k=0;
    		} else {
    			echo '<tr class="OddTableRows">';
    			$k=1;
		}
	}

	printf ('<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td>%s</td>
		</tr>' ,
			$myrow['itemcode'],
			$myrow['itemdescription'],
			locale_number_format($myrow['quantityord'], $myrow['decimalplaces']),
			locale_number_format($myrow['quantityrecd'], $myrow['decimalplaces']),
			locale_number_format($myrow['qtyinvoiced'], $myrow['decimalplaces']),
			locale_money_format($myrow['unitprice'], $OrderCurrCode),
			locale_money_format($myrow['actprice'], $OrderCurrCode),
			$DisplayReqdDate);

}

echo '<tr><td><br /></td>
	</tr>
	<tr><td colspan="4" class="number">' . _('Total Order Value Excluding Tax') .'</td>
	<td colspan="2" class="number">' . locale_money_format($OrderTotal,$OrderCurrCode) . '</td></tr>';
echo '<tr>
	<td colspan="4" class="number">' . _('Total Order Value Received Excluding Tax') . '</td>
	<td colspan="2" class="number">' . locale_money_format($RecdTotal,$OrderCurrCode) . '</td></tr>';
echo '</table>';

echo '<br />';

include ('includes/footer.inc');
?>