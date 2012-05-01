<?php

/* $Id$*/

include('includes/session.inc');

if (!isset($_POST['FromCat'])  OR $_POST['FromCat']=='') {
	$title=_('Low Gross Profit Sales');
}
$debug=0;
if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Low Gross Profit Sales'));
	$pdf->addInfo('Subject', _('Low Gross Profit Sales'));
	$FontSize=10;
	$PageNumber=1;
	$line_height=12;

	$title = _('Low GP sales') . ' - ' . _('Problem Report');

	if (! Is_Date($_POST['FromDate']) OR ! Is_Date($_POST['ToDate'])){
		include('includes/header.inc');
		prnMsg(_('The dates entered must be in the format') . ' '  . $_SESSION['DefaultDateFormat'],'error');
		include('includes/footer.inc');
		exit;
	}

	  /*Now figure out the data to report for the category range under review */
	$SQL = "SELECT stockmaster.categoryid,
						stockmaster.stockid,
						stockmoves.transno,
						stockmoves.trandate,
						systypes.typename,
						stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost as unitcost,
						stockmoves.qty,
						stockmoves.debtorno,
						stockmoves.branchcode,
						stockmoves.price*(1-stockmoves.discountpercent) as sellingprice,
						(stockmoves.price*(1-stockmoves.discountpercent)) - (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS gp,
						debtorsmaster.name,
						debtorsmaster.currcode
				FROM stockmaster,
						stockmoves,
						systypes,
						debtorsmaster
				WHERE stockmoves.type=systypes.typeid
				AND stockmaster.stockid=stockmoves.stockid
				AND stockmoves.trandate >= '" . FormatDateForSQL($_POST['FromDate']) . "'
				AND stockmoves.trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'
				AND ((stockmoves.price*(1-stockmoves.discountpercent)) - (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))/(stockmoves.price*(1-stockmoves.discountpercent)) <=" . (filter_number_input($_POST['GPMin']/100)) . "
				AND stockmoves.debtorno=debtorsmaster.debtorno
				ORDER BY stockmaster.stockid";

	$LowGPSalesResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {

	  include('includes/header.inc');
		prnMsg(_('The low GP items could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
		echo '<br /><a href="' .$rootpath .'/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
		  echo '<br />'.$SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	if (DB_num_rows($LowGPSalesResult) == 0) {

		include('includes/header.inc');
		prnMsg(_('No low GP items retrieved'), 'warn');
		echo '<br /><a href="'  . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
		  echo '<br />'.$SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	include ('includes/PDFLowGPPageHeader.inc');
	$Tot_Val=0;
	$Category = '';
	$CatTot_Val=0;
	while ($LowGPItems = DB_fetch_array($LowGPSalesResult,$db)){

		$YPos -=$line_height;
		$FontSize=8;

		$LeftOvers = $pdf->addTextWrap($Left_Margin+2,$YPos,30,$FontSize,$LowGPItems['typename']);
		$LeftOvers = $pdf->addTextWrap(100,$YPos,30,$FontSize,$LowGPItems['transno']);
		$LeftOvers = $pdf->addTextWrap(130,$YPos,50,$FontSize,$LowGPItems['stockid']);
		$LeftOvers = $pdf->addTextWrap(220,$YPos,50,$FontSize,$LowGPItems['name']);
		$DisplayUnitCost = locale_money_format($LowGPItems['unitcost'],$LowGPItems['currcode']);
		$DisplaySellingPrice = locale_money_format($LowGPItems['sellingprice'],$LowGPItems['currcode']);
		$DisplayGP = locale_money_format($LowGPItems['gp'],$LowGPItems['currcode']);
		$DisplayGPPercent = locale_money_format(($LowGPItems['gp']*100)/$LowGPItems['sellingprice'],$LowGPItems['currcode']);

		$LeftOvers = $pdf->addTextWrap(330,$YPos,60,$FontSize,$DisplaySellingPrice,'right');
		$LeftOvers = $pdf->addTextWrap(380,$YPos,62,$FontSize,$DisplayUnitCost, 'right');
		$LeftOvers = $pdf->addTextWrap(440,$YPos,60,$FontSize,$DisplayGP, 'right');
		$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayGPPercent . '%', 'right');

		if ($YPos < $Bottom_Margin + $line_height){
			include('includes/PDFLowGPPageHeader.inc');
		}

	} /*end low GP items while loop */

	$FontSize =10;

	$YPos -= (2*$line_height);
	$pdf->OutputD($_SESSION['DatabaseName'] . '_LowGPSales_' . date('Y-m-d') . '.pdf');//UldisN
	$pdf->__destruct(); //UldisN

} else { /*The option to print PDF was not hit */

	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . $title . '" alt="" />' . ' '
		. _('Low Gross Profit Report') . '</p>';

	if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate'])) {

	/*if $FromDate is not set then show a form to allow input */
		$_POST['FromDate']=Date($_SESSION['DefaultDateFormat']);
		$_POST['ToDate']=Date($_SESSION['DefaultDateFormat']);
		$_POST['GPMin']=0;
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<table class="selection"><tr><td>' . _('Sales Made From') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</td>
								<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="FromDate" size="10" maxlength="10" value="' . $_POST['FromDate'] . '" /></td>
						</tr>';

		echo '<tr><td>' . _('Sales Made To') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</td>
								<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="ToDate" size="10" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
						</tr>';

		echo '<tr><td>' . _('Show sales with GP') . ' % ' . _('below') . ':</td>
								<td><input type="text" class="number" name="GPMin" maxlength="6" size="6" value="' . locale_number_format($_POST['GPMin'],2) . '" />&nbsp;%</td>
						</tr>';

		echo '</table><br /><div class="centre"><button type="submit" name="PrintPDF">' . _('Print PDF') . '</button></div><br />';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>