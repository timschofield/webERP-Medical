<?php
/* $Id$*/

include('includes/session.inc');
$PaperSize='T1_portrait';
include('includes/PDFStarter.php');

$FontSize=16;
$pdf->addInfo('Title', _('Sales Receipt') );

$PageNumber=1;
$line_height=17;
$FontSize=14;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
if ($_SESSION['ShowLogoOnReceipt']==1) {
	$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos,$YPos-30,0,60);
}

$sql="SELECT locationname, deladd1 FROM locations WHERE loccode='".$_SESSION['UserStockLocation']."'";
$result=DB_query($sql, $db);
$mylocationrow=DB_fetch_array($result);

$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*1),300,$FontSize,$mylocationrow['deladd1']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*2),300,$FontSize,$_SESSION['CompanyRecord']['regoffice1']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*3),300,$FontSize,$_SESSION['CompanyRecord']['regoffice2']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*4),300,$FontSize,$_SESSION['CompanyRecord']['regoffice3']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*5),300,$FontSize,$_SESSION['CompanyRecord']['regoffice4']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*6),300,$FontSize,$_SESSION['CompanyRecord']['regoffice5']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*7),300,$FontSize,$_SESSION['CompanyRecord']['regoffice6']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*8),150,$FontSize, _('Customer Receipt Number ').'  : ' . $_GET['FromTransNo'] );
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*9),150,$FontSize, _('Date ').'  : ' . date('l jS \of F Y h:i:s A') );
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*10),140,$FontSize, _('Cashier').': ' . $_SESSION['UsersRealName'] );
$NameYPos=$YPos-($line_height*12);
$sql="SELECT MIN(id) as start FROM debtortrans WHERE type=10 AND transno='".$_GET['FromTransNo']. "'";
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$StartReceiptNumber=$myrow['start'];

if ($_GET['InvOrCredit']=='Invoice') {
	$Type=10;
} else if ($_GET['InvOrCredit']=='Credit') {
	$Type=11;
} else {
	$Type=12;
}

$sql="SELECT 	debtortrans.debtorno,
				debtortrans.ovamount,
				debtortrans.invtext,
				debtortrans.alloc,
				salesorderdetails.stkcode,
				stockmaster.description,
				salesorderdetails.qtyinvoiced as quantity,
				salesorderdetails.units,
				salesorderdetails.unitprice
			FROM debtortrans
			LEFT JOIN salesorders
				ON salesorders.orderno=debtortrans.order_
			LEFT JOIN salesorderdetails
				ON salesorderdetails.orderno=debtortrans.order_
			LEFT JOIN stockmaster
				ON stockmaster.stockid=salesorderdetails.stkcode
			WHERE type='".$Type."'
			AND transno='".$_GET['FromTransNo']."'";
$MyOrderResult=DB_query($sql, $db);

$myrow=DB_fetch_array($MyOrderResult);
$DebtorNo=$myrow['debtorno'];
if (!isset($_GET['Amount'])) {
	$Amount=$myrow['alloc'];
} else {
	$Amount=-$_GET['Amount'];
}
$Narrative=$myrow['invtext'];
DB_data_seek($MyOrderResult, 0);
if ($Type!=12) {
	$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*11),140,$FontSize,$Narrative);
}

$YPos -= 170;

$YPos -=$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

//$pdf->line(20, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

$FontSize=14;
$YPos -= (1.5 * $line_height);

//$PageNumber++;

$sql="SELECT currency,
				currabrev,
				decimalplaces
					FROM currencies
					WHERE currabrev=(SELECT currcode
													FROM banktrans
													WHERE type=10
													AND transno='".$_GET['FromTransNo']."')";
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$Currency=$myrow['currency'];
$CurrCode=$myrow['currabrev'];
$DecimalPlaces=$myrow['decimalplaces'];
$sql="SELECT  name,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						currcode
			FROM debtorsmaster
			WHERE debtorno='".$DebtorNo."'";

$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);

$LeftOvers = $pdf->addTextWrap(0,$NameYPos,300,$FontSize,_('Received From').' : ');
$NameYPos=$NameYPos-($line_height*1);
$LeftOvers = $pdf->addTextWrap(0,$NameYPos,300,$FontSize, $DebtorNo.' '.htmlspecialchars_decode($myrow['name']));
/*
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*1),300,$FontSize, htmlspecialchars_decode($myrow['address1']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*2),300,$FontSize, htmlspecialchars_decode($myrow['address2']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*3),300,$FontSize, htmlspecialchars_decode($myrow['address3']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*4),300,$FontSize, htmlspecialchars_decode($myrow['address4']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*5),300,$FontSize, htmlspecialchars_decode($myrow['address5']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*6),300,$FontSize, htmlspecialchars_decode($myrow['address6']));

$YPos=$YPos-($line_height*8);
*/
if ($Type!=12) {
	while ($mylines=DB_fetch_array($MyOrderResult)) {

//		$YPos=$YPos-($line_height);
//		$LeftOvers = $pdf->addTextWrap(20,$YPos,300,$FontSize, htmlspecialchars_decode($mylines['stkcode']));
		$LeftOvers = $pdf->addTextWrap(0,$YPos,300,$FontSize, htmlspecialchars_decode($mylines['description']));
		$YPos=$YPos-($line_height);
		$LeftOvers = $pdf->addTextWrap(20,$YPos,300,$FontSize, htmlspecialchars_decode($mylines['quantity']) . ' @ ' . $mylines['unitprice']);
//		$LeftOvers = $pdf->addTextWrap(180,$YPos,300,$FontSize, htmlspecialchars_decode($mylines['unitprice']));
		$LeftOvers = $pdf->addTextWrap(100,$YPos,300,$FontSize, number_format($mylines['quantity']*$mylines['unitprice'],0).' '.$myrow['currcode']);
		$YPos=$YPos-($line_height);
		if ($YPos<=$Bottom_Margin) {
			$PageNumber++;
			$YPos= $Page_Height-$Top_Margin;
			$pdf->newPage();
		}
	}
} else {
		$YPos=$YPos-($line_height);
		$LeftOvers = $pdf->addTextWrap(0,$YPos,300,$FontSize, htmlspecialchars_decode(_('In Patient Deposit')));
}

$YPos=$YPos-($line_height*1);
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,_('Total received').' : ');
if ($Type!=12) {
	$LeftOvers = $pdf->addTextWrap(150,$YPos,200,$FontSize,number_format($Amount,$DecimalPlaces).'  '.$myrow['currcode']);
} else {
	$LeftOvers = $pdf->addTextWrap(150,$YPos,200,$FontSize,number_format(-$Amount,$DecimalPlaces).'  '.$myrow['currcode']);
}
$YPos=$YPos-($line_height*2);

$pdf->OutputD('Receipt-'.$_GET['FromTransNo'], 'I');
?>