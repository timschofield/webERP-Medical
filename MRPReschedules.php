<?php
// MRPReschedules.php - Report of purchase orders and work orders that MRP determines should be
// rescheduled.

include('includes/session.php');

if ( !DB_table_exists('mrprequirements') ) {
	$Title='MRP error';
	include('includes/header.php');
	echo '<br />';
	prnMsg( _('The MRP calculation must be run before you can run this report') . '<br />' .
			_('To run the MRP calculation click').' ' . '<a href="'.$RootPath .'/MRP.php">' . _('here') . '</a>', 'error');
	include('includes/footer.php');
	exit;
}
if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('MRP Reschedule Report'));
	$pdf->addInfo('Subject',_('MRP Reschedules'));
	$FontSize=9;
	$PageNumber=1;
	$line_height=12;

/*Find mrpsupplies records where the duedate is not the same as the mrpdate */
	$selecttype = " ";
	if ($_POST['Selection'] != 'All') {
		 $selecttype = " AND ordertype = '" . $_POST['Selection'] . "'";
	 }
	$sql = "SELECT mrpsupplies.*,
				   stockmaster.description,
				   stockmaster.decimalplaces
			  FROM mrpsupplies,stockmaster
			  WHERE mrpsupplies.part = stockmaster.stockid AND duedate <> mrpdate
				 $selecttype
			  ORDER BY mrpsupplies.part";
	$result = DB_query($sql,'','',false,true);

	if (DB_error_no() !=0) {
	  $Title = _('MRP Reschedules') . ' - ' . _('Problem Report');
	  include('includes/header.php');
	   prnMsg( _('The MRP reschedules could not be retrieved by the SQL because') . ' '  . DB_error_msg(),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
		  echo '<br />' . $sql;
	   }
	   include('includes/footer.php');
	   exit;
	}

	if (DB_num_rows($result) == 0) {
	  $Title = _('MRP Reschedules') . ' - ' . _('Problem Report');
	  include('includes/header.php');
	   prnMsg( _('No MRP reschedule retrieved'), 'warn');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
		echo '<br />' . $sql;
	   }
	   include('includes/footer.php');
	   exit;
	}

	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
					   $Right_Margin);
	$Tot_Val=0;
	$fill = false;
	$pdf->SetFillColor(224,235,255);
	While ($myrow = DB_fetch_array($result)){

		$YPos -=$line_height;
		$FontSize=8;

		$FormatedDueDate = ConvertSQLDate($myrow['duedate']);
		$FormatedMRPDate = ConvertSQLDate($myrow['mrpdate']);
		if ($myrow['mrpdate'] == '2050-12-31') {
			$FormatedMRPDate = 'Cancel';
		}

		// Use to alternate between lines with transparent and painted background
		if ($_POST['Fill'] == 'yes'){
			$fill=!$fill;
		}

		// Parameters for addTextWrap are defined in /includes/class.pdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set to transparent
		$pdf->addTextWrap($Left_Margin,$YPos,90,$FontSize,$myrow['part'],'',0,$fill);
		$pdf->addTextWrap(130,$YPos,200,$FontSize,$myrow['description'],'',0,$fill);
		$pdf->addTextWrap(330,$YPos,50,$FontSize,$myrow['orderno'],'right',0,$fill);
		$pdf->addTextWrap(380,$YPos,30,$FontSize,$myrow['ordertype'],'right',0,$fill);
		$pdf->addTextWrap(410,$YPos,50,$FontSize,locale_number_format($myrow['supplyquantity'], $myrow['decimalplaces']),'right',0,$fill);
		$pdf->addTextWrap(460,$YPos,55,$FontSize,$FormatedDueDate,'right',0,$fill);
		$pdf->addTextWrap(515,$YPos,50,$FontSize,$FormatedMRPDate,'right',0,$fill);

		if ($YPos < $Bottom_Margin + $line_height){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
					   $Right_Margin);
		}

	} /*end while loop */

	$FontSize =10;
	$YPos -= (2*$line_height);

	if ($YPos < $Bottom_Margin + $line_height){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
					   $Right_Margin);
	}

	$pdf->OutputD($_SESSION['DatabaseName'] . '_MRPReschedules_' . date('Y-m-d').'.pdf');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit so display form */

	$Title=_('MRP Reschedule Reporting');
	$ViewTopic = 'MRP';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="'
		. _('Stock') . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
		<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="Fill">' . _('Print Option') . ':</label>
			<select name="Fill">
				<option selected="selected" value="yes">' . _('Print With Alternating Highlighted Lines') . '</option>
				<option value="no">' . _('Plain Print') . '</option>
			</select>
		</field>
		<field>
			<label for="Selection">' . _('Selection') . ':</label>
			<select name="Selection">
				<option selected="selected" value="All">' . _('All') . '</option>
				<option value="WO">' . _('Work Orders Only') . '</option>
				<option value="PO">' . _('Purchase Orders Only') . '</option>
			</select>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>
		</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */


function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin) {

$line_height=12;
/*PDF page header for MRP Reschedule report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=9;
$YPos= $Page_Height-$Top_Margin;

$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$YPos -=$line_height;

$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('MRP Reschedule Report'));
$pdf->addTextWrap($Page_Width-$Right_Margin-115,$YPos,160,$FontSize,_('Printed') . ': ' .
	 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber);
$YPos -=$line_height;
$pdf->addTextWrap($Left_Margin,$YPos,70,$FontSize,_('Selection:'));
$pdf->addTextWrap(90,$YPos,15,$FontSize,$_POST['Selection']);

$YPos -=(2*$line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;

$pdf->addTextWrap($Xpos,$YPos,135,$FontSize,_('Part Number'), 'left');
$pdf->addTextWrap(135,$YPos,195,$FontSize,_('Description'), 'left');
$pdf->addTextWrap(330,$YPos,50,$FontSize,_('Order No.'), 'right');
$pdf->addTextWrap(380,$YPos,35,$FontSize,_('Type'), 'right');
$pdf->addTextWrap(415,$YPos,45,$FontSize,_('Quantity'), 'right');
$pdf->addTextWrap(460,$YPos,55,$FontSize,_('Order Date'), 'right');
$pdf->addTextWrap(515,$YPos,50,$FontSize,_('MRP Date'), 'right');

$FontSize=8;
$YPos =$YPos - (2*$line_height);
$PageNumber++;
} // End of PrintHeader function
?>
