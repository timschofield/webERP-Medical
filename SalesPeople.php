<?php
/* $Id: SalesPeople.php 4591 2011-06-09 10:33:38Z daintree $*/

include('includes/session.inc');
$title = _('Patient Employers Maintenance');
include('includes/header.inc');

if (isset($_GET['SelectedEmployer'])){
	$SelectedEmployer = strtoupper($_GET['SelectedEmployer']);
} elseif(isset($_POST['SelectedEmployer'])){
	$SelectedEmployer = strtoupper($_POST['SelectedEmployer']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $title.'</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	if (strlen($_POST['EmployerCode']) > 3) {
		$InputError = 1;
		prnMsg(_('The employer code must be three characters or less long'),'error');
		$Errors[$i] = 'EmployerCode';
		$i++;
	} elseif (strlen($_POST['EmployerCode'])==0 OR $_POST['EmployerCode']=='') {
		$InputError = 1;
		prnMsg(_('The employer code cannot be empty'),'error');
		$Errors[$i] = 'EmployerCode';
		$i++;
	} elseif (strlen($_POST['EmployerName']) > 30) {
		$InputError = 1;
		prnMsg(_('The employers name must be thirty characters or less long'),'error');
		$Errors[$i] = 'EmployerName';
		$i++;
	} elseif (strlen($_POST['SManTel']) > 20) {
		$InputError = 1;
		prnMsg(_('The employers telephone number must be twenty characters or less long'),'error');

	} elseif (strlen($_POST['SManFax']) > 20) {
		$InputError = 1;
		prnMsg(_('The employers telephone number must be twenty characters or less long'),'error');

	}

	if (!isset($_POST['SManTel'])){
	  $_POST['SManTel']='';
	}
	if (!isset($_POST['SManFax'])){
	  $_POST['SManFax']='';
	}
	if (!isset($_POST['CommissionRate1'])){
	  $_POST['CommissionRate1']=0;
	}
	if (!isset($_POST['CommissionRate2'])){
	  $_POST['CommissionRate2']=0;
	}
	if (!isset($_POST['Breakpoint'])){
	  $_POST['Breakpoint']=0;
	}
	if (!isset($_POST['Current'])){
	  $_POST['Current']=0;
	}

	if (isset($SelectedEmployer) AND $InputError !=1) {

		/*SelectedEmployer could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE salesman SET salesmanname='" . $_POST['EmployerName'] . "',
						commissionrate1='" . $_POST['CommissionRate1'] . "',
						smantel='" . $_POST['SManTel'] . "',
						smanfax='" . $_POST['SManFax'] . "',
						breakpoint='" . $_POST['Breakpoint'] . "',
						commissionrate2='" . $_POST['CommissionRate2'] . "',
						current='" . $_POST['Current'] . "'
				WHERE salesmancode = '".$SelectedEmployer."'";

		$msg = _('The record for') . ' ' . $_POST['EmployerName'] . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new Sales-person form */

		$sql = "INSERT INTO salesman (salesmancode,
						salesmanname,
						commissionrate1,
						commissionrate2,
						breakpoint,
						smantel,
						smanfax,
						current)
				VALUES ('" . $_POST['EmployerCode'] . "',
					'" . $_POST['EmployerName'] . "',
					'" . $_POST['CommissionRate1'] . "',
					'" . $_POST['CommissionRate2'] . "',
					'" . $_POST['Breakpoint'] . "',
					'" . $_POST['SManTel'] . "',
					'" . $_POST['SManFax'] . "',
					'" . $_POST['Current'] . "'
					)";

		$msg = _('A new record has been added for') . ' ' . $_POST['EmployerName'];
	}
	if ($InputError !=1) {
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The insert or update of the employer failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$result = DB_query($sql,$db,$ErrMsg, $DbgMsg);

		prnMsg($msg , 'success');

		unset($SelectedEmployer);
		unset($_POST['EmployerCode']);
		unset($_POST['EmployerName']);
		unset($_POST['CommissionRate1']);
		unset($_POST['CommissionRate2']);
		unset($_POST['Breakpoint']);
		unset($_POST['SManFax']);
		unset($_POST['SManTel']);
		unset($_POST['Current']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorsMaster'


	$sql= "SELECT COUNT(branchcode) AS branches FROM custbranch WHERE custbranch.salesman='".$SelectedEmployer."'";
	$result = DB_query($sql,$db);

	$myrow = DB_fetch_array($result);
	if ($myrow['branches']>0) {
		prnMsg(_('Cannot delete this salesperson because branches are set up referring to them') . ' - ' . _('first alter the branches concerned') . '<br />' . _('There are') . ' ' . $myrow['branches'] . ' ' . _('branches that refer to this salesperson'),'error');

	} else {

		$sql= "SELECT COUNT(salesperson) AS salespeople FROM salesanalysis WHERE salesanalysis.salesperson='".$SelectedEmployer."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_array($result);
		if ($myrow['salespeople']>0) {
			prnMsg(_('Cannot delete this salesperson because sales analysis records refer to them') , '<br />' . _('There are') . ' ' . $myrow['salespeople'] . ' ' . _('sales analysis records that refer to this salesperson'),'error');
		} else {

			$sql="DELETE FROM salesman WHERE salesmancode='".$SelectedEmployer."'";
			$ErrMsg = _('The employer could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);

			prnMsg(_('Employer') . ' ' . $SelectedEmployer . ' ' . _('has been deleted from the database'),'success');
			unset ($SelectedEmployer);
			unset($delete);
		}
	} //end if Sales-person used in GL accounts

}

if (!isset($SelectedEmployer)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedEmployer will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Sales-persons will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT salesmancode,
			salesmanname,
			smantel,
			smanfax
		FROM salesman";
	$result = DB_query($sql,$db);

	echo '<table class="selection">';
	echo '<tr><th>' . _('Code') . '</th>
		<th>' . _('Name') . '</th>
		<th>' . _('Telephone') . '</th>
		<th>' . _('Facsimile') . '</th></tr>';
	$k=0;
	while ($myrow=DB_fetch_array($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		if ($myrow['current'] == 1) {
			$ActiveText = _('Yes');
		} else {
			$ActiveText = _('No');
		}

		printf('
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href="%sSelectedEmployer=%s">'. _('Edit') . '</a></td>
			<td><a href="%sSelectedEmployer=%s&delete=1">' . _('Delete') . '</a></td>
		</tr>',
			$myrow['salesmancode'],
			$myrow['salesmanname'],
			$myrow['smantel'],
			$myrow['smanfax'],
			htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
			$myrow['salesmancode'],
			htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
			$myrow['salesmancode']);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedEmployer)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Sales People') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedEmployer)) {
		//editing an existing Sales-person

		$sql = "SELECT salesmancode,
				salesmanname,
				smantel,
				smanfax
			FROM salesman
			WHERE salesmancode='".$SelectedEmployer."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['EmployerCode'] = $myrow['salesmancode'];
		$_POST['EmployerName'] = $myrow['salesmanname'];
		$_POST['SManTel'] = $myrow['smantel'];
		$_POST['SManFax'] = $myrow['smanfax'];

		echo '<input type="hidden" name="SelectedEmployer" value="' . $SelectedEmployer . '" />';
		echo '<input type="hidden" name="EmployerCode" value="' . $_POST['EmployerCode'] . '" />';
		echo '<table class="selection"> <tr><td>' . _('Salesperson code') . ':</td><td>';
		echo $_POST['EmployerCode'] . '</td></tr>';

	} else { //end of if $SelectedEmployer only do the else when a new record is being entered

		echo '<table class="selection"><tr><td>' . _('Employer code') . ':</td>
			<td><input type="text" '. (in_array('EmployerCode',$Errors) ? 'class="inputerror"' : '' ) .' name="EmployerCode" size="3" maxlength="3" /></td></tr>';
	}
	if (!isset($_POST['EmployerName'])){
	  $_POST['EmployerName']='';
	}
	if (!isset($_POST['SManTel'])){
	  $_POST['SManTel']='';
	}
	if (!isset($_POST['SManFax'])){
	  $_POST['SManFax']='';
	}
	if (!isset($_POST['CommissionRate1'])){
	  $_POST['CommissionRate1']=0;
	}
	if (!isset($_POST['CommissionRate2'])){
	  $_POST['CommissionRate2']=0;
	}
	if (!isset($_POST['Breakpoint'])){
	  $_POST['Breakpoint']=0;
	}
	if (!isset($_POST['Current'])){
	  $_POST['Current']=0;
	}

	echo '<tr><td>' . _('Employers Name') . ':</td><td><input type="text" '. (in_array('EmployerName',$Errors) ? 'class="inputerror"' : '' ) .' name="EmployerName"  size="48" maxlength="48" value="' . $_POST['EmployerName'] . '" /></td></tr>';
	echo '<tr><td>' . _('Telephone No') . ':</td><td><input type="text" name="SManTel" size="20" maxlength="20" value="' . $_POST['SManTel'] . '" /></td></tr>';
	echo '<tr><td>' . _('Facsimile No') . ':</td><td><input type="text" name="SManFax" size="20" maxlength="20" value="' . $_POST['SManFax'] . '" /></td></tr>';

	echo '</table>';

	echo '<br /><div class="centre"><button type="submit" name="submit">' . _('Enter Information') . '</button></div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record


include('includes/footer.inc');
?>