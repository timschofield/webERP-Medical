<?php

/* $Id: PDFStockTransfer.php 4545 2011-04-10 10:41:20Z daintree $*/

include('includes/session.inc');
//unset ($_SESSION['dispatch']);

if (!isset($_GET['TransferNo'])){
	if (isset($_POST['TransferNo'])){
		if (is_numeric($_POST['TransferNo'])){
			$_GET['TransferNo'] = $_POST['TransferNo'];
		} else {
			prnMsg(_('The entered transfer reference is expected to be numeric'),'error');
			unset($_POST['TransferNo']);
		}
	}
	if (!isset($_GET['TransferNo'])){ //still not set from a post then
	//open a form for entering a transfer number
		$title = _('Print an Internal Transfer Note');
		include('includes/header.inc');
		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print Transfer Note') . '" alt="" />' . ' ' . $title.'</p><br />';
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" name="form">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class="selection"><tr>';
		echo '<td>'._('Internal Transfer number to print').' : '.'</td>';
		echo '<td><input type=text class="number"  name="TransferNo" maxlength=10 size=11 /></td></tr>';
		echo '</table>';
		echo '<br><div class="centre"><button type="submit" name="Process">' . _('Print Transfer Note') . '</button></div></form><br />';
		include('includes/footer.inc');
		exit();
	}
}


include('includes/PDFStarter.php');
$pdf->addInfo('Title', _('Stock Transfer Form') );
$FontSize=10;
$PageNumber=1;
$line_height=12;

include('includes/PDFStockTransferHeader.inc');
$FontSize =10;

/*Print out the category totals */

$sql = "SELECT  stockmaster.stockid,
				stockmaster.description,
				dispatchitems.quantity,
				departments.description as namedepartments,
				loccode,despatchdate
			FROM dispatch
			INNER JOIN dispatchitems
				ON dispatch.dispatchid = dispatchitems.dispatchid
			INNER JOIN stockmaster
				ON stockmaster.stockid = dispatchitems.itemid
			INNER JOIN departments
				ON departments.departmentid=dispatch.departmentid
			WHERE dispatchitems.`dispatchid` ='".$_GET['TransferNo']."'";

$result=DB_query($sql, $db);
if (DB_num_rows($result) == 0){
	$title = _('Print Stock Transfer - Error');
	include ('includes/header.inc');
	prnMsg(_('There was no transfer found with number') . ': ' . $_GET['TransferNo'], 'error');
	echo '<a href="PDFStockTransfer_departments.php">' . _('Try Again') .'</a>';
	include ('includes/footer.inc');
	exit;
}
//get the first stock movement which will be the quantity taken from the initiating location
while ($myrow=DB_fetch_array($result)){
	//$myrow=DB_fetch_array($result);
	$StockID=$myrow['stockid'];
	$FromCode=$myrow['namedepartments'];
	$From = $myrow['loccode'];
	$Date=$myrow['despatchdate'];
	//get the next row which will be the quantity received in the receiving location
	//$myNextRow=DB_fetch_array($result);
	//$ToCode=$myNextRow['loccode'];
	$To = $myrow['namedepartments'];
	$Quantity=$myrow['quantity'];
	$Description=$myrow['description'];


	$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos-10,300-$Left_Margin,$FontSize, $StockID);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+75,$YPos-10,300-$Left_Margin,$FontSize, $Description);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos-10,300-$Left_Margin,$FontSize, $From);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos-10,300-$Left_Margin,$FontSize, $To);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+475,$YPos-10,300-$Left_Margin,$FontSize, $Quantity);

	$YPos-=20;
}

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-70,300-$Left_Margin,$FontSize, _('Date of transfer: '). ConvertSQLDate($Date));

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-120,300-$Left_Margin,$FontSize, _('Signed for ').$From.'______________________');
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-160,300-$Left_Margin,$FontSize, _('Signed for ').$To.'______________________');

$pdf->OutputD($_SESSION['DatabaseName'] . '_StockTransfer_' . date('Y-m-d') . '.pdf');
$pdf->__destruct();
?>