<?php
/* $Id$ */
include ('includes/session.php');
if (isset($_GET['FromTransNo'])) {
	$FromTransNo = trim($_GET['FromTransNo']);
} elseif (isset($_POST['FromTransNo'])) {
	$FromTransNo = trim($_POST['FromTransNo']);
} else {
	$FromTransNo = '';
}
if (isset($_GET['InvOrCredit'])) {
	$InvOrCredit = $_GET['InvOrCredit'];
} elseif (isset($_POST['InvOrCredit'])) {
	$InvOrCredit = $_POST['InvOrCredit'];
}
if (isset($_GET['PrintPDF'])) {
	$PrintPDF = true;
} elseif (isset($_POST['PrintPDF'])) {
	$PrintPDF = true;
}
if (!isset($_POST['ToTransNo']) or trim($_POST['ToTransNo']) == '' or $_POST['ToTransNo'] < $FromTransNo) {
	$_POST['ToTransNo'] = $FromTransNo;
}
$FirstTrans = $FromTransNo;
/* Need to start a new page only on subsequent transactions */
if ($FromTransNo == 'Preview') {
	$FormDesign = simplexml_load_file(sys_get_temp_dir() . '/SalesInvoice.xml');
} else {
	$FormDesign = simplexml_load_file($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/SalesInvoice.xml');
}
if (isset($PrintPDF) or isset($_GET['PrintPDF']) and $PrintPDF and isset($FromTransNo) and isset($InvOrCredit) and $FromTransNo != '') {
	$PaperSize = $FormDesign->PaperSize;
	include ('includes/PDFStarter.php');
	// Javier: now I use the native constructor, better to not use references
	if ($InvOrCredit == 'Invoice') {
		$PDF->addInfo('Title', _('Sales Invoice') . ' ' . $FromTransNo . ' to ' . $_POST['ToTransNo']);
		$PDF->addInfo('Subject', _('Invoices from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	} else {
		$PDF->addInfo('Title', _('Sales Credit Note'));
		$PDF->addInfo('Subject', _('Credit Notes from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	}
	/* Javier: I have brought this piece from the pdf class constructor to get it closer to the admin/user,
	I corrected it to match TCPDF, but it still needs some check, after which,
	I think it should be moved to each report to provide flexible Document Header and Margins in a per-report basis. */
	/* END Brought from class.pdf.php constructor */
	//	$PDF->selectFont('helvetica');
	$FirstPage = true;
	$line_height = $FormDesign->LineHeight;
	while ($FromTransNo <= $_POST['ToTransNo']) {
		/* retrieve the invoice details from the database to print
		notice that salesorder record must be present to print the invoice purging of sales orders will
		nobble the invoice reprints */
		if ($InvOrCredit == 'Invoice') {
			$SQL = "SELECT debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					debtorsmaster.invaddrbranch,
					debtorsmaster.taxref,
					paymentterms.terms,
					salesorders.deliverto,
					salesorders.deladd1,
					salesorders.deladd2,
					salesorders.deladd3,
					salesorders.deladd4,
					salesorders.deladd5,
					salesorders.deladd6,
					salesorders.customerref,
					salesorders.orderno,
					salesorders.orddate,
					locations.locationname,
					shippers.shippername,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					salesman.salesmanname,
					debtortrans.debtorno,
					debtortrans.branchcode
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesorders,
					shippers,
					salesman,
					locations,
					paymentterms
				WHERE debtortrans.order_ = salesorders.orderno
				AND debtortrans.type=10
				AND debtortrans.transno='" . $FromTransNo . "'
				AND debtortrans.shipvia=shippers.shipper_id
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtorsmaster.paymentterms=paymentterms.termsindicator
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode
				AND salesorders.fromstkloc=locations.loccode";
			if (isset($_POST['PrintEDI']) and $_POST['PrintEDI'] == 'No') {
				$SQL = $SQL . " AND debtorsmaster.ediinvoices=0";
			}
		} else {
			$SQL = "SELECT debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtorsmaster.invaddrbranch,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					debtorsmaster.taxref,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					salesman.salesmanname,
					debtortrans.debtorno,
					debtortrans.branchcode,
					paymentterms.terms
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesman,
					paymentterms
				WHERE debtortrans.type=11
				AND debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtortrans.transno='" . $FromTransNo . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode";
			if ($_POST['PrintEDI'] == 'No') {
				$SQL = $SQL . ' AND debtorsmaster.ediinvoices=0';
			}
		} // end else
		if ($FromTransNo != 'Preview') {
			$Result = DB_query($SQL, '', '', false, false);
			if (DB_error_no() != 0) {
				$Title = _('Transaction Print Error Report');
				include ('includes/header.php');
				prnMsg(_('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available'), 'error');
				if ($debug == 1) {
					prnMsg(_('The SQL used to get this information that failed was') . "<br />" . $SQL, 'error');
				}
				include ('includes/footer.php');
				exit;
			}
		}
		if ($FromTransNo == 'Preview' or DB_num_rows($Result) == 1) {
			if ($FromTransNo != 'Preview') {
				$MyRow = DB_fetch_array($Result);
				$ExchRate = $MyRow['rate'];
			} else {
				$ExchRate = 'X';
			}
			if ($InvOrCredit == 'Invoice') {
				$SQL = "SELECT stockmoves.stockid,
						stockmaster.description,
						-stockmoves.qty*stockmoves.conversionfactor as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmoves.units,
						stockmoves.conversionfactor,
						stockmaster.decimalplaces
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=10
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
			} else {
				/* only credit notes to be retrieved */
				$SQL = "SELECT stockmoves.stockid,
						stockmaster.description,
						stockmoves.qty*stockmoves.conversionfactor as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmoves.units,
						stockmoves.conversionfactor,
						stockmaster.decimalplaces
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=11
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
			} // end else
			if ($FromTransNo != 'Preview') {
				$Result = DB_query($SQL);
			}
			if (DB_error_no() != 0) {
				$Title = _('Transaction Print Error Report');
				include ('includes/header.php');
				echo '<br />' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
				if ($debug == 1) {
					echo '<br />' . _('The SQL used to get this information that failed was') . "<br />$SQL";
				}
				include ('includes/footer.php');
				exit;
			}
			if ($FromTransNo == 'Preview' or DB_num_rows($Result) > 0) {
				$PageNumber = 1;
				include ('includes/PDFTransPageHeader.php');
				$FirstPage = False;
				$YPos = $Page_Height - $FormDesign->Data->y;
				$Line = 1;
				while (($FromTransNo == 'Preview' and $Line == 1) or (isset($Result) and $MyRow2 = DB_fetch_array($Result))) {
					if ($MyRow2['discountpercent'] == 0) {
						$DisplayDiscount = '';
					} else {
						$DisplayDiscount = number_format($MyRow2['discountpercent'] * 100, 2) . '%';
						$DiscountPrice = $MyRow2['fxprice'] * (1 - $MyRow2['discountpercent']);
					}
					$DisplayNet = number_format($MyRow2['fxnet'], 2);
					$DisplayPrice = $MyRow2['fxprice'];
					$DisplayQty = $MyRow2['quantity'];
					$LeftOvers = $PDF->addTextWrap($FormDesign->Data->Column1->x, $YPos, $FormDesign->Data->Column1->Length, $FormDesign->Data->Column1->FontSize, $MyRow2['stockid']);
					$LeftOvers = $PDF->addTextWrap($FormDesign->Data->Column2->x, $YPos, $FormDesign->Data->Column2->Length, $FormDesign->Data->Column2->FontSize, $MyRow2['description']);
					$LeftOvers = $PDF->addTextWrap($FormDesign->Data->Column3->x, $YPos, $FormDesign->Data->Column3->Length, $FormDesign->Data->Column3->FontSize, number_format($DisplayPrice, 4), 'right');
					$LeftOvers = $PDF->addTextWrap($FormDesign->Data->Column4->x, $YPos, $FormDesign->Data->Column4->Length, $FormDesign->Data->Column4->FontSize, number_format($DisplayQty, $MyRow2['decimalplaces']), 'right');
					$LeftOvers = $PDF->addTextWrap($FormDesign->Data->Column5->x, $YPos, $FormDesign->Data->Column5->Length, $FormDesign->Data->Column5->FontSize, $MyRow2['units'], 'centre');
					$LeftOvers = $PDF->addTextWrap($FormDesign->Data->Column6->x, $YPos, $FormDesign->Data->Column6->Length, $FormDesign->Data->Column6->FontSize, $DisplayDiscount, 'right');
					$LeftOvers = $PDF->addTextWrap($FormDesign->Data->Column7->x, $YPos, $FormDesign->Data->Column7->Length, $FormDesign->Data->Column7->FontSize, $DisplayNet, 'right');
					$YPos-= ($line_height);
					$lines = explode('\r\n', htmlspecialchars_decode($MyRow2['narrative']));
					for ($i = 0;$i < sizeOf($lines);$i++) {
						while (strlen($lines[$i]) > 1) {
							if ($YPos - $line_height <= $Bottom_Margin) {
								/* head up a new invoice/credit note page */
								/* draw the vertical column lines right to the bottom */
								PrintLinesToBottom($PDF, $Page_Height, $PageNumber, $FormDesign);
								include ('includes/PDFTransPageHeaderPortrait.php');
							} //end if need a new page headed up
							/* increment a line down for the next line item */
							if (strlen($lines[$i]) > 1) {
								$lines[$i] = $PDF->addTextWrap($Left_Margin + 100, $YPos, 245, $FontSize, stripslashes($lines[$i]));
							}
							$YPos-= ($line_height);
						}
					}
					if ($YPos <= $Bottom_Margin) {
						/* head up a new invoice/credit note page */
						/*draw the vertical column lines right to the bottom */
						PrintLinesToBottom($PDF, $Page_Height, $PageNumber, $FormDesign);
						include ('includes/PDFTransPageHeader.php');
					} //end if need a new page headed up
					$Line++;

				} //end while there are line items to print out
				
			}
			/*end if there are stock movements to show on the invoice or credit note*/
			$YPos-= $line_height;
			/* check to see enough space left to print the 4 lines for the totals/footer */
			if (($YPos - $Bottom_Margin) < (2 * $line_height)) {
				PrintLinesToBottom($PDF, $Page_Height, $PageNumber, $FormDesign);
				include ('includes/PDFTransPageHeader.php');
			}
			/* Print a column vertical line  with enough space for the footer */
			/* draw the vertical column lines to 4 lines shy of the bottom to leave space for invoice footer info ie totals etc */
			$PDF->line($FormDesign->DataLines->Line1->startx, $Page_Height - $FormDesign->DataLines->Line1->starty, $FormDesign->DataLines->Line1->endx, $Page_Height - $FormDesign->DataLines->Line1->endy);
			/* Print a column vertical line */
			$PDF->line($FormDesign->DataLines->Line2->startx, $Page_Height - $FormDesign->DataLines->Line2->starty, $FormDesign->DataLines->Line2->endx, $Page_Height - $FormDesign->DataLines->Line2->endy);
			/* Print a column vertical line */
			$PDF->line($FormDesign->DataLines->Line3->startx, $Page_Height - $FormDesign->DataLines->Line3->starty, $FormDesign->DataLines->Line3->endx, $Page_Height - $FormDesign->DataLines->Line3->endy);
			/* Print a column vertical line */
			$PDF->line($FormDesign->DataLines->Line4->startx, $Page_Height - $FormDesign->DataLines->Line4->starty, $FormDesign->DataLines->Line4->endx, $Page_Height - $FormDesign->DataLines->Line4->endy);
			/* Print a column vertical line */
			$PDF->line($FormDesign->DataLines->Line5->startx, $Page_Height - $FormDesign->DataLines->Line5->starty, $FormDesign->DataLines->Line5->endx, $Page_Height - $FormDesign->DataLines->Line5->endy);
			$PDF->line($FormDesign->DataLines->Line6->startx, $Page_Height - $FormDesign->DataLines->Line6->starty, $FormDesign->DataLines->Line6->endx, $Page_Height - $FormDesign->DataLines->Line6->endy);
			/* Rule off at bottom of the vertical lines */
			$PDF->line($FormDesign->LineAboveFooter->startx, $Page_Height - $FormDesign->LineAboveFooter->starty, $FormDesign->LineAboveFooter->endx, $Page_Height - $FormDesign->LineAboveFooter->endy);
			/* Now print out the footer and totals */
			if ($InvOrCredit == 'Invoice') {
				$DisplaySubTot = number_format($MyRow['ovamount'], 2);
				$DisplayFreight = number_format($MyRow['ovfreight'], 2);
				$DisplayTax = number_format($MyRow['ovgst'], 2);
				$DisplayTotal = number_format($MyRow['ovfreight'] + $MyRow['ovgst'] + $MyRow['ovamount'], 2);
			} else {
				$DisplaySubTot = number_format(-$MyRow['ovamount'], 2);
				$DisplayFreight = number_format(-$MyRow['ovfreight'], 2);
				$DisplayTax = number_format(-$MyRow['ovgst'], 2);
				$DisplayTotal = number_format(-$MyRow['ovfreight'] - $MyRow['ovgst'] - $MyRow['ovamount'], 2);
			}
			/* Print out the payment terms */
			$PDF->addTextWrap($FormDesign->PaymentTerms->x, $Page_Height - $FormDesign->PaymentTerms->y, $FormDesign->PaymentTerms->Length, $FormDesign->PaymentTerms->FontSize, _('Payment Terms') . ': ' . $MyRow['terms']);
			//      $PDF->addText($Page_Width-$Right_Margin-392, $YPos - ($line_height*3)+22,$FontSize, _('Bank Code:***** Bank Account:*****'));
			//	$FontSize=10;
			$LeftOvers = explode('\r\n', DB_escape_string($MyRow['invtext']));
			for ($i = 0;$i < sizeOf($LeftOvers);$i++) {
				$PDF->addText($FormDesign->InvoiceText->x, $Page_Height - $FormDesign->InvoiceText->y - ($i * 10), $FormDesign->InvoiceText->FontSize, $LeftOvers[$i]);
			}
			$PDF->addText($FormDesign->SubTotalCaption->x, $Page_Height - $FormDesign->SubTotalCaption->y, $FormDesign->SubTotalCaption->FontSize, _('Sub Total'));
			$LeftOvers = $PDF->addTextWrap($FormDesign->SubTotal->x, $Page_Height - $FormDesign->SubTotal->y, $FormDesign->SubTotal->Length, $FormDesign->SubTotal->FontSize, $DisplaySubTot, 'right');
			$PDF->addText($FormDesign->FreightCaption->x, $Page_Height - $FormDesign->FreightCaption->y, $FormDesign->FreightCaption->FontSize, _('Freight'));
			$LeftOvers = $PDF->addTextWrap($FormDesign->Freight->x, $Page_Height - $FormDesign->Freight->y, $FormDesign->Freight->Length, $FormDesign->Freight->FontSize, $DisplayFreight, 'right');
			$PDF->addText($FormDesign->TaxCaption->x, $Page_Height - $FormDesign->TaxCaption->y, $FormDesign->TaxCaption->FontSize, _('Tax'));
			$LeftOvers = $PDF->addTextWrap($FormDesign->Tax->x, $Page_Height - $FormDesign->Tax->y, $FormDesign->Tax->Length, $FormDesign->Tax->FontSize, $DisplayTax, 'right');
			/*rule off for total */
			$PDF->line($FormDesign->TotalLine->startx, $Page_Height - $FormDesign->TotalLine->starty, $FormDesign->TotalLine->endx, $Page_Height - $FormDesign->TotalLine->endy);
			/*vertical to separate totals from comments and ROMALPA */
			$PDF->line($FormDesign->RomalpaLine->startx, $Page_Height - $FormDesign->RomalpaLine->starty, $FormDesign->RomalpaLine->endx, $Page_Height - $FormDesign->RomalpaLine->endy);
			if ($InvOrCredit == 'Invoice') {
				$PDF->addText($FormDesign->TotalCaption->x, $Page_Height - $FormDesign->TotalCaption->y, $FormDesign->TotalCaption->FontSize, _('TOTAL INVOICE'));
				$YPos = $FormDesign->Romalpa->y;
				$LeftOvers = $PDF->addTextWrap($FormDesign->Romalpa->x, $Page_Height - $YPos, $FormDesign->Romalpa->Length, $FormDesign->Romalpa->FontSize, $_SESSION['RomalpaClause']);
				while (strlen($LeftOvers) > 0 and ($Page_Height - $YPos) > $Bottom_Margin) {
					$YPos+= $FormDesign->Romalpa->FontSize + 1;
					$LeftOvers = $PDF->addTextWrap($FormDesign->Romalpa->x, $Page_Height - $YPos, $FormDesign->Romalpa->Length, $FormDesign->Romalpa->FontSize, $LeftOvers);
				}
				/* Add Images for Visa / Mastercard / Paypal */
				if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg')) {
					$PDF->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg', $FormDesign->CreditCardLogo->x, $Page_Height - $FormDesign->CreditCardLogo->y, $FormDesign->CreditCardLogo->width, $FormDesign->CreditCardLogo->height);
				}
				//				$PDF->addText($Page_Width - $Right_Margin - 472, $YPos - ($line_height * 3) + 32, $FontSize, '');
				
			} else {
				$PDF->addText($FormDesign->TotalCaption->x, $Page_Height - $FormDesign->TotalCaption->y, $FormDesign->TotalCaption->FontSize, _('TOTAL CREDIT'));
			}
			$LeftOvers = $PDF->addTextWrap($FormDesign->Total->x, $Page_Height - $FormDesign->Total->y, $FormDesign->Total->Length, $FormDesign->Total->FontSize, $DisplayTotal, 'right');
		}
		/* end of check to see that there was an invoice record to print */
		$FromTransNo++;
	}
}
/* end loop to print invoices */

if (($InvOrCredit == 'Invoice' or $InvOrCredit == 'Credit') and isset($PrintPDF)) {

	if (isset($_GET['Email'])) { //email the invoice to address supplied
		include ('includes/header.php');
		include ('includes/htmlMimeMail.php');
		$mail = new htmlMimeMail();
		$FileName = $_SESSION['reports_dir'] . '/' . $_SESSION['DatabaseName'] . '_' . $InvOrCredit . '_' . $_GET['FromTransNo'] . '.pdf';
		$PDF->Output($FileName, 'F');
		$Attachment = $mail->getFile($FileName);
		$mail->setText(_('Please find attached') . ' ' . $InvOrCredit . ' ' . $_GET['FromTransNo']);
		$mail->SetSubject($InvOrCredit . ' ' . $_GET['FromTransNo']);
		$mail->addAttachment($Attachment, $FileName, 'application/pdf');
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
		$Result = $mail->send(array($_GET['Email']));
		unlink($FileName); //delete the temporary file
		$Title = _('Emailing') . ' ' . $InvOrCredit . ' ' . _('Number') . ' ' . $FromTransNo;
		include ('includes/header.php');
		echo '<br />' . $InvOrCredit . ' ' . _('number') . ' ' . $_GET['FromTransNo'] . ' ' . _('has been emailed to') . ' ' . $_GET['Email'];
		include ('includes/footer.php');
		exit;
	} else {
		$PDF->OutputD($_SESSION['DatabaseName'] . '_' . $InvOrCredit . '_' . $_GET['FromTransNo'] . '.pdf');
	}
	$PDF->__destruct();
} else {
	/*The option to print PDF was not hit */
	$Title = _('Select Invoices/Credit Notes To Print');
	include ('includes/header.php');
	if (!isset($FromTransNo) or $FromTransNo == '') {
		/* if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */
		echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST"><table class="selection">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . _('Print Invoices or Credit Notes (Landscape Mode)') . '</p>';
		echo '<tr><td>' . _('Print Invoices or Credit Notes') . '</td><td><select name=InvOrCredit>';
		if ($InvOrCredit == 'Invoice' or !isset($InvOrCredit)) {
			echo '<option selected value="Invoice">' . _('Invoices') . '</option>';
			echo '<option value="Credit">' . _('Credit Notes') . '</option>';
		} else {
			echo '<option selected value="Credit">' . _('Credit Notes') . '</option>';
			echo '<option value="Invoice">' . _('Invoices') . '</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>' . _('Print EDI Transactions') . '</td><td><select name=PrintEDI>';
		if ($InvOrCredit == 'Invoice' or !isset($InvOrCredit)) {
			echo '<option selected value="No">' . _('Do not Print PDF EDI Transactions') . '</option>';
			echo '<option value="Yes">' . _('Print PDF EDI Transactions Too') . '</option>';
		} else {
			echo '<option value="No">' . _('Do not Print PDF EDI Transactions') . '</option>';
			echo '<option selected value="Yes">' . _('Print PDF EDI Transactions Too') . '</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td>' . _('Start invoice/credit note number to print') . '</td><td><input Type=text class=number max=6 size=7 name=FromTransNo></td></tr>';
		echo '<tr><td>' . _('End invoice/credit note number to print') . '</td><td><input Type=text class=number max=6 size=7 name="ToTransNo"></td></tr></table>';
		echo '<br /><div class="centre"><input type=Submit Name="Print" Value="' . _('Print') . '"><br />';
		echo '<input type=Submit Name="PrintPDF" Value="' . _('Print PDF') . '"></div>';
		$SQL = "SELECT typeno FROM systypes WHERE typeid=10";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		echo '<div class="page_help_text"><b>' . _('The last invoice created was number') . ' ' . $MyRow[0] . '</b><br />' . _('If only a single invoice is required') . ', ' . _('enter the invoice number to print in the Start transaction number to print field and leave the End transaction number to print field blank') . '. ' . _('Only use the end invoice to print field if you wish to print a sequential range of invoices') . '';
		$SQL = "SELECT typeno FROM systypes WHERE typeid=11";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		echo '<br /><b>' . _('The last credit note created was number') . ' ' . $MyRow[0] . '</b><br />' . _('A sequential range can be printed using the same method as for invoices above') . '. ' . _('A single credit note can be printed by only entering a start transaction number') . '</div>';
	} else {
		while ($FromTransNo <= $_POST['ToTransNo']) {
			/*retrieve the invoice details from the database to print
			notice that salesorder record must be present to print the invoice purging of sales orders will
			nobble the invoice reprints */
			if ($InvOrCredit == 'Invoice') {
				$SQL = "SELECT
						debtortrans.trandate,
						debtortrans.ovamount,
						debtortrans.ovdiscount,
						debtortrans.ovfreight,
						debtortrans.ovgst,
						debtortrans.rate,
						debtortrans.invtext,
						debtortrans.consignment,
						debtorsmaster.name,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3,
						debtorsmaster.address4,
						debtorsmaster.address5,
						debtorsmaster.address6,
						debtorsmaster.currcode,
						salesorders.deliverto,
						salesorders.deladd1,
						salesorders.deladd2,
						salesorders.deladd3,
						salesorders.deladd4,
						salesorders.deladd5,
						salesorders.deladd6,
						salesorders.customerref,
						salesorders.orderno,
						salesorders.orddate,
						shippers.shippername,
						custbranch.brname,
						custbranch.braddress1,
						custbranch.braddress2,
						custbranch.braddress3,
						custbranch.braddress4,
						custbranch.braddress5,
						custbranch.braddress6,
						salesman.salesmanname,
						debtortrans.debtorno
					FROM debtortrans,
						debtorsmaster,
						custbranch,
						salesorders,
						shippers,
						salesman
					WHERE debtortrans.order_ = salesorders.orderno
					AND debtortrans.type=10
					AND debtortrans.transno='" . $FromTransNo . "'
					AND debtortrans.shipvia=shippers.shipper_id
					AND debtortrans.debtorno=debtorsmaster.debtorno
					AND debtortrans.debtorno=custbranch.debtorno
					AND debtortrans.branchcode=custbranch.branchcode
					AND custbranch.salesman=salesman.salesmancode";
			} else {
				$SQL = "SELECT debtortrans.trandate,
						debtortrans.ovamount,
						debtortrans.ovdiscount,
						debtortrans.ovfreight,
						debtortrans.ovgst,
						debtortrans.rate,
						debtortrans.invtext,
						debtorsmaster.name,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3,
						debtorsmaster.address4,
						debtorsmaster.address5,
						debtorsmaster.address6,
						debtorsmaster.currcode,
						custbranch.brname,
						custbranch.braddress1,
						custbranch.braddress2,
						custbranch.braddress3,
						custbranch.braddress4,
						custbranch.braddress5,
						custbranch.braddress6,
						salesman.salesmanname,
						debtortrans.debtorno
					FROM debtortrans,
						debtorsmaster,
						custbranch,
						salesman
					WHERE debtortrans.type=11
					AND debtortrans.transno='" . $FromTransNo . "'
					AND debtortrans.debtorno=debtorsmaster.debtorno
					AND debtortrans.debtorno=custbranch.debtorno
					AND debtortrans.branchcode=custbranch.branchcode
					AND custbranch.salesman=salesman.salesmancode";
			}
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 0 or DB_error_no() != 0) {
				echo '<div class="page_help_text">' . _('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $FromTransNo . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available') . '</div>';
				if ($debug == 1) {
					echo _('The SQL used to get this information that failed was') . "<br />$SQL";
				}
				break;
				include ('includes/footer.php');
				exit;
			} elseif (DB_num_rows($Result) == 1) {
				$MyRow = DB_fetch_array($Result);
				/* Then there's an invoice (or credit note) to print. So print out the invoice header and GST Number from the company record */
				if (count($_SESSION['AllowedPageSecurityTokens']) == 1 and in_array(1, $_SESSION['AllowedPageSecurityTokens']) and $MyRow['debtorno'] != $_SESSION['CustomerID']) {
					echo '<p><font color=RED size=4>' . _('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '. ' . _('Please select only transactions relevant to your company') . '</font></p>';
					exit;
				}
				$ExchRate = $MyRow['rate'];
				$PageNumber = 1;
				echo '<table class="table1"><tr><td VALIGN=TOP WIDTH=10%><img src="' . $_SESSION['LogoFile'] . '"></td><td bgcolor="#BBBBBB"><b>';
				if ($InvOrCredit == 'Invoice') {
					echo '<font size=4>' . _('TAX INVOICE') . ' ';
				} else {
					echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
				}
				echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br /><font size=1>' . _('Tax Authority Ref') . '. ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr></table>';
				/* Now print out the logo and company name and address */
				echo '<table class="table1"><tr><td><font size=4 color="#333333"><b>' . $_SESSION['CompanyRecord']['coyname'] . '</b></font><br />';
				echo $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
				echo _('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
				echo _('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
				echo _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';
				echo '</td><td WIDTH=50% class=number>';
				/* Now the customer charged to details in a sub table within a cell of the main table*/
				echo '<table class="table1"><tr><td align=left bgcolor="#BBBBBB"><b>' . _('Charge To') . ':</b></td></tr><tr><td bgcolor="#EEEEEE">';
				echo $MyRow['name'] . '<br />' . $MyRow['address1'] . '<br />' . $MyRow['address2'] . '<br />' . $MyRow['address3'] . '<br />' . $MyRow['address4'] . '<br />' . $MyRow['address5'] . '<br />' . $MyRow['address6'];
				echo '</td></tr></table>';
				/*end of the small table showing charge to account details */
				echo _('Page') . ': ' . $PageNumber;
				echo '</td></tr></table>';
				/*end of the main table showing the company name and charge to details */
				if ($InvOrCredit == 'Invoice') {
					echo '<table class="table1">
							<tr>
								<td align=left bgcolor="#BBBBBB"><b>' . _('Charge Branch') . ':</b></td>
								<td align=left bgcolor="#BBBBBB"><b>' . _('Delivered To') . ':</b></td>
							</tr>';
					echo '<tr>
						<td bgcolor="#EEEEEE">' . $MyRow['brname'] . '<br />' . $MyRow['braddress1'] . '<br />' . $MyRow['braddress2'] . '<br />' . $MyRow['braddress3'] . '<br />' . $MyRow['braddress4'] . '<br />' . $MyRow['braddress5'] . '<br />' . $MyRow['braddress6'] . '</td>';
					echo '<td bgcolor="#EEEEEE">' . $MyRow['deliverto'] . '<br />' . $MyRow['deladd1'] . '<br />' . $MyRow['deladd2'] . '<br />' . $MyRow['deladd3'] . '<br />' . $MyRow['deladd4'] . '<br />' . $MyRow['deladd5'] . '<br />' . $MyRow['deladd6'] . '</td>';
					echo '</tr>
					</table><hr>';
					echo '<table class="table1">
						<tr>
							<td align=left bgcolor="#BBBBBB"><b>' . _('Your Order Ref') . '</b></td>
							<td align=left bgcolor="#BBBBBB"><b>' . _('Our Order No') . '</b></td>
							<td align=left bgcolor="#BBBBBB"><b>' . _('Order Date') . '</b></td>
							<td align=left bgcolor="#BBBBBB"><b>' . _('Invoice Date') . '</b></td>
							<td align=left bgcolor="#BBBBBB"><b>' . _('Sales Person') . '</font></b></td>
							<td align=left bgcolor="#BBBBBB"><b>' . _('Shipper') . '</b></td>
							<td align=left bgcolor="#BBBBBB"><b>' . _('Consignment Ref') . '</b></td>
						</tr>';
					echo '<tr>
							<td bgcolor="#EEEEEE">' . $MyRow['customerref'] . '</td>
							<td bgcolor="#EEEEEE">' . $MyRow['orderno'] . '</td>
							<td bgcolor="#EEEEEE">' . ConvertSQLDate($MyRow['orddate']) . '</td>
							<td bgcolor="#EEEEEE">' . ConvertSQLDate($MyRow['trandate']) . '</td>
							<td bgcolor="#EEEEEE">' . $MyRow['salesmanname'] . '</td>
							<td bgcolor="#EEEEEE">' . $MyRow['shippername'] . '</td>
							<td bgcolor="#EEEEEE">' . $MyRow['consignment'] . '</td>
						</tr>
					</table>';
					$SQL = "SELECT stockmoves.stockid,
						stockmaster.description,
						-stockmoves.qty*stockmoves.conversionfactor as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmoves.units,
						stockmoves.conversionfactor
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=10
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
				} else {
					/* then its a credit note */
					echo '<table width=50%><tr>
						<td align=left bgcolor="#BBBBBB"><b>' . _('Branch') . ':</b></td>
						</tr>';
					echo '<tr>
						<td bgcolor="#EEEEEE">' . $MyRow['brname'] . '<br />' . $MyRow['braddress1'] . '<br />' . $MyRow['braddress2'] . '<br />' . $MyRow['braddress3'] . '<br />' . $MyRow['braddress4'] . '<br />' . $MyRow['braddress5'] . '<br />' . $MyRow['braddress6'] . '</td>
					</tr></table>';
					echo '<hr><table class="table1"><tr>
						<td align=left bgcolor="#BBBBBB"><b>' . _('Date') . '</b></td>
						<td align=left bgcolor="#BBBBBB"><b>' . _('Sales Person') . '</font></b></td>
					</tr>';
					echo '<tr>
						<td bgcolor="#EEEEEE">' . ConvertSQLDate($MyRow['trandate']) . '</td>
						<td bgcolor="#EEEEEE">' . $MyRow['salesmanname'] . '</td>
					</tr></table>';
					$SQL = "SELECT stockmoves.stockid,
						stockmaster.description,
						stockmoves.qty*stockmoves.conversionfactor as quantity,
						stockmoves.discountpercent, ((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.units,
						stockmoves.conversionfactor
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=11
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
				}

				echo '<hr>';
				echo '<div class="centre"><font size=2>' . _('All amounts stated in') . ' ' . $MyRow['currcode'] . '</font></div>';
				$Result = DB_query($SQL);
				if (DB_error_no() != 0) {
					echo '<div class="page_help_text">' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database') . '</div>';
					if ($debug == 1) {
						echo '<br />' . _('The SQL used to get this information that failed was') . '<br />' . $SQL;
					}
					exit;
				}
				if (DB_num_rows($Result) > 0) {
					echo '<table class="table1">
						<tr><th>' . _('Item Code') . '</th>
						<th>' . _('Item Description') . '</th>
						<th>' . _('Quantity') . '</th>
						<th>' . _('Unit') . '</th>
						<th>' . _('Price') . '</th>
						<th>' . _('Discount') . '</th>
						<th>' . _('Net') . '</th></tr>';
					$LineCounter = 17;
					$k = 0; //row colour counter
					while ($MyRow2 = DB_fetch_array($Result)) {
						$DisplayPrice = number_format($MyRow2['fxprice'], 2);
						$DisplayQty = number_format($MyRow2['quantity'], 2);
						$DisplayNet = number_format($MyRow2['fxnet'], 2);
						if ($MyRow2['discountpercent'] == 0) {
							$DisplayDiscount = '';
						} else {
							$DisplayDiscount = number_format($MyRow2['discountpercent'] * 100, 2) . '%';
						}
						echo '<tr class="striped_row">
								<td>', $MyRow2['stockid'], '</td>
								<td>', $MyRow2['description'], '</td>
								<td class=number>', $DisplayQty, '</td>
								<td class=number>', $MyRow2['units'], '</td>
								<td class=number>', $DisplayPrice, '</td>
								<td class=number>', $DisplayDiscount, '</td>
								<td class=number>', $DisplayNet, '</td>
							</tr>';
						if (strlen($MyRow2['narrative']) > 1) {
							echo '<tr class="striped_row">
									<td></td>
									<td colspan=6>', $MyRow2['narrative'], '</td>
								</tr>';
							$LineCounter++;
						}
						$LineCounter++;
						if ($LineCounter == ($_SESSION['PageLength'] - 2)) {
							/* head up a new invoice/credit note page */
							$PageNumber++;
							echo '</table><table class="table1"><tr><td VALIGN=TOp><img src="' . $_SESSION['LogoFile'] . '"></td><td bgcolor="#BBBBBB"><b>';
							if ($InvOrCredit == 'Invoice') {
								echo '<font size=4>' . _('TAX INVOICE') . ' ';
							} else {
								echo '<font color=red size=4>' . _('TAX CREDIT NOTE') . ' ';
							}
							echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br /><font size=1>' . _('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr></table>';
							/*Now print out company name and address */
							echo '<table class="table1"><tr>
								<td><font size=4 color="#333333"><b>' . $_SESSION['CompanyRecord']['coyname'] . '</b></font><br />';
							echo $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
							echo $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
							echo _('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
							echo _('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
							echo _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';
							echo '</td><td class=number>' . _('Page') . ': ' . $PageNumber . '</td></tr></table>';
							echo '<table class="table1"><tr>
							<th>' . _('Item Code') . '</th>
							<th>' . _('Item Description') . '</th>
							<th>' . _('Quantity') . '</th>
							<th>' . _('Unit') . '</th>
							<th>' . _('Price') . '</th>
							<th>' . _('Discount') . '</th>
							<th>' . _('Net') . '</th></tr>';
							$LineCounter = 10;
						} //end if need a new page headed up
						
					} //end while there are line items to print out
					echo '</table>';
				}
				/*end if there are stock movements to show on the invoice or credit note*/
				/* check to see enough space left to print the totals/footer */
				$LinesRequiredForText = floor(strlen($MyRow['invtext']) / 140);
				if ($LineCounter >= ($_SESSION['PageLength'] - 8 - $LinesRequiredForText)) {
					/* head up a new invoice/credit note page */
					$PageNumber++;
					echo '<table class="table1"><tr><td VALIGN=TOp><img src="' . $_SESSION['LogoFile'] . '"></td><td bgcolor="#BBBBBB"><b>';
					if ($InvOrCredit == 'Invoice') {
						echo '<font size=4>' . _('TAX INVOICE') . ' ';
					} else {
						echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
					}
					echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br /><font size=1>' . _('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr><table>';
					/* Print out the logo and company name and address */
					echo '<table class="table1"><tr><td><font size=4 color="#333333"><b>' . $_SESSION['CompanyRecord']['coyname'] . '</b></font><br />';
					echo $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
					echo _('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
					echo _('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
					echo _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';
					echo '</td><td class=number>' . _('Page') . ': ' . $PageNumber . '</td></tr></table>';
					echo '<table class="table1"><tr>
						<th>' . _('Item Code') . '</th>
						<th>' . _('Item Description') . '</th>
						<th>' . _('Quantity') . '</th>
						<th>' . _('Unit') . '</th>
						<th>' . _('Price') . '</th>
						<th>' . _('Discount') . '</th>
						<th>' . _('Net') . '</th></tr>';
					$LineCounter = 10;
				}
				/* Space out the footer to the bottom of the page */
				echo '<br /><br />' . $MyRow['invtext'];
				$LineCounter = $LineCounter + 2 + $LinesRequiredForText;
				while ($LineCounter < ($_SESSION['PageLength'] - 6)) {
					echo '<br />';
					$LineCounter++;
				}
				/* Now print out the footer and totals */
				if ($InvOrCredit == 'Invoice') {
					$DisplaySubTot = number_format($MyRow['ovamount'], 2);
					$DisplayFreight = number_format($MyRow['ovfreight'], 2);
					$DisplayTax = number_format($MyRow['ovgst'], 2);
					$DisplayTotal = number_format($MyRow['ovfreight'] + $MyRow['ovgst'] + $MyRow['ovamount'], 2);
				} else {
					$DisplaySubTot = number_format(-$MyRow['ovamount'], 2);
					$DisplayFreight = number_format(-$MyRow['ovfreight'], 2);
					$DisplayTax = number_format(-$MyRow['ovgst'], 2);
					$DisplayTotal = number_format(-$MyRow['ovfreight'] - $MyRow['ovgst'] - $MyRow['ovamount'], 2);
				}
				/*Print out the invoice text entered */
				echo '<table class=table1><tr>
					<td class=number>' . _('Sub Total') . '</td>
					<td class=number bgcolor="#EEEEEE" width=15%>' . $DisplaySubTot . '</td></tr>';
				echo '<tr><td class=number>' . _('Freight') . '</td>
					<td class=number bgcolor="#EEEEEE">' . $DisplayFreight . '</td></tr>';
				echo '<tr><td class=number>' . _('Tax') . '</td>
					<td class=number bgcolor="#EEEEEE">' . $DisplayTax . '</td></tr>';
				if ($InvOrCredit == 'Invoice') {
					echo '<tr><td class=number><b>' . _('TOTAL INVOICE') . '</b></td>
						<td class=number bgcolor="#EEEEEE"><U><b>' . $DisplayTotal . '</b></U></td></tr>';
				} else {
					echo '<tr><td class=number><font color=RED><b>' . _('TOTAL CREDIT') . '</b></font></td>
							<td class=number bgcolor="#EEEEEE"><font color=RED><U><b>' . $DisplayTotal . '</b></U></font></td></tr>';
				}
				echo '</table>';
			}
			/* end of check to see that there was an invoice record to print */
			$FromTransNo++;
		}
		/* end loop to print invoices */
	}
	/*end of if FromTransNo exists */
	include ('includes/footer.php');
}
/*end of else not PrintPDF */
function PrintLinesToBottom($PDF, $Page_Height, $PageNumber, $FormDesign) {
	/* draw the vertical column lines right to the bottom */
	$PDF->line($FormDesign->DataLines->Line1->startx, $Page_Height - $FormDesign->DataLines->Line1->starty, $FormDesign->DataLines->Line1->endx, $Page_Height - $FormDesign->DataLines->Line1->endy);
	/* Print a column vertical line */
	$PDF->line($FormDesign->DataLines->Line2->startx, $Page_Height - $FormDesign->DataLines->Line2->starty, $FormDesign->DataLines->Line2->endx, $Page_Height - $FormDesign->DataLines->Line2->endy);
	/* Print a column vertical line */
	$PDF->line($FormDesign->DataLines->Line3->startx, $Page_Height - $FormDesign->DataLines->Line3->starty, $FormDesign->DataLines->Line3->endx, $Page_Height - $FormDesign->DataLines->Line3->endy);
	/* Print a column vertical line */
	$PDF->line($FormDesign->DataLines->Line4->startx, $Page_Height - $FormDesign->DataLines->Line4->starty, $FormDesign->DataLines->Line4->endx, $Page_Height - $FormDesign->DataLines->Line4->endy);
	/* Print a column vertical line */
	$PDF->line($FormDesign->DataLines->Line5->startx, $Page_Height - $FormDesign->DataLines->Line5->starty, $FormDesign->DataLines->Line5->endx, $Page_Height - $FormDesign->DataLines->Line5->endy);
	$PDF->line($FormDesign->DataLines->Line6->startx, $Page_Height - $FormDesign->DataLines->Line6->starty, $FormDesign->DataLines->Line6->endx, $Page_Height - $FormDesign->DataLines->Line6->endy);
	//	$PDF->newPage();
	$PageNumber++;
}
?>