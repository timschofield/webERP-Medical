<?php

Class Allocation {
	Var $TransID;
	Var $Amount;

	function __construct ($TransID, $Amount){
		$this->TransID = $TransID;
		$this->Amount = $Amount;
	}
}

include('includes/session.php');
if (isset($_POST['AmountsDueBy'])){$_POST['AmountsDueBy'] = ConvertSQLDate($_POST['AmountsDueBy']);};
include('includes/SQL_CommonFunctions.inc');
include('includes/GetPaymentMethods.php');


if ((isset($_POST['PrintPDF']) OR isset($_POST['PrintPDFAndProcess']))
	AND isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1
	AND is_numeric(filter_number_format($_POST['ExRate']))){

/*then print the report */
	$Title = _('Payment Run - Problem Report');
	$RefCounter = 0;
	include('includes/PDFStarter.php');
	$PDF->addInfo('Title',_('Payment Run Report'));
	$PDF->addInfo('Subject',_('Payment Run') . ' - ' . _('suppliers from') . ' ' . $_POST['FromCriteria'] . ' to ' . $_POST['ToCriteria'] . ' in ' . $_POST['Currency'] . ' ' . _('and Due By') . ' ' .  $_POST['AmountsDueBy']);

	$PageNumber=1;
	$line_height=12;

  /*Now figure out the invoice less credits due for the Supplier range under review */

	include ('includes/PDFPaymentRunPageHeader.inc');

	$SQL = "SELECT suppliers.supplierid,
					currencies.decimalplaces AS currdecimalplaces,
					SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
			FROM suppliers INNER JOIN paymentterms
			ON suppliers.paymentterms = paymentterms.termsindicator
			INNER JOIN supptrans
			ON suppliers.supplierid = supptrans.supplierno
			INNER JOIN systypes
			ON systypes.typeid = supptrans.type
			INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
			WHERE supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
			AND supptrans.duedate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "'
			AND supptrans.hold=0
			AND suppliers.currcode = '" . $_POST['Currency'] . "'
			AND supptrans.supplierno >= '" . $_POST['FromCriteria'] . "'
			AND supptrans.supplierno <= '" . $_POST['ToCriteria'] . "'
			GROUP BY suppliers.supplierid,
					currencies.decimalplaces
			HAVING SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) > 0
			ORDER BY suppliers.supplierid";

	$SuppliersResult = DB_query($SQL);

	$SupplierID ='';
	$TotalPayments = 0;
	$TotalAccumDiffOnExch = 0;


	if (isset($_POST['PrintPDFAndProcess'])){
		DB_Txn_Begin();
	}

	while ($SuppliersToPay = DB_fetch_array($SuppliersResult)){

		$CurrDecimalPlaces = $SuppliersToPay['currdecimalplaces'];

		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						systypes.typename,
						paymentterms.terms,
						supptrans.suppreference,
						supptrans.trandate,
						supptrans.rate,
						supptrans.transno,
						supptrans.type,
						(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
						(supptrans.ovamount + supptrans.ovgst ) AS trantotal,
						supptrans.diffonexch,
						supptrans.id
				FROM suppliers INNER JOIN paymentterms
				ON suppliers.paymentterms = paymentterms.termsindicator
				INNER JOIN supptrans
				ON suppliers.supplierid = supptrans.supplierno
				INNER JOIN systypes
				ON systypes.typeid = supptrans.type
				WHERE supptrans.supplierno = '" . $SuppliersToPay['supplierid'] . "'
				AND supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
				AND supptrans.duedate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "'
				AND supptrans.hold = 0
				AND suppliers.currcode = '" . $_POST['Currency'] . "'
				AND supptrans.supplierno >= '" . $_POST['FromCriteria'] . "'
				AND supptrans.supplierno <= '" . $_POST['ToCriteria'] . "'
				ORDER BY supptrans.supplierno,
					supptrans.type,
					supptrans.transno";

		$TransResult = DB_query($SQL,'','',false,false);
		if (DB_error_no() !=0) {
			$Title = _('Payment Run - Problem Report');
			include('includes/header.php');
			prnMsg(_('The details of supplier invoices due could not be retrieved because') . ' - ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug==1){
				echo '<br />' . _('The SQL that failed was') . ' ' . $SQL;
			}
			include('includes/footer.php');
			exit;
		}
		if (DB_num_rows($TransResult)==0) {
			include('includes/header.php');
			prnMsg(_('There are no outstanding supplier invoices to pay'),'info');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			include('includes/footer.php');
			exit;
		}

		unset($Allocs);
		$Allocs = array();
		$AllocCounter =0;

		while ($DetailTrans = DB_fetch_array($TransResult)){

			if ($DetailTrans['supplierid'] != $SupplierID){ /*Need to head up for a new suppliers details */

				if ($SupplierID!=''){ /*only print the footer if this is not the first pass */
					include('includes/PDFPaymentRun_PymtFooter.php');
				}
				$SupplierID = $DetailTrans['supplierid'];
				$SupplierName = $DetailTrans['suppname'];
				if (isset($_POST['PrintPDFAndProcess'])){
					$SuppPaymentNo = GetNextTransNo(22);
				}
				$AccumBalance = 0;
				$AccumDiffOnExch = 0;
				$LeftOvers = $PDF->addTextWrap($Left_Margin,
												$YPos,
												450-$Left_Margin,
												$FontSize,
												$DetailTrans['supplierid'] . ' - ' . $DetailTrans['suppname'] . ' - ' . $DetailTrans['terms'],
												'left');

				$YPos -= $line_height;
			}

			$DislayTranDate = ConvertSQLDate($DetailTrans['trandate']);

			$LeftOvers = $PDF->addTextWrap($Left_Margin+15, $YPos, 340-$Left_Margin,$FontSize,$DislayTranDate . ' - ' . $DetailTrans['typename'] . ' - ' . $DetailTrans['suppreference'], 'left');

			/*Positive is a favourable */
			$DiffOnExch = ($DetailTrans['balance'] / $DetailTrans['rate']) -  ($DetailTrans['balance'] / filter_number_format($_POST['ExRate']));

			$AccumBalance += $DetailTrans['balance'];
			$AccumDiffOnExch += $DiffOnExch;


			if (isset($_POST['PrintPDFAndProcess'])){

				/*Record the Allocations for later insertion once we have the ID of the payment SuppTrans */

				$Allocs[$AllocCounter] = new Allocation($DetailTrans['id'],$DetailTrans['balance']);
				$AllocCounter++;

				/*Now update the SuppTrans for the allocation made and the fact that it is now settled */

				$SQL = "UPDATE supptrans SET settled = 1,
											alloc = '" . $DetailTrans['trantotal'] . "',
											diffonexch = '" . ($DetailTrans['diffonexch'] + $DiffOnExch)  . "'
							WHERE type = '" . $DetailTrans['type'] . "'
							AND transno = '" . $DetailTrans['transno'] . "'";

				$ProcessResult = DB_query($SQL,'','',false,false);
				if (DB_error_no() !=0) {
					$Title = _('Payment Processing - Problem Report') . '.... ';
					include('includes/header.php');
					prnMsg(_('None of the payments will be processed since updates to the transaction records for') . ' ' .$SupplierName . ' ' . _('could not be processed because') . ' - ' . DB_error_msg(),'error');
					echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
					if ($debug==1){
						echo '<br />' . _('The SQL that failed was') . $SQL;
					}
					DB_Txn_Rollback();
					include('includes/footer.php');
					exit;
				}
			}

			$LeftOvers = $PDF->addTextWrap(340, $YPos,60,$FontSize,locale_number_format($DetailTrans['balance'],$CurrDecimalPlaces), 'right');
			$LeftOvers = $PDF->addTextWrap(405, $YPos,60,$FontSize,locale_number_format($DiffOnExch,$_SESSION['CompanyRecord']['decimalplaces']), 'right');

			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
				$PageNumber++;
				include('includes/PDFPaymentRunPageHeader.inc');
			}
		} /*end while there are detail transactions to show */
	} /* end while there are suppliers to retrieve transactions for */

	if ($SupplierID!=''){
		/*All the payment processing is in the below file */
		include('includes/PDFPaymentRun_PymtFooter.php');

		DB_Txn_Commit();

		if (DB_error_no() !=0) {
			$Title = _('Payment Processing - Problem Report') . '.... ';
			include('includes/header.php');
			prnMsg(_('None of the payments will be processed. Unfortunately, there was a problem committing the changes to the database because') . ' - ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug==1){
				prnMsg(_('The SQL that failed was') . '<br />' . $SQL,'error');
			}
			DB_Txn_Rollback();
			include('includes/footer.php');
			exit;
		}

		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 340-$Left_Margin,$FontSize,_('Grand Total Payments Due'), 'left');
		$LeftOvers = $PDF->addTextWrap(340, $YPos, 60,$FontSize,locale_number_format($TotalPayments,$CurrDecimalPlaces), 'right');
		$LeftOvers = $PDF->addTextWrap(405, $YPos, 60,$FontSize,locale_number_format($TotalAccumDiffOnExch,$_SESSION['CompanyRecord']['decimalplaces']), 'right');

	}

	$PDF->OutputD($_SESSION['DatabaseName'] . '_Payment_Run_' . Date('Y-m-d_Hms') . '.pdf');
	$PDF->__destruct();

} else { /*The option to print PDF was not hit */

	$Title=_('Payment Run');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Supplier Types'). '" alt="" />' . $Title . '
		</p>';

	if (isset($_POST['Currency']) AND !is_numeric(filter_number_format($_POST['ExRate']))){
		echo '<br />' . _('To process payments for') . ' ' . $_POST['Currency'] . ' ' . _('a numeric exchange rate applicable for purchasing the currency to make the payment with must be entered') . '. ' . _('This rate is used to calculate the difference in exchange and make the necessary postings to the General ledger if linked') . '.';
	}

	/* show form to allow input	*/

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Select Suppliers To Pay'), '</legend>';

	if (!isset($_POST['FromCriteria']) OR mb_strlen($_POST['FromCriteria'])<1){
		$DefaultFromCriteria = '1';
	} else {
		$DefaultFromCriteria = $_POST['FromCriteria'];
	}
	if (!isset($_POST['ToCriteria']) OR mb_strlen($_POST['ToCriteria'])<1){
		$DefaultToCriteria = 'zzzzzzz';
	} else {
		$DefaultToCriteria = $_POST['ToCriteria'];
	}
	echo '<field>
			<label for="FromCriteria">' . _('From Supplier Code') . ':</label>
			<input type="text" pattern="[^><+-]{1,10}" title="" maxlength="10" size="7" name="FromCriteria" value="' . $DefaultFromCriteria . '" />
			<fieldhelp>'._('Illegal characters are not allowed') . ' ' . '" \' - &amp; or a space'.'</fieldhelp>
		  </field>';
	echo '<field>
			<label for="ToCriteria">' . _('To Supplier Code') . ':</label>
			<input type="text" pattern="[^<>+-]{1,10}" title="" maxlength="10" size="7" name="ToCriteria" value="' . $DefaultToCriteria . '" />
			<fieldhelp>'._('Illegal characters are not allowed').'</fieldhelp>
		 </field>';


	echo '<field>
			<label for="Currency">' . _('For Suppliers Trading in') . ':</label>
			<select name="Currency">';

	$SQL = "SELECT currency, currabrev FROM currencies";
	$Result=DB_query($SQL);

	while ($MyRow=DB_fetch_array($Result)){
	if ($MyRow['currabrev'] == $_SESSION['CompanyRecord']['currencydefault']){
			echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
	}
	}
	echo '</select>
		</field>';

	if (!isset($_POST['ExRate']) OR !is_numeric(filter_number_format($_POST['ExRate']))){
		$DefaultExRate = '1';
	} else {
		$DefaultExRate = filter_number_format($_POST['ExRate']);
	}
	echo '<field>
			<label for="ExRate">' . _('Exchange Rate') . ':</label>
			<input type="text" class="number" title="" name="ExRate" maxlength="11" size="12" value="' . locale_number_format($DefaultExRate,'Variable') . '" />
			<fieldhelp>'._('The input must be number').'</fieldhelp>
		  </field>';

	if (!isset($_POST['AmountsDueBy'])){
		$DefaultDate = Date('Y-m-d', Mktime(0,0,0,Date('m')+1,0 ,Date('y')));
	} else {
		$DefaultDate = FormatDateForSQL($_POST['AmountsDueBy']);
	}

	echo '<field>
			<label for="AmountsDueBy">' . _('Payments Due To') . ':</label>
			<input type="date" name="AmountsDueBy" maxlength="10" size="11" value="' . $DefaultDate . '" />
		  </field>';

	$SQL = "SELECT bankaccountname, accountcode FROM bankaccounts";

	$AccountsResults = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		 echo '<br />' . _('The bank accounts could not be retrieved by the SQL because') . ' - ' . DB_error_msg();
		 if ($debug==1){
			echo '<br />' . _('The SQL used to retrieve the bank accounts was') . ':<br />' . $SQL;
		 }
		 exit;
	}

	echo '<field>
			<label for="BankAccount">' . _('Pay From Account') . ':</label>
			<select name="BankAccount">';

	if (DB_num_rows($AccountsResults)==0){
		 echo '</select></td>
			</field>
			</table>
			<p>' . _('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected') . '.
			</p>';
		 include('includes/footer.php');
		 exit;
	} else {
		while ($MyRow=DB_fetch_array($AccountsResults)){
			  /*list the bank account names */

			if (isset($_POST['BankAccount']) and $_POST['BankAccount']==$MyRow['accountcode']){
				echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . '</option>';
			}
		}
		echo '</select>
			</field>';
	}

	echo '<field>
			<label for="PaytType">' . _('Payment Type') . ':</label>
			<select name="PaytType">';

/* The array PaytTypes is set up in config.php for user modification
Payment types can be modified by editing that file */

	foreach ($PaytTypes as $PaytType) {

		 if (isset($_POST['PaytType']) and $_POST['PaytType']==$PaytType){
		   echo '<option selected="selected" value="' . $PaytType . '">' . $PaytType . '</option>';
		 } else {
		   echo '<option value="' . $PaytType . '">' . $PaytType . '</option>';
		 }
	}
	echo '</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Print PDF Only') . '" />
				<input type="submit" name="PrintPDFAndProcess" value="' . _('Print and Process Payments') . '" />
			</div>';
	echo '</form>';
	include ('includes/footer.php');
} /*end of else not PrintPDF */
?>