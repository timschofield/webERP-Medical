<?php
include ('includes/session.php');

$Title = _('Billing Configuration');

include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/currency.png" title="', _('Hospital Configuration'), '" alt="" />', $Title, '
	</p>';

if (isset($_POST['Save'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if ($InputError != 1) {

		$SQL = array();

		if ($_SESSION['RegistrationBillingItem'] != $_POST['X_RegistrationBillingItem']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_RegistrationBillingItem'] . "' WHERE confname = 'RegistrationBillingItem'";
		}
		if ($_SESSION['InpatientAdmissionsBillingItem'] != $_POST['X_InpatientAdmissionsBillingItem']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_InpatientAdmissionsBillingItem'] . "' WHERE confname = 'InpatientAdmissionsBillingItem'";
		}
		if ($_SESSION['OutpatientAdmissionsBillingItem'] != $_POST['X_OutpatientAdmissionsBillingItem']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_OutpatientAdmissionsBillingItem'] . "' WHERE confname = 'OutpatientAdmissionsBillingItem'";
		}
		if ($_SESSION['LabPaymentBeforeTest'] != $_POST['X_LabPaymentBeforeTest']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_LabPaymentBeforeTest'] . "' WHERE confname = 'LabPaymentBeforeTest'";
		}
		if ($_SESSION['BillForBacteriologyTest'] != $_POST['X_BillForBacteriologyTest']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_BillForBacteriologyTest'] . "' WHERE confname = 'BillForBacteriologyTest'";
		}
		if ($_SESSION['BillForBloodTest'] != $_POST['X_BillForBloodTest']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_BillForBloodTest'] . "' WHERE confname = 'BillForBloodTest'";
		}
		if ($_SESSION['BillForMedicalTest'] != $_POST['X_BillForMedicalTest']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_BillForMedicalTest'] . "' WHERE confname = 'BillForMedicalTest'";
		}
		if ($_SESSION['BillForPathologyTest'] != $_POST['X_BillForPathologyTest']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_BillForPathologyTest'] . "' WHERE confname = 'BillForPathologyTest'";
		}
		$ErrMsg = _('The billing configuration could not be updated because');
		$DbgMsg = _('The SQL that failed was') . ':';
		if (sizeof($SQL) > 0) {
			$Result = DB_Txn_Begin();
			foreach ($SQL as $SqlLine) {
				$Result = DB_query($SqlLine, $ErrMsg, $DbgMsg, true);
			}
			$Result = DB_Txn_Commit();
			prnMsg(_('Billing configuration updated'), 'success');

			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include ($PathPrefix . 'includes/GetConfig.php');
			$ForceConfigReload = False;
		}
	} else {
		prnMsg(_('Validation failed') . ', ' . _('no updates or deletes took place'), 'warn');
	}

}

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('Registration and Admissions'), '</legend>';

$SQL = "SELECT stockid,
				description
			FROM stockmaster
			INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN stocktypes
				ON stockcategory.stocktype=stocktypes.type
			WHERE stocktypes.type='R'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<field>
			<label for="X_RegistrationBillingItem">', _('Item to bill for patient registration'), ':</label>
			<select name="X_RegistrationBillingItem">
				<option value="">', _('Do not Bill'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['stockid'] == $_SESSION['RegistrationBillingItem']) {
			echo '<option selected="selected" value="', $MyRow['stockid'], '">', $MyRow['stockid'], ' - ', $MyRow['description'], '</option>';
		} else {
			echo '<option value="', $MyRow['stockid'], '">', $MyRow['stockid'], ' - ', $MyRow['description'], '</option>';
		}
	}
	echo '</select>
		</field>';
}

$SQL = "SELECT stockid,
				description
			FROM stockmaster
			INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN stocktypes
				ON stockcategory.stocktype=stocktypes.type
			WHERE stocktypes.type='R'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<field>
			<label for="X_InpatientAdmissionsBillingItem">', _('Item to bill for inpatient admissions'), ':</label>
			<select name="X_InpatientAdmissionsBillingItem">
				<option value="">', _('Do not Bill'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['stockid'] == $_SESSION['InpatientAdmissionsBillingItem']) {
			echo '<option selected="selected" value="', $MyRow['stockid'], '">', $MyRow['stockid'], ' - ', $MyRow['description'], '</option>';
		} else {
			echo '<option value="', $MyRow['stockid'], '">', $MyRow['stockid'], ' - ', $MyRow['description'], '</option>';
		}
	}
	echo '</select>
		</field>';
}

$SQL = "SELECT stockid,
				description
			FROM stockmaster
			INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN stocktypes
				ON stockcategory.stocktype=stocktypes.type
			WHERE stocktypes.type='R'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<field>
			<label for="X_OutpatientAdmissionsBillingItem">', _('Item to bill for outpatient admissions'), ':</label>
			<select name="X_OutpatientAdmissionsBillingItem">
				<option value="">', _('Do not Bill'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['stockid'] == $_SESSION['OutpatientAdmissionsBillingItem']) {
			echo '<option selected="selected" value="', $MyRow['stockid'], '">', $MyRow['stockid'], ' - ', $MyRow['description'], '</option>';
		} else {
			echo '<option value="', $MyRow['stockid'], '">', $MyRow['stockid'], ' - ', $MyRow['description'], '</option>';
		}
	}
	echo '</select>
		</field>';
}

echo '</fieldset><br />';

echo '<fieldset>
		<legend>', _('Laboratory Billing'), '</legend>';

echo '<field>
		<label for="X_LabPaymentBeforeTest">', _('Payment must be made before Test'), '</label>
		<select name="X_LabPaymentBeforeTest">';
if ($_SESSION['LabPaymentBeforeTest'] == 0) {
	$Selected0 = ' selected="selected" ';
} else {
	$Selected0 = ' ';
}
if ($_SESSION['LabPaymentBeforeTest'] == 1) {
	$Selected1 = ' selected="selected" ';
} else {
	$Selected1 = ' ';
}
if ($_SESSION['LabPaymentBeforeTest'] == 2) {
	$Selected2 = ' selected="selected" ';
} else {
	$Selected2 = ' ';
}
echo '<option', $Selected0, ' value="0">', _('If not insured'), '</option>';
echo '<option', $Selected1, ' value="1">', _('Always'), '</option>';
echo '<option', $Selected2, ' value="2">', _('Never'), '</option>';
echo '</select>
	</field>';

echo '<field>
		<label for="X_BillForBacteriologyTest">', _('Bill for bacteriology tests'), '</label>
		<select name="X_BillForBacteriologyTest">';
if ($_SESSION['BillForBacteriologyTest'] == 0) {
	$Selected0 = ' selected="selected" ';
} else {
	$Selected0 = ' ';
}
if ($_SESSION['BillForBacteriologyTest'] == 1) {
	$Selected1 = ' selected="selected" ';
} else {
	$Selected1 = ' ';
}

echo '<option', $Selected1, 'value="1">', _('Yes'), '</option>';
echo '<option', $Selected0, 'value="0">', _('No'), '</option>';
echo '</select>
	</field>';

echo '<field>
		<label for="X_BillForBloodTest">', _('Bill for blood tests'), '</label>
		<select name="X_BillForBloodTest">';
if ($_SESSION['BillForBloodTest'] == 0) {
	$Selected0 = ' selected="selected" ';
} else {
	$Selected0 = ' ';
}
if ($_SESSION['BillForBloodTest'] == 1) {
	$Selected1 = ' selected="selected" ';
} else {
	$Selected1 = ' ';
}
echo '<option', $Selected1, 'value="1">', _('Yes'), '</option>';
echo '<option', $Selected0, 'value="0">', _('No'), '</option>';
echo '</select>
	</field>';

echo '<field>
		<label for="X_BillForMedicalTest">', _('Bill for medical tests'), '</label>
		<select name="X_BillForMedicalTest">';
if ($_SESSION['BillForMedicalTest'] == 0) {
	$Selected0 = ' selected="selected" ';
} else {
	$Selected0 = ' ';
}
if ($_SESSION['BillForMedicalTest'] == 1) {
	$Selected1 = ' selected="selected" ';
}
echo '<option', $Selected1, 'value="1">', _('Yes'), '</option>';
echo '<option', $Selected0, 'value="0">', _('No'), '</option>';
echo '</select>
	</field>';

echo '<field>
		<label for="X_BillForPathologyTest">', _('Bill for pathology tests'), '</label>
		<select name="X_BillForPathologyTest">';
if ($_SESSION['BillForPathologyTest'] == 0) {
	$Selected0 = ' selected="selected" ';
} else {
	$Selected0 = ' ';
}
if ($_SESSION['BillForPathologyTest'] == 1) {
	$Selected1 = ' selected="selected" ';
}
echo '<option', $Selected1, 'value="1">', _('Yes'), '</option>';
echo '<option', $Selected0, 'value="0">', _('No'), '</option>';
echo '</select>
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Save" value="', _('Save Configuration'), '" />
	</div>';

echo '</form>';

include ('includes/footer.php');

?>