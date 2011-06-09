<?php
/* $Id$*/

//$PageSecurity = 2;
include('includes/session.inc');
$PaperSize='T1_portrait';
include('includes/PDFStarter.php');

$FontSize=16;
$pdf->addInfo('Title', _('Sales Receipt') );

$PageNumber=1;
$line_height=17;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=16;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos,$YPos,0,30);


$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*3),300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*4),300,$FontSize,$_SESSION['CompanyRecord']['regoffice1']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*5),300,$FontSize,$_SESSION['CompanyRecord']['regoffice2']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*6),300,$FontSize,$_SESSION['CompanyRecord']['regoffice3']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*7),300,$FontSize,$_SESSION['CompanyRecord']['regoffice4']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*8),300,$FontSize,$_SESSION['CompanyRecord']['regoffice5']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*9),300,$FontSize,$_SESSION['CompanyRecord']['regoffice6']);
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*10),150,$FontSize, _('Customer Receipt Number ').'  : ' . $_GET['FromTransNo'] );
//$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*14),140,$FontSize, _('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. _('Page'). ' ' . $PageNumber);

$sql="SELECT MIN(id) as start FROM debtortrans WHERE type=10 AND transno='".$_GET['FromTransNo']. "'";
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$StartReceiptNumber=$myrow['start'];

$sql="SELECT 	debtortrans.debtorno,
				debtortrans.ovamount,
				debtortrans.invtext,
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
			WHERE type=10
			AND transno='".$_GET['FromTransNo']."'";
$MyOrderResult=DB_query($sql, $db);

$sql="SELECT 	debtortrans.debtorno,
				debtortrans.ovamount,
				debtortrans.invtext
			FROM debtortrans
			WHERE type=10
			AND transno='".$_GET['FromTransNo']."'";
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$DebtorNo=$myrow['debtorno'];
$Amount=$myrow['ovamount'];
$Narrative=$myrow['invtext'];
$LeftOvers = $pdf->addTextWrap(0,$YPos-($line_height*14),140,$FontSize,$Narrative);

$YPos -= 170;

$YPos -=$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line(20, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

$FontSize=16;
$YPos -= (1.5 * $line_height);

$PageNumber++;

$sql="SELECT currency,
						decimalplaces
					FROM currencies
					WHERE currabrev=(SELECT currcode
													FROM banktrans
													WHERE type=10
													AND transno='".$_GET['FromTransNo']."')";
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$Currency=$myrow['currency'];
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

/*
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,_('Received From').' : ');

$LeftOvers = $pdf->addTextWrap(150,$YPos,300,$FontSize, htmlspecialchars_decode($myrow['name']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*1),300,$FontSize, htmlspecialchars_decode($myrow['address1']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*2),300,$FontSize, htmlspecialchars_decode($myrow['address2']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*3),300,$FontSize, htmlspecialchars_decode($myrow['address3']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*4),300,$FontSize, htmlspecialchars_decode($myrow['address4']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*5),300,$FontSize, htmlspecialchars_decode($myrow['address5']));
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*6),300,$FontSize, htmlspecialchars_decode($myrow['address6']));

$YPos=$YPos-($line_height*8);
*/
while ($mylines=DB_fetch_array($MyOrderResult)) {
	$YPos=$YPos-($line_height);
//	$LeftOvers = $pdf->addTextWrap(20,$YPos,300,$FontSize, htmlspecialchars_decode($mylines['stkcode']));
	$LeftOvers = $pdf->addTextWrap(0,$YPos,300,$FontSize, htmlspecialchars_decode($mylines['description']));
	$YPos=$YPos-($line_height);
	$LeftOvers = $pdf->addTextWrap(20,$YPos,300,$FontSize, htmlspecialchars_decode($mylines['quantity']) . ' @ ' . $mylines['unitprice']);
//	$LeftOvers = $pdf->addTextWrap(180,$YPos,300,$FontSize, htmlspecialchars_decode($mylines['unitprice']));
	$LeftOvers = $pdf->addTextWrap(100,$YPos,300,$FontSize, number_format($mylines['quantity']*$mylines['unitprice'],0).' '.$myrow['currcode']);
	$YPos=$YPos-($line_height);
}

$YPos=$YPos-($line_height*2);
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,_('Total received').' : ');
$LeftOvers = $pdf->addTextWrap(150,$YPos,300,$FontSize,number_format($Amount,$DecimalPlaces).'  '.$myrow['currcode']);

$YPos=$YPos-($line_height*2);

$pdf->OutputD('Receipt-'.$_GET['FromTransNo'], 'I');
?>