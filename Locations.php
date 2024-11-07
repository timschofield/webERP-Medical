<?php
/* Defines the inventory stocking locations or warehouses */

include('includes/session.php');
$Title = _('Location Maintenance');// Screen identification.
$ViewTopic = 'Inventory';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Locations';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/supplier.png" title="',// Icon image.
	_('Inventory'), '" /> ',// Icon title.
	_('Location Maintenance'), '</p>';// Page title.

include('includes/CountriesArray.php');

if(isset($_GET['SelectedLocation'])) {
	$SelectedLocation = $_GET['SelectedLocation'];
} elseif(isset($_POST['SelectedLocation'])) {
	$SelectedLocation = $_POST['SelectedLocation'];
}

if(isset($_POST['submit'])) {
	$_POST['Managed']='off';
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	$_POST['LocCode']=mb_strtoupper($_POST['LocCode']);
	if(trim($_POST['LocCode']) == '') {
		$InputError = 1;
		prnMsg(_('The location code may not be empty'), 'error');
	}
	if($_POST['CashSaleCustomer']!='') {

		if($_POST['CashSaleBranch']=='') {
			prnMsg(_('A cash sale customer and branch are necessary to fully setup the counter sales functionality'),'error');
			$InputError =1;
		} else {//customer branch is set too ... check it ties up with a valid customer
			$sql = "SELECT * FROM custbranch
					WHERE debtorno='" . $_POST['CashSaleCustomer'] . "'
					AND branchcode='" . $_POST['CashSaleBranch'] . "'";

			$result = DB_query($sql);
			if(DB_num_rows($result)==0) {
				$InputError = 1;
				prnMsg(_('The cash sale customer for this location must be defined with both a valid customer code and a valid branch code for this customer'),'error');
			}
		}
	}//end of checking the customer - branch code entered

	if(isset($SelectedLocation) AND $InputError !=1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		if(isset($_POST['Managed']) and $_POST['Managed'] == 'on') {
			$_POST['Managed'] = 1;
		} else {
			$_POST['Managed'] = 0;
		}

		$sql = "UPDATE locations SET loccode='" . $_POST['LocCode'] . "',
									locationname='" . $_POST['LocationName'] . "',
									deladd1='" . $_POST['DelAdd1'] . "',
									deladd2='" . $_POST['DelAdd2'] . "',
									deladd3='" . $_POST['DelAdd3'] . "',
									deladd4='" . $_POST['DelAdd4'] . "',
									deladd5='" . $_POST['DelAdd5'] . "',
									deladd6='" . $_POST['DelAdd6'] . "',
									tel='" . $_POST['Tel'] . "',
									fax='" . $_POST['Fax'] . "',
									email='" . $_POST['Email'] . "',
									contact='" . $_POST['Contact'] . "',
									taxprovinceid = '" . $_POST['TaxProvince'] . "',
									cashsalecustomer ='" . $_POST['CashSaleCustomer'] . "',
									cashsalebranch ='" . $_POST['CashSaleBranch'] . "',
									managed = '" . $_POST['Managed'] . "',
									internalrequest = '" . $_POST['InternalRequest'] . "',
									usedforwo = '" . $_POST['UsedForWO'] . "',
									glaccountcode = '" . $_POST['GLAccountCode'] . "',
									allowinvoicing = '" . $_POST['AllowInvoicing'] . "'
						WHERE loccode = '" . $SelectedLocation . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedLocation . ' ' . _('location record because');
		$DbgMsg = _('The SQL used to update the location record was');

		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg(_('The location record has been updated'),'success');
		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['DelAdd1']);
		unset($_POST['DelAdd2']);
		unset($_POST['DelAdd3']);
		unset($_POST['DelAdd4']);
		unset($_POST['DelAdd5']);
		unset($_POST['DelAdd6']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['TaxProvince']);
		unset($_POST['Managed']);
		unset($_POST['CashSaleCustomer']);
		unset($_POST['CashSaleBranch']);
		unset($SelectedLocation);
		unset($_POST['Contact']);
		unset($_POST['InternalRequest']);
		unset($_POST['UsedForWO']);
		unset($_POST['GLAccountCode']);
		unset($_POST['AllowInvoicing']);


	} elseif($InputError !=1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		if($_POST['Managed'] == 'on') {
			$_POST['Managed'] = 1;
		} else {
			$_POST['Managed'] = 0;
		}

		/*SelectedLocation is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$sql = "INSERT INTO locations (loccode,
										locationname,
										deladd1,
										deladd2,
										deladd3,
										deladd4,
										deladd5,
										deladd6,
										tel,
										fax,
										email,
										contact,
										taxprovinceid,
										cashsalecustomer,
										cashsalebranch,
										managed,
										internalrequest,
										usedforwo,
										glaccountcode,
										allowinvoicing)
						VALUES ('" . $_POST['LocCode'] . "',
								'" . $_POST['LocationName'] . "',
								'" . $_POST['DelAdd1'] ."',
								'" . $_POST['DelAdd2'] ."',
								'" . $_POST['DelAdd3'] . "',
								'" . $_POST['DelAdd4'] . "',
								'" . $_POST['DelAdd5'] . "',
								'" . $_POST['DelAdd6'] . "',
								'" . $_POST['Tel'] . "',
								'" . $_POST['Fax'] . "',
								'" . $_POST['Email'] . "',
								'" . $_POST['Contact'] . "',
								'" . $_POST['TaxProvince'] . "',
								'" . $_POST['CashSaleCustomer'] . "',
								'" . $_POST['CashSaleBranch'] . "',
								'" . $_POST['Managed'] . "',
								'" . $_POST['InternalRequest'] . "',
								'" . $_POST['UsedForWO'] . "',
								'" . $_POST['GLAccountCode'] . "',
								'" . $_POST['AllowInvoicing'] . "')";

		$ErrMsg = _('An error occurred inserting the new location record because');
		$DbgMsg = _('The SQL used to insert the location record was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg(_('The new location record has been added'),'success');

	/* Also need to add LocStock records for all existing stock items */

		$sql = "INSERT INTO locstock (
					loccode,
					stockid,
					quantity,
					reorderlevel)
			SELECT '" . $_POST['LocCode'] . "',
				stockmaster.stockid,
				0,
				0
			FROM stockmaster";

		$ErrMsg = _('An error occurred inserting the new location stock records for all pre-existing parts because');
		$DbgMsg = _('The SQL used to insert the new stock location records was');
		$result = DB_query($sql,$ErrMsg, $DbgMsg);
		prnMsg('........ ' . _('and new stock locations inserted for all existing stock items for the new location'), 'success');

	/* Also need to add locationuser records for all existing users*/
		$sql = "INSERT INTO locationusers (userid, loccode, canview, canupd)
				SELECT www_users.userid,
				locations.loccode,
				1,
				1
				FROM www_users CROSS JOIN locations
				LEFT JOIN locationusers
				ON www_users.userid = locationusers.userid
				AND locations.loccode = locationusers.loccode
				WHERE locationusers.userid IS NULL
				AND locations.loccode='". $_POST['LocCode'] . "';";

		$ErrMsg = _('The users/locations that need user location records created cannot be retrieved because');
		$Result = DB_query($sql,$ErrMsg);
		prnMsg(_('Existing users have been authorized for this location'),'success');

		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['DelAdd1']);
		unset($_POST['DelAdd2']);
		unset($_POST['DelAdd3']);
		unset($_POST['DelAdd4']);
		unset($_POST['DelAdd5']);
		unset($_POST['DelAdd6']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['TaxProvince']);
		unset($_POST['CashSaleCustomer']);
		unset($_POST['CashSaleBranch']);
		unset($_POST['Managed']);
		unset($SelectedLocation);
		unset($_POST['Contact']);
		unset($_POST['InternalRequest']);
		unset($_POST['UsedForWO']);
		unset($_POST['GLAccountCode']);
		unset($_POST['AllowInvoicing']);
	}


	/* Go through the tax authorities for all Locations deleting or adding TaxAuthRates records as necessary */

	$result = DB_query("SELECT COUNT(taxid) FROM taxauthorities");
	$NoTaxAuths =DB_fetch_row($result);

	$DispTaxProvincesResult = DB_query("SELECT taxprovinceid FROM locations");
	$TaxCatsResult = DB_query("SELECT taxcatid FROM taxcategories");
	if(DB_num_rows($TaxCatsResult) > 0) {// This will only work if there are levels else we get an error on seek.

		while ($myrow=DB_fetch_row($DispTaxProvincesResult)) {
			/*Check to see there are TaxAuthRates records set up for this TaxProvince */
			$NoTaxRates = DB_query("SELECT taxauthority FROM taxauthrates WHERE dispatchtaxprovince='" . $myrow[0] . "'");

			if(DB_num_rows($NoTaxRates) < $NoTaxAuths[0]) {

				/*First off delete any tax authoritylevels already existing */
				$DelTaxAuths = DB_query("DELETE FROM taxauthrates WHERE dispatchtaxprovince='" . $myrow[0] . "'");

				/*Now add the new TaxAuthRates required */
				while ($CatRow = DB_fetch_row($TaxCatsResult)) {
					$sql = "INSERT INTO taxauthrates (taxauthority,
										dispatchtaxprovince,
										taxcatid)
							SELECT taxid,
								'" . $myrow[0] . "',
								'" . $CatRow[0] . "'
							FROM taxauthorities";

					$InsTaxAuthRates = DB_query($sql);
				}
				DB_data_seek($TaxCatsResult,0);
			}
		}
	}


} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS
	$sql= "SELECT COUNT(*) FROM salesorders WHERE fromstkloc='". $SelectedLocation . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this location because sales orders have been created delivering from this location'),'warn');
		echo _('There are') . ' ' . $myrow[0] . ' ' . _('sales orders with this Location code');
	} else {
		$sql= "SELECT COUNT(*) FROM stockmoves WHERE stockmoves.loccode='" . $SelectedLocation . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete this location because stock movements have been created using this location'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock movements with this Location code');

		} else {
			$sql= "SELECT COUNT(*) FROM locstock
					WHERE locstock.loccode='". $SelectedLocation . "'
					AND locstock.quantity !=0";
			$result = DB_query($sql);
			$myrow = DB_fetch_row($result);
			if($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this location because location stock records exist that use this location and have a quantity on hand not equal to 0'),'warn');
				echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock items with stock on hand at this location code');
			} else {
				$sql= "SELECT COUNT(*) FROM www_users
						WHERE www_users.defaultlocation='" . $SelectedLocation . "'";
				$result = DB_query($sql);
				$myrow = DB_fetch_row($result);
				if($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this location because it is the default location for a user') . '. ' . _('The user record must be modified first'),'warn');
					echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('users using this location as their default location');
				} else {
					$sql= "SELECT COUNT(*) FROM bom
							WHERE bom.loccode='" . $SelectedLocation . "'";
					$result = DB_query($sql);
					$myrow = DB_fetch_row($result);
					if($myrow[0]>0) {
						$CancelDelete = 1;
						prnMsg(_('Cannot delete this location because it is the default location for a bill of material') . '. ' . _('The bill of materials must be modified first'),'warn');
						echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('bom components using this location');
					} else {
						$sql= "SELECT COUNT(*) FROM workcentres
								WHERE workcentres.location='" . $SelectedLocation . "'";
						$result = DB_query($sql);
						$myrow = DB_fetch_row($result);
						if($myrow[0]>0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this location because it is used by some work centre records'),'warn');
							echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('works centres using this location');
						} else {
							$sql= "SELECT COUNT(*) FROM workorders
									WHERE workorders.loccode='" . $SelectedLocation . "'";
							$result = DB_query($sql);
							$myrow = DB_fetch_row($result);
							if($myrow[0]>0) {
								$CancelDelete = 1;
								prnMsg(_('Cannot delete this location because it is used by some work order records'),'warn');
								echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('work orders using this location');
							} else {
								$sql= "SELECT COUNT(*) FROM custbranch
										WHERE custbranch.defaultlocation='" . $SelectedLocation . "'";
								$result = DB_query($sql);
								$myrow = DB_fetch_row($result);
								if($myrow[0]>0) {
									$CancelDelete = 1;
									prnMsg(_('Cannot delete this location because it is used by some branch records as the default location to deliver from'),'warn');
									echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('branches set up to use this location by default');
								} else {
									$sql= "SELECT COUNT(*) FROM purchorders WHERE intostocklocation='" . $SelectedLocation . "'";
									$result = DB_query($sql);
									$myrow = DB_fetch_row($result);
									if($myrow[0]>0) {
										$CancelDelete = 1;
										prnMsg(_('Cannot delete this location because it is used by some purchase order records as the location to receive stock into'),'warn');
										echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('purchase orders set up to use this location as the receiving location');
									}
								}
							}
						}
					}
				}
			}
		}
	}
	if(! $CancelDelete) {

		/* need to figure out if this location is the only one in the same tax province */
		$result = DB_query("SELECT taxprovinceid FROM locations
							WHERE loccode='" . $SelectedLocation . "'");
		$TaxProvinceRow = DB_fetch_row($result);
		$result = DB_query("SELECT COUNT(taxprovinceid) FROM locations
							WHERE taxprovinceid='" .$TaxProvinceRow[0] . "'");
		$TaxProvinceCount = DB_fetch_row($result);
		if($TaxProvinceCount[0]==1) {
		/* if its the only location in this tax authority then delete the appropriate records in TaxAuthLevels */
			$result = DB_query("DELETE FROM taxauthrates
								WHERE dispatchtaxprovince='" . $TaxProvinceRow[0] . "'");
		}

		$result= DB_query("DELETE FROM locstock WHERE loccode ='" . $SelectedLocation . "'");
		$result = DB_query("DELETE FROM locationusers WHERE loccode='" . $SelectedLocation . "'");
		$result = DB_query("DELETE FROM locations WHERE loccode='" . $SelectedLocation . "'");

		prnMsg(_('Location') . ' ' . $SelectedLocation . ' ' . _('has been deleted') . '!', 'success');
		unset ($SelectedLocation);
	}//end if Delete Location
	unset($SelectedLocation);
	unset($_GET['delete']);
}

if(!isset($SelectedLocation)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedLocation will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Locations will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT loccode,
				locationname,
				taxprovinces.taxprovincename as description,
				glaccountcode,
				allowinvoicing,
				managed
			FROM locations INNER JOIN taxprovinces
			ON locations.taxprovinceid=taxprovinces.taxprovinceid";
	$result = DB_query($sql);

	if(DB_num_rows($result)==0) {
		prnMsg(_('There are no locations that match up with a tax province record to display. Check that tax provinces are set up for all dispatch locations'),'error');
	}

	echo '<table class="selection">
		<thead>
		<tr>
			<th class="ascending">', _('Location Code'), '</th>
			<th class="ascending">', _('Location Name'), '</th>
			<th class="ascending">', _('Tax Province'), '</th>
			<th class="ascending">', _('GL Account Code'), '</th>
			<th class="ascending">', _('Allow Invoicing'), '</th>
			<th class="noprint" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

while ($myrow = DB_fetch_array($result)) {
/* warehouse management not implemented ... yet
	if($myrow['managed'] == 1) {
		$myrow['managed'] = _('Yes');
	} else {
		$myrow['managed'] = _('No');
	}
*/
	printf('<tr class="striped_row">
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="centre">%s</td>
			<td class="noprint"><a href="%sSelectedLocation=%s">' . _('Edit') . '</a></td>
			<td class="noprint"><a href="%sSelectedLocation=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this inventory location?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['loccode'],
			$myrow['locationname'],
			$myrow['description'],
			($myrow['glaccountcode']!='' ? $myrow['glaccountcode'] : '&nbsp;'),// Use a non-breaking space to avoid an empty cell in a HTML table.
			($myrow['allowinvoicing']==1 ? _('Yes') : _('No')),
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['loccode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['loccode']);
	}
	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!

if(isset($SelectedLocation)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Records') . '</a>';
}

if(!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($SelectedLocation)) {
		//editing an existing Location

		$sql = "SELECT loccode,
					locationname,
					deladd1,
					deladd2,
					deladd3,
					deladd4,
					deladd5,
					deladd6,
					contact,
					fax,
					tel,
					email,
					taxprovinceid,
					cashsalecustomer,
					cashsalebranch,
					managed,
					internalrequest,
					usedforwo,
					glaccountcode,
					allowinvoicing
				FROM locations
				WHERE loccode='" . $SelectedLocation . "'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['LocCode'] = $myrow['loccode'];
		$_POST['LocationName'] = $myrow['locationname'];
		$_POST['DelAdd1'] = $myrow['deladd1'];
		$_POST['DelAdd2'] = $myrow['deladd2'];
		$_POST['DelAdd3'] = $myrow['deladd3'];
		$_POST['DelAdd4'] = $myrow['deladd4'];
		$_POST['DelAdd5'] = $myrow['deladd5'];
		$_POST['DelAdd6'] = $myrow['deladd6'];
		$_POST['Contact'] = $myrow['contact'];
		$_POST['Tel'] = $myrow['tel'];
		$_POST['Fax'] = $myrow['fax'];
		$_POST['Email'] = $myrow['email'];
		$_POST['TaxProvince'] = $myrow['taxprovinceid'];
		$_POST['CashSaleCustomer'] = $myrow['cashsalecustomer'];
		$_POST['CashSaleBranch'] = $myrow['cashsalebranch'];
		$_POST['Managed'] = $myrow['managed'];
		$_POST['InternalRequest'] = $myrow['internalrequest'];
		$_POST['UsedForWO'] = $myrow['usedforwo'];
		$_POST['GLAccountCode'] = $myrow['glaccountcode'];
		$_POST['AllowInvoicing'] = $myrow['allowinvoicing'];

		echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
		echo '<input type="hidden" name="LocCode" value="' . $_POST['LocCode'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . _('Amend Location details') . '</legend>';
		echo '<field>
				<label for="LocCode">' . _('Location Code') . ':</label>
				<fieldtext>' . $_POST['LocCode'] . '</fieldtext>
			</field>';
	} else {//end of if $SelectedLocation only do the else when a new record is being entered
		if(!isset($_POST['LocCode'])) {
			$_POST['LocCode'] = '';
		}
		echo '<fieldset>
				<legend>' . _('New Location details') . '</legend>';
		echo '<field>
				<label for="LocCode">' . _('Location Code') . ':</label>
				<input type="text" autofocus="autofocus" required="required" title="" data-type="no-illegal-chars" name="LocCode" value="' . $_POST['LocCode'] . '" size="5" maxlength="5" /></td>
				<fieldhelp>' . _('Enter up to five characters for the inventory location code') . '</fieldhelp
			</field>';
	}
	if(!isset($_POST['LocationName'])) {
		$_POST['LocationName'] = '';
	}
	if(!isset($_POST['Contact'])) {
		$_POST['Contact'] = '';
	}
	if(!isset($_POST['DelAdd1'])) {
		$_POST['DelAdd1'] = ' ';
	}
	if(!isset($_POST['DelAdd2'])) {
		$_POST['DelAdd2'] = '';
	}
	if(!isset($_POST['DelAdd3'])) {
		$_POST['DelAdd3'] = '';
	}
	if(!isset($_POST['DelAdd4'])) {
		$_POST['DelAdd4'] = '';
	}
	if(!isset($_POST['DelAdd5'])) {
		$_POST['DelAdd5'] = '';
	}
	if(!isset($_POST['DelAdd6'])) {
		$_POST['DelAdd6'] = '';
	}
	if(!isset($_POST['Tel'])) {
		$_POST['Tel'] = '';
	}
	if(!isset($_POST['Fax'])) {
		$_POST['Fax'] = '';
	}
	if(!isset($_POST['Email'])) {
		$_POST['Email'] = '';
	}
	if(!isset($_POST['CashSaleCustomer'])) {
		$_POST['CashSaleCustomer'] = '';
	}
	if(!isset($_POST['CashSaleBranch'])) {
		$_POST['CashSaleBranch'] = '';
	}
	if(!isset($_POST['Managed'])) {
		$_POST['Managed'] = 0;
	}
	if(!isset($_POST['AllowInvoicing'])) {
		$_POST['AllowInvoicing'] = 1;// If not set, set value to "Yes".
	}
	if(!isset($_POST['GLAccountCode'])) {
		$_POST['GLAccountCode'] = 1;
	}

	echo '<field>
			<label for="LocationName">' . _('Location Name') . ':' . '</label>
			<input type="text" name="LocationName" required="required" value="'. $_POST['LocationName'] . '" title="" namesize="51" maxlength="50" />
			<fieldhelp>' . _('Enter the inventory location name, this could be either a warehouse or a factory') . '</fieldhelp>
		</field>
		<field>
			<label for="Contact">' . _('Contact for deliveries') . ':' . '</label>
			<input type="text" name="Contact" required="required" value="' . $_POST['Contact'] . '" title="" size="31" maxlength="30" />
			<fieldhelp>' . _('Enter the name of the responsible person to contact for this inventory location') . '</fieldhelp>
		</field>
		<field>
			<label for="DelAdd1">' . _('Delivery Address 1 (Building)') . ':' . '</label>
			<input type="text" name="DelAdd1" value="' . $_POST['DelAdd1'] . '" size="41" maxlength="40" />
		</field>
		<field>
			<label for="DelAdd2">' . _('Delivery Address 2 (Street)') . ':' . '</label>
			<input type="text" name="DelAdd2" value="' . $_POST['DelAdd2'] . '" size="41" maxlength="40" />
		</field>
		<field>
			<label for="DelAdd3">' . _('Delivery Address 3 (Suburb)') . ':' . '</label>
			<input type="text" name="DelAdd3" value="' . $_POST['DelAdd3'] . '" size="41" maxlength="40" />
		</field>
		<field>
			<label for="DelAdd4">' . _('Delivery Address 4 (City)') . ':' . '</label>
			<input type="text" name="DelAdd4" value="' . $_POST['DelAdd4'] . '" size="41" maxlength="40" />
		</field>
		<field>
			<label for="DelAdd5">' . _('Delivery Address 5 (Zip Code)') . ':' . '</label>
			<input type="text" name="DelAdd5" value="' . $_POST['DelAdd5'] . '" size="21" maxlength="20" />
		</field>
		<field>
			<label for="DelAdd6">' . _('Country') . ':</label>
			<select name="DelAdd6">';
		foreach ($CountriesArray as $CountryEntry => $CountryName) {
			if(isset($_POST['DelAdd6']) AND (strtoupper($_POST['DelAdd6']) == strtoupper($CountryName))) {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} elseif(!isset($_POST['Address6']) AND $CountryName == "") {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} else {
				echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
			}
		}
		echo '</select>
		</field>
		<field>
			<label for="Tel">' . _('Telephone No') . ':' . '</label>
			<input name="Tel" type="tel" pattern="[0-9+\-\s()]*" value="' . $_POST['Tel'] . '" size="31" maxlength="30" title="" />
			<fieldhelp>' . _('The phone number should consist of numbers, spaces, parentheses, or the + character') . '</fieldhelp>
		</field>
		<field>
			<label for="Fax">' . _('Facsimile No') . ':' . '</label>
			<input name="Fax" type="tel" pattern="[0-9+\-\s()]*" value="' . $_POST['Fax'] . '" size="31" maxlength="30" title=""/>
			<fieldhelp>' . _('The fax number should consist of numbers, parentheses, spaces or the + character') . '</fieldhelp>
		</field>
		<field>
			<label for="Email">', _('Email'), ':</label>
			<input id="Email" maxlength="55" name="Email" size="31" type="email" value="', $_POST['Email'], '" />
			<fieldhelp>', _('The email address should be an email format such as adm@weberp.org'), '</fieldhelp>
		</field>
		<field>
			<label for="TaxProvince">' . _('Tax Province') . ':' . '</label>
			<select name="TaxProvince">';

	$TaxProvinceResult = DB_query("SELECT taxprovinceid, taxprovincename FROM taxprovinces");
	while ($myrow=DB_fetch_array($TaxProvinceResult)) {
		if($_POST['TaxProvince']==$myrow['taxprovinceid']) {
			echo '<option selected="selected" value="' . $myrow['taxprovinceid'] . '">' . $myrow['taxprovincename'] . '</option>';
		} else {
			echo '<option value="' . $myrow['taxprovinceid'] . '">' . $myrow['taxprovincename'] . '</option>';
		}
	}

	echo '</select>
		</field>
		<field>
			<label for="CashSaleCustomer">' . _('Default Counter Sales Customer Code') . ':' . '</label>
			<input type="text" name="CashSaleCustomer" data-type="no-illegal-chars" title="" value="' . $_POST['CashSaleCustomer'] . '" size="11" maxlength="10" />
			<fieldhelp>' . _('If counter sales are being used for this location then an existing customer account code needs to be entered here. All sales created from the counter sales will be recorded against this customer account') . '</fieldhelp>
		</field>
		<field>
			<label for="CashSaleBranch">' . _('Counter Sales Branch Code') . ':' . '</label>
			<input type="text" name="CashSaleBranch" data-type="no-illegal-chars" title="" value="' . $_POST['CashSaleBranch'] . '" size="11" maxlength="10" />
			<fieldhelp>' . _('If counter sales are being used for this location then an existing customer branch code for the customer account code entered above needs to be entered here. All sales created from the counter sales will be recorded against this branch') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="InternalRequest">' . _('Allow internal requests?') . ':</label>
			<select name="InternalRequest">';
	if($_POST['InternalRequest']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['InternalRequest']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="UsedForWO">' . _('Use for Work Order Productions?') . ':</label>
			<select name="UsedForWO">';
	if($_POST['UsedForWO']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['UsedForWO']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select>
		</field>';
	// Location's ledger account:
	echo '<field title="">
			<label for="GLAccountCode">', _('GL Account Code'), ':</label>
			<input data-type="no-illegal-chars" id="GLAccountCode" maxlength="20" name="GLAccountCode" size="20" type="text" value="', $_POST['GLAccountCode'], '" />
			<fieldhelp>', _('Enter the GL account for this location, or leave it blank if not needed'), '</fieldhelp>
		</field>';
	// Allow or deny the invoicing of items in this location:
	echo '<field>
			<label for="AllowInvoicing">', _('Allow Invoicing'), ':</label>
			<select name="AllowInvoicing">
				<option', ($_POST['AllowInvoicing']==1 ? ' selected="selected"' : ''), ' value="1">', _('Yes'), '</option>
				<option', ($_POST['AllowInvoicing']==0 ? ' selected="selected"' : ''), ' value="0">', _('No'), '</option>
			</select>
			<fieldhelp>', _('Use this parameter to indicate whether these inventory location allows or denies the invoicing of its items.'), '</fieldhelp>
		</field>';

	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
		</form>';

}//end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
