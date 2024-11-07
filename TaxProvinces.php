<?php

include('includes/session.php');
$Title = _('Dispatch Tax Provinces');
$ViewTopic = 'Tax';// Filename in ManualContents.php's TOC.
$BookMark = 'TaxProvinces';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		_('Dispatch Tax Province Maintenance') . '" />' . ' ' .
		_('Dispatch Tax Province Maintenance') . '</p>';

if( isset($_GET['SelectedTaxProvince']) )
	$SelectedTaxProvince = $_GET['SelectedTaxProvince'];
elseif(isset($_POST['SelectedTaxProvince']))
	$SelectedTaxProvince = $_POST['SelectedTaxProvince'];

if(isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if(ContainsIllegalCharacters($_POST['TaxProvinceName'])) {
		$InputError = 1;
		prnMsg( _('The tax province name cannot contain any of the illegal characters') . ' ' . '" \' - &amp; or a space','error');
	}
	if(trim($_POST['TaxProvinceName']) == '') {
		$InputError = 1;
		prnMsg( _('The tax province name may not be empty'), 'error');
	}

	if($_POST['SelectedTaxProvince']!='' AND $InputError !=1) {

		/*SelectedTaxProvince could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM taxprovinces
				WHERE taxprovinceid <> '" . $SelectedTaxProvince ."'
				AND taxprovincename " . LIKE . " '" . $_POST['TaxProvinceName'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The tax province cannot be renamed because another with the same name already exists.'),'error');
		} else {
			// Get the old name and check that the record still exists
			$sql = "SELECT taxprovincename FROM taxprovinces
						WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
			$result = DB_query($sql);
			if( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$OldTaxProvinceName = $myrow[0];
				$sql = "UPDATE taxprovinces
					SET taxprovincename='" . $_POST['TaxProvinceName'] . "'
					WHERE taxprovincename ".LIKE." '".$OldTaxProvinceName."'";
				$ErrMsg = _('Could not update tax province');
				$result = DB_query($sql, $ErrMsg);
				if(!$result) {
					prnMsg(_('Tax province name changed'),'success');
				}
			} else {
				$InputError = 1;
				prnMsg( _('The tax province no longer exists'),'error');
			}
		}
	} elseif($InputError !=1) {
		/*SelectedTaxProvince is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM taxprovinces
				WHERE taxprovincename " .LIKE. " '".$_POST['TaxProvinceName'] ."'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);

		if( $myrow[0] > 0 ) {

			$InputError = 1;
			prnMsg( _('The tax province cannot be created because another with the same name already exists'),'error');

		} else {

			$sql = "INSERT INTO taxprovinces (taxprovincename )
					VALUES ('" . $_POST['TaxProvinceName'] ."')";

			$ErrMsg = _('Could not add tax province');
			$result = DB_query($sql, $ErrMsg);

			$TaxProvinceID = DB_Last_Insert_ID('taxprovinces', 'taxprovinceid');
			$sql = "INSERT INTO taxauthrates (taxauthority, dispatchtaxprovince, taxcatid)
					SELECT taxauthorities.taxid, '" . $TaxProvinceID . "', taxcategories.taxcatid
					FROM taxauthorities CROSS JOIN taxcategories";
			$ErrMsg = _('Could not add tax authority rates for the new dispatch tax province. The rates of tax will not be able to be added - manual database interaction will be required to use this dispatch tax province');
			$result = DB_query($sql, $ErrMsg);
		}

		if(!$result) {
			prnMsg(_('Errors were encountered adding this tax province'),'error');
		} else {
			prnMsg(_('New tax province added'),'success');
		}
	}
	unset ($SelectedTaxProvince);
	unset ($_POST['SelectedTaxProvince']);
	unset ($_POST['TaxProvinceName']);

} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the tax province the ID is just a secure way to find the tax province
	$sql = "SELECT taxprovincename FROM taxprovinces
		WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
	$result = DB_query($sql);
	if( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('Cannot delete this tax province because it no longer exists'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldTaxProvinceName = $myrow[0];
		$sql= "SELECT COUNT(*) FROM locations WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if($myrow[0]>0) {
			prnMsg( _('Cannot delete this tax province because at least one stock location is defined to be inside this province'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock locations that refer to this tax province') . '</font>';
		} else {
			$sql = "DELETE FROM taxauthrates WHERE dispatchtaxprovince = '" . $SelectedTaxProvince . "'";
			$result = DB_query($sql);
			$sql = "DELETE FROM taxprovinces WHERE taxprovinceid = '" .$SelectedTaxProvince . "'";
			$result = DB_query($sql);
			prnMsg( $OldTaxProvinceName . ' ' . _('tax province and any tax rates set for it have been deleted'),'success');
		}
	} //end if
	unset ($SelectedTaxProvince);
	unset ($_GET['SelectedTaxProvince']);
	unset($_GET['delete']);
	unset ($_POST['SelectedTaxProvince']);
	unset ($_POST['TaxProvinceName']);
}

if(!isset($SelectedTaxProvince)) {

/* An tax province could be posted when one has been edited and is being updated
or GOT when selected for modification
SelectedTaxProvince will exist because it was sent with the page in a GET .
If its the first time the page has been displayed with no parameters
then none of the above are true and the list of account groups will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT taxprovinceid,
			taxprovincename
			FROM taxprovinces
			ORDER BY taxprovinceid";

	$ErrMsg = _('Could not get tax categories because');
	$result = DB_query($sql,$ErrMsg);

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="ascending">' . _('Tax Province') . '</th>
				<th colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while($myrow = DB_fetch_row($result)) {
		echo '<tr class="striped_row">
				<td>' . $myrow[1] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTaxProvince=' . $myrow[0] . '">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTaxProvince=' . $myrow[0] . '&amp;delete=1">' . _('Delete')  . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</tbody></table>';
} //end of ifs and buts!


if(isset($SelectedTaxProvince)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Tax Provinces') . '</a>
		</div>';
}

if(! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($SelectedTaxProvince)) {
		//editing an existing section

		$sql = "SELECT taxprovinceid,
				taxprovincename
				FROM taxprovinces
				WHERE taxprovinceid='" . $SelectedTaxProvince . "'";

		$result = DB_query($sql);
		if( DB_num_rows($result) == 0 ) {
			prnMsg( _('Could not retrieve the requested tax province, please try again.'),'warn');
			unset($SelectedTaxProvince);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['TaxProvinceName']  = $myrow['taxprovincename'];

			echo '<input type="hidden" name="SelectedTaxProvince" value="' . $myrow['taxprovinceid'] . '" />';
			echo '<fieldset>
					<legend>', _('Edit Tax Province'), '</legend>';
		}

	}  else {
		$_POST['TaxProvinceName']='';
		echo '<fieldset>
					<legend>', _('Create Tax Province'), '</legend>';
	}
	echo '<field>
			<td>' . _('Tax Province Name') . ':' . '</td>
			<td><input type="text" pattern="(?!^ *$)[^\\><+-]+" required="true" title="'._('The tax province cannot be left blank and includes illegal characters').'" placeholder="'._('Within 30 legal characters').'" name="TaxProvinceName" size="30" maxlength="30" value="' . $_POST['TaxProvinceName'] . '" /></td>
		</field>
		</fieldset>';

	echo '<br />
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Enter Information') . '" />
			</div>';

	echo '</div>
</form>';

} //end if record deleted no point displaying form to add record

echo '<br />
	<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . _('Tax Authorities and Rates Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxGroups.php">' . _('Tax Group Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxCategories.php">' . _('Tax Category Maintenance') .  '</a>
	</div>';

include('includes/footer.php');
?>
