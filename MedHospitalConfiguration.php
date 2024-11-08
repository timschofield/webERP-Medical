<?php
include ('includes/session.php');

$Title = _('Hospital Configuration');

include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/hospital.png" title="', _('Hospital Configuration'), '" alt="" />', $Title, '
	</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if ($InputError != 1) {

		$SQL = array();

		if ($_SESSION['DispenseOnBill'] != $_POST['X_DispenseOnBill']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_DispenseOnBill'] . "' WHERE confname = 'DispenseOnBill'";
		}
		if ($_SESSION['CanAmendBill'] != $_POST['X_CanAmendBill']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_CanAmendBill'] . "' WHERE confname = 'CanAmendBill'";
		}
		if ($_SESSION['DefaultArea'] != $_POST['X_DefaultArea']) {
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultArea'] . "' WHERE confname='DefaultArea'";
		}
		if ($_SESSION['DefaultSalesPerson'] != $_POST['X_DefaultSalesPerson']) {
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultSalesPerson'] . "' WHERE confname='DefaultSalesPerson'";
		}
		if ($_SESSION['AutoPatientNo'] != $_POST['X_AutoPatientNo']) {
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_AutoPatientNo'] . "' WHERE confname='AutoPatientNo'";
		}
		if ($_SESSION['InsuranceDebtorType'] != $_POST['X_InsuranceDebtorType']) {
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_InsuranceDebtorType'] . "' WHERE confname='InsuranceDebtorType'";
		}
		if ($_SESSION['qrcodes_dir'] != $_POST['X_qrcodes_dir']) {
			$SQL[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_qrcodes_dir'] . "' WHERE confname = 'qrcodes_dir'";
		}
		if ($_SESSION['barcodes_dir'] != $_POST['X_barcodes_dir']) {
			$SQL[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_barcodes_dir'] . "' WHERE confname = 'barcodes_dir'";
		}
		if ($_SESSION['bacteriology_cat'] != $_POST['X_bacteriology_cat']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_bacteriology_cat'] . "' WHERE confname = 'bacteriology_cat'";
		}
		if ($_SESSION['radiology_cat'] != $_POST['X_radiology_cat']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_radiology_cat'] . "' WHERE confname = 'radiology_cat'";
		}
		$ErrMsg = _('The hospital configuration could not be updated because');
		$DbgMsg = _('The SQL that failed was') . ':';
		if (sizeof($SQL) > 0) {
			$Result = DB_Txn_Begin();
			foreach ($SQL as $SqlLine) {
				$Result = DB_query($SqlLine, $ErrMsg, $DbgMsg, true);
			}
			$Result = DB_Txn_Commit();
			prnMsg(_('Hospital configuration updated'), 'success');

			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include ($PathPrefix . 'includes/GetConfig.php');
			$ForceConfigReload = False;
		}
	} else {
		prnMsg(_('Validation failed') . ', ' . _('no updates or deletes took place'), 'warn');
	}

}
/* end of if submit */

echo '<form method="post" action="', htmlspecialchars(htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset style="width:auto; margin-right:5px;">
		<legend>', _('General Settings'), '</legend>';

echo '<field>
		<label for="X_DispenseOnBill">', _('Dispense on Bill'), ':</label>
		<select name="X_DispenseOnBill" autofocus="autofocus">';
if ($_SESSION['DispenseOnBill'] == '0') {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>', _('Should items be deducted from stock automatically on production of the bill, or on actual dispensing?'), '</fieldhelp>
</field>';

echo '<field>
		<label for="X_CanAmendBill">', _('Cashiers can Amend Bills'), ':</label>
		<select name="X_CanAmendBill">';
if ($_SESSION['CanAmendBill'] == '0') {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>' . _('Can the cashiers delete and insert lines in patients bills?') . '</fieldhelp>
</field>';

$SQL = "SELECT salesmancode, salesmanname FROM salesman";
$Result = DB_query($SQL);
echo '<field>
		<label for="X_DefaultSalesPerson">', _('Default Sales Person for Patients'), ':</label>
		<select required="required" minlength="1" name="X_DefaultSalesPerson">
			<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['DefaultSalesPerson']) and $MyRow['salesmancode'] == $_SESSION['DefaultSalesPerson']) {
		echo '<option selected="selected" value="', $MyRow['salesmancode'], '">', $MyRow['salesmanname'], '</option>';
	} else {
		echo '<option value="', $MyRow['salesmancode'], '">', $MyRow['salesmanname'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>' . _('The default sales person that will be used when patients are transferred from care2x') . '</fieldhelp>
</field>';

$SQL = "SELECT areacode, areadescription FROM areas";
$Result = DB_query($SQL);
echo '<field>
		<label for="X_DefaultArea">', _('Default Sales Area for Patients'), ':</label>
		<select required="required" minlength="1" name="X_DefaultArea">
			<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['DefaultArea']) and $MyRow['areacode'] == $_SESSION['DefaultArea']) {
		echo '<option selected="selected" value="', $MyRow['areacode'], '">', $MyRow['areadescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['areacode'], '">', $MyRow['areadescription'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>' . _('The default sales area that will be used when patients are transferred from care2x') . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_AutoPatientNo">', _('New Patient numbers Automatically Generated'), ':</label>
		<select name="X_AutoPatientNo">';
if ($_SESSION['AutoPatientNo'] == '0') {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>' . _('If new patient numbers are to be automatically allocated select Yes here.') . '</fieldhelp>
</field>';

$SQL = "SELECT typeid, typename FROM debtortype";
$Result = DB_query($SQL);
echo '<field>
		<label for="X_InsuranceDebtorType">', _('Debtor type to use for Insurance companies'), '</label>
		<select name="X_InsuranceDebtorType">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['InsuranceDebtorType']) and $MyRow['typeid'] == $_SESSION['InsuranceDebtorType']) {
		echo '<option selected="selected" value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
	} else {
		echo '<option value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>' . _('The debtor type that is used for insurance companies. All Insurancecompanies must be of this type.') . '</fieldhelp>
</field>';

//$qrcodes_dir
$CompanyDirectory = 'companies/' . $_SESSION['DatabaseName'] . '/';
$DirHandle = dir($CompanyDirectory);
echo '<field>
		<label for="X_qrcodes_dir">', _('The directory where QRcodes are stored'), ':</label>
		<select required="required" name="X_qrcodes_dir">';
while ($DirEntry = $DirHandle->read()) {
	if (is_dir($CompanyDirectory . $DirEntry) and $DirEntry != '..' and $DirEntry != '.' and $DirEntry != '.svn' and $DirEntry != 'CVS' and $DirEntry != 'reports' and $DirEntry != 'locale' and $DirEntry != 'fonts') {
		if ($_SESSION['qrcodes_dir'] == $CompanyDirectory . $DirEntry) {
			echo '<option selected="selected" value="', $DirEntry, '">', $DirEntry, '</option>';
		} else {
			echo '<option value="', $DirEntry, '">', $DirEntry, '</option>';
		}
	}
}
echo '</select>
	<fieldhelp>', _('The directory under which all qrcodes_dir files will be stored.'), '</fieldhelp>
</field>';

//barcodes_dir
$CompanyDirectory = 'companies/' . $_SESSION['DatabaseName'] . '/';
$DirHandle = dir($CompanyDirectory);
echo '<field>
		<label for="X_barcodes_dir">', _('The directory where barcodes are stored'), ':</label>
		<select required="required" name="X_barcodes_dir">';
while ($DirEntry = $DirHandle->read()) {
	if (is_dir($CompanyDirectory . $DirEntry) and $DirEntry != '..' and $DirEntry != '.' and $DirEntry != '.svn' and $DirEntry != 'CVS' and $DirEntry != 'reports' and $DirEntry != 'locale' and $DirEntry != 'fonts') {
		if ($_SESSION['barcodes_dir'] == $CompanyDirectory . $DirEntry) {
			echo '<option selected="selected" value="', $DirEntry, '">', $DirEntry, '</option>';
		} else {
			echo '<option value="', $DirEntry, '">', $DirEntry, '</option>';
		}
	}
}
echo '</select>
	<fieldhelp>', _('The directory under which all qrcodes_dir files will be stored.'), '</fieldhelp>
</field>';

echo '</fieldset>';

echo '<fieldset style="width:auto; margin-right:5px;">
		<legend>', _('Laboratory Settings'), '</legend>';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
$Result = DB_query($SQL);

echo '<field>
		<label for="X_bacteriology_cat">', _('Stock category for bacteriology tests'), '</label>
		<select required="required" name="X_bacteriology_cat">';

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['bacteriology_cat']) and $MyRow['categoryid'] == $_SESSION['bacteriology_cat']) {
		echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('Select the stock category to be used for bacteriology test part numbers'), '</fieldhelp>
</field>';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
$Result = DB_query($SQL);

echo '<field>
		<label for="X_radiology_cat">', _('Stock category for radiology tests'), '</label>
		<select required="required" name="X_radiology_cat">';

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['radiology_cat']) and $MyRow['categoryid'] == $_SESSION['radiology_cat']) {
		echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('Select the stock category to be used for radiology part numbers'), '</fieldhelp>
</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="', _('Update'), '" />
	</div>
</form>';

include ('includes/footer.php');
?>