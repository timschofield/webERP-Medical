<?php

include('includes/session.php');
$Title = _('QA Tests Maintenance');
$ViewTopic= 'QualityAssurance';// Filename in ManualContents.php's TOC.
$BookMark = 'QA_Tests';// Anchor's id in the manual's html document.
include('includes/header.php');

if (isset($_GET['SelectedQATest'])){
	$SelectedQATest =mb_strtoupper($_GET['SelectedQATest']);
} elseif(isset($_POST['SelectedQATest'])){
	$SelectedQATest =mb_strtoupper($_POST['SelectedQATest']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	if (mb_strlen($_POST['QATestName']) > 50) {
		$InputError = 1;
		prnMsg(_('The QA Test name must be fifty characters or less long'),'error');
		$Errors[$i] = 'QATestName';
		$i++;
	}

	if (mb_strlen($_POST['Type']) =='') {
		$InputError = 1;
		prnMsg(_('The Type must not be blank'),'error');
		$Errors[$i] = 'Type';
		$i++;
	}
	$sql= "SELECT COUNT(*) FROM qatests WHERE qatests.name='".$_POST['QATestName']."'
										AND qatests.testid <> '" .$SelectedQATest. "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$InputError = 1;
		prnMsg(_('The QA Test name already exists'),'error');
		$Errors[$i] = 'QATestName';
		$i++;
	}

	if (isset($SelectedQATest) AND $InputError !=1) {

		/*SelectedQATest could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE qatests SET name='" . $_POST['QATestName'] . "',
									method='" . $_POST['Method'] . "',
									groupby='" . $_POST['GroupBy'] . "',
									units='" . $_POST['Units'] . "',
									type='" . $_POST['Type'] . "',
									defaultvalue='" . $_POST['DefaultValue'] . "',
									numericvalue='" . $_POST['NumericValue'] . "',
									showoncert='" . $_POST['ShowOnCert'] . "',
									showonspec='" . $_POST['ShowOnSpec'] . "',
									showontestplan='" . $_POST['ShowOnTestPlan'] . "',
									active='" . $_POST['Active'] . "'
				WHERE qatests.testid = '".$SelectedQATest."'";

		$msg = _('QA Test record for') . ' ' . $_POST['QATestName'] . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new QA Test form */

		$sql = "INSERT INTO qatests (name,
						method,
						groupby,
						units,
						type,
						defaultvalue,
						numericvalue,
						showoncert,
						showonspec,
						showontestplan,
						active)
				VALUES ('" . $_POST['QATestName'] . "',
					'" . $_POST['Method'] . "',
					'" . $_POST['GroupBy'] . "',
					'" . $_POST['Units'] . "',
					'" .$_POST['Type'] . "',
					'" . $_POST['DefaultValue'] . "',
					'" . $_POST['NumericValue'] . "',
					'" . $_POST['ShowOnCert'] . "',
					'" . $_POST['ShowOnSpec'] . "',
					'" . $_POST['ShowOnTestPlan'] . "',
					'" . $_POST['Active'] . "'
					)";

		$msg = _('A new QA Test record has been added for') . ' ' . $_POST['QATestName'];
	}
	if ($InputError !=1) {
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The insert or update of the QA Test failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$result = DB_query($sql,$ErrMsg, $DbgMsg);

		prnMsg($msg , 'success');

		unset($SelectedQATest);
		unset($_POST['QATestName']);
		//unset($_POST['Method']);
		//unset($_POST['GroupBy']);
		//unset($_POST['Units']);
		//unset($_POST['Type']);
		unset($_POST['DefaultValue']);
		unset($_POST['NumericValue']);
		//unset($_POST['ShowOnCert']);
		//unset($_POST['ShowOnSpec']);
		//unset($_POST['ShowOnTestPlan']);
		//unset($_POST['Active']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS

	$sql= "SELECT COUNT(*) FROM prodspec WHERE  prodspec.testid='".$SelectedQATest."'";
	//$result = DB_query($sql);
	//$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this QA Test because Product Specs are using it'),'error');
	} else {
		$sql="DELETE FROM qatests WHERE testid='". $SelectedQATest."'";
		$ErrMsg = _('The QA Test could not be deleted because');
		$result = DB_query($sql,$ErrMsg);

		prnMsg(_('QA Test') . ' ' . $SelectedQATest . ' ' . _('has been deleted from the database'),'success');
		unset ($SelectedQATest);
		unset($delete);
		unset ($_GET['delete']);
	}
}

if (isset($SelectedQATest)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All QA Tests') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedQATest)) {
		//editing an existing Sales-person

		$sql = "SELECT testid,
				name,
				method,
				groupby,
				units,
				type,
				defaultvalue,
				numericvalue,
				showoncert,
				showonspec,
				showontestplan,
				active
				FROM qatests
				WHERE testid='".$SelectedQATest."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['SelectedQATest'] = $myrow['testid'];
		$_POST['QATestName'] = $myrow['name'];
		$_POST['Method'] = $myrow['method'];
		$_POST['GroupBy'] = $myrow['groupby'];
		$_POST['Type'] = $myrow['type'];
		$_POST['Units'] = $myrow['units'];
		$_POST['DefaultValue'] = $myrow['defaultvalue'];
		$_POST['NumericValue'] = $myrow['numericvalue'];
		$_POST['ShowOnCert'] = $myrow['showoncert'];
		$_POST['ShowOnSpec'] = $myrow['showonspec'];
		$_POST['ShowOnTestPlan'] = $myrow['showontestplan'];
		$_POST['Active'] = $myrow['active'];


		echo '<input type="hidden" name="SelectedQATest" value="' . $SelectedQATest . '" />';
		echo '<input type="hidden" name="TestID" value="' . $_POST['SelectedQATest'] . '" />';
		echo '<fieldset>
				<legend>', _('Edit QA Test'), '</legend>
				<field>
					<label for="SelectedQATest">' . _('QA Test ID') . ':</label>
					<fieldtext>' . $_POST['SelectedQATest'] . '</fieldtext>
				</field>';

	} else { //end of if $SelectedQATest only do the else when a new record is being entered

		echo '<fieldset>
				<legend>', _('Create New QA Test'), '</legend>';

	}
	if (!isset($_POST['QATestName'])){
	  $_POST['QATestName']='';
	}
	if (!isset($_POST['Method'])){
	  $_POST['Method']='';
	}
	if (!isset($_POST['GroupBy'])){
	  $_POST['GroupBy']='';
	}
	if (!isset($_POST['Units'])){
	  $_POST['Units']='';
	}
	if (!isset($_POST['Type'])) {
		$_POST['Type']=4;
	}
	if (!isset($_POST['Active'])) {
		$_POST['Active']=1;
	}
	if (!isset($_POST['NumericValue'])) {
		$_POST['NumericValue']=1;
	}
	if (!isset($_POST['ShowOnCert'])) {
		$_POST['ShowOnCert']=1;
	}
	if (!isset($_POST['ShowOnSpec'])) {
		$_POST['ShowOnSpec']=1;
	}
	if (!isset($_POST['ShowOnTestPlan'])) {
		$_POST['ShowOnTestPlan']=1;
	}
	if (!isset($_POST['DefaultValue'])) {
		$_POST['DefaultValue'] = '';
	}
	echo '<field>
			<label for="QATestName">' . _('QA Test Name') . ':</label>
			<input type="text" '. (in_array('QATestName',$Errors) ? 'class="inputerror"' : '' ) .' name="QATestName"  required="required" title="" size="30" maxlength="50" value="' . $_POST['QATestName'] . '" />
			<fieldhelp>' . _('The name of the Test you are setting up') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="Method">' . _('Method') . ':</label>
			<input type="text" name="Method" title="" size="20" maxlength="20" value="' . $_POST['Method'] . '" />
			<fieldhelp>' . _('ASTM, ISO, UL or other') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="GroupBy">' . _('Group By') . ':</label>
			<input type="text" name="GroupBy" title="" size="20" maxlength="20" value="' . $_POST['GroupBy'] . '" />
			<fieldhelp>' . _('Can be used to group certain Tests on the Product Specification or Certificate of Analysis or left blank') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="Units">' . _('Units') . ':</label>
			<input type="text" name="Units" title="" size="20" maxlength="20" value="' . $_POST['Units'] . '" />
			<fieldhelp>' . _('How this is measured. PSI, Fahrenheit, Celsius etc.') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="Type">' . _('Type') . ':</label>
			<td><select title="" name="Type">';
	if ($_POST['Type']==0){
		echo '<option selected="selected" value="0">' . _('Text Box') . '</option>';
	} else {
		echo '<option value="0">' . _('Text Box') . '</option>';
	}
	if ($_POST['Type']==1){
		echo '<option selected="selected" value="1">' . _('Select Box') . '</option>';
	} else {
		echo '<option value="1">' . _('Select Box') . '</option>';
	}
	if ($_POST['Type']==2){
		echo '<option selected="selected" value="2">' . _('Check Box') . '</option>';
	} else {
		echo '<option value="2">' . _('Check Box') . '</option>';
	}
	if ($_POST['Type']==3){
		echo '<option selected="selected" value="3">' . _('Date Box') . '</option>';
	} else {
		echo '<option value="3">' . _('Date Box') . '</option>';
	}
	if ($_POST['Type']==4){
		echo '<option selected="selected" value="4">' . _('Range') . '</option>';
	} else {
		echo '<option value="4">' . _('Range') . '</option>';
	}
	echo '</select>
		<fieldhelp>' . _('What sort of data field is required to record the results for this test') . '</fieldhelp>
	</field>';

	echo '<field>
			<label for="DefaultValue">' . _('Possible Values') . ':</label>
			<input type="text" name="DefaultValue" size="50" maxlength="150" value="' . $_POST['DefaultValue']. '" />
		</field>';

	echo '<field>
			<label for="NumericValue">' . _('Numeric Value?') . ':</label>
			<select name="NumericValue">';
	if ($_POST['NumericValue']==1){
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['NumericValue']==0){
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowOnCert">' . _('Show On Cert?') . ':</label>
			<select name="ShowOnCert">';
	if ($_POST['ShowOnCert']==1){
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['ShowOnCert']==0){
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowOnSpec">' . _('Show On Spec?') . ':</label>
			<select name="ShowOnSpec">';
	if ($_POST['ShowOnSpec']==1){
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['ShowOnSpec']==0){
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowOnTestPlan">' . _('Show On Test Plan?') . ':</label>
			<select name="ShowOnTestPlan">';
	if ($_POST['ShowOnTestPlan']==1){
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['ShowOnTestPlan']==0){
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Active">' . _('Active?') . ':</label>
			<select name="Active">';
	if ($_POST['Active']==1){
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['Active']==0){
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
		</form>';

} //end if record deleted no point displaying form to add record
if (!isset($SelectedQATest)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedQATest will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of QA Test will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT testid,
				name,
				method,
				groupby,
				units,
				type,
				defaultvalue,
				numericvalue,
				showoncert,
				showonspec,
				showontestplan,
				active
			FROM qatests
			ORDER BY name";
	$result = DB_query($sql);

	echo '<table class="selection">
		<thead>
			<tr>
			<th class="ascending">' . _('Test ID') . '</th>
			<th class="ascending">' . _('Name') . '</th>
			<th class="ascending">' . _('Method') . '</th>
			<th class="ascending">' . _('Group By') . '</th>
			<th class="ascending">' . _('Units') . '</th>
			<th class="ascending">' . _('Type') . '</th>
			<th>' . _('Possible Values') . '</th>
			<th class="ascending">' . _('Numeric Value') . '</th>
			<th class="ascending">' . _('Show on Cert') . '</th>
			<th class="ascending">' . _('Show on Spec') . '</th>
			<th class="ascending">' . _('Show on Test Plan') . '</th>
			<th class="ascending">' . _('Active') . '</th>
			</tr>
		</thead>
		<tbody>';

	while ($myrow=DB_fetch_array($result)) {

	if ($myrow['active'] == 1) {
		$ActiveText = _('Yes');
	} else {
		$ActiveText = _('No');
	}
	if ($myrow['numericvalue'] == 1) {
		$IsNumeric = _('Yes');
	} else {
		$IsNumeric = _('No');
	}
	if ($myrow['showoncert'] == 1) {
		$ShowOnCertText = _('Yes');
	} else {
		$ShowOnCertText = _('No');
	}
	if ($myrow['showonspec'] == 1) {
		$ShowOnSpecText = _('Yes');
	} else {
		$ShowOnSpecText = _('No');
	}
	if ($myrow['showontestplan'] == 1) {
		$ShowOnTestPlanText = _('Yes');
	} else {
		$ShowOnTestPlanText = _('No');
	}

	switch ($myrow['type']) {
	 	case 0; //textbox
	 		$TypeDisp='Text Box';
	 		break;
	 	case 1; //select box
	 		$TypeDisp='Select Box';
			break;
		case 2; //checkbox
			$TypeDisp='Check Box';
			break;
		case 3; //datebox
			$TypeDisp='Date Box';
			break;
		case 4; //range
			$TypeDisp='Range';
			break;
	} //end switch

	printf('<tr class="striped_row">
			<td class="number">%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href="%sSelectedQATest=%s">' .  _('Edit') . '</a></td>
			<td><a href="%sSelectedQATest=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this QA Test ?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['testid'],
			$myrow['name'],
			$myrow['method'],
			$myrow['groupby'],
			$myrow['units'],
			$TypeDisp,
			$myrow['defaultvalue'],
			$IsNumeric,
			$ShowOnCertText,
			$ShowOnSpecText,
			$ShowOnTestPlanText,
			$ActiveText,
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['testid'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['testid']);

	} //END WHILE LIST LOOP
	echo '</tbody></table><br />';
} //end of ifs and buts!
include('includes/footer.php');
?>