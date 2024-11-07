<?php

include('includes/session.php');
$Title = _('Maintenance Of Petty Cash Tabs');
/* webERP manual links before header.php */
$ViewTopic = 'PettyCash';
$BookMark = 'PCTabSetup';
include('includes/header.php');
echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Payment Entry'), '" alt="" />', ' ', $Title, '
	</p>';
if (isset($_POST['SelectedTab'])) {
	$SelectedTab = mb_strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])) {
	$SelectedTab = mb_strtoupper($_GET['SelectedTab']);
}
if (isset($_POST['Cancel'])) {
	unset($SelectedTab);
	unset($_POST['TabCode']);
	unset($_POST['SelectUser']);
	unset($_POST['SelectTabs']);
	unset($_POST['SelectCurrency']);
	unset($_POST['TabLimit']);
	unset($_POST['SelectAssigner']);
	unset($_POST['SelectAuthoriserCash']);
	unset($_POST['SelectAuthoriserExpenses']);
	unset($_POST['GLAccountCash']);
	unset($_POST['GLAccountPcashTab']);
}
if (isset($_POST['Submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	//first off validate inputs sensible
	if ($_POST['TabCode'] == '' or $_POST['TabCode'] == ' ' or $_POST['TabCode'] == '  ') {
		$InputError = 1;
		prnMsg('<br />' . _('The Tab code cannot be an empty string or spaces'), 'error');
	} elseif (mb_strlen($_POST['TabCode']) > 20) {
		$InputError = 1;
		prnMsg(_('The Tab code must be twenty characters or less long'), 'error');
	} elseif (($_POST['SelectUser']) == '') {
		$InputError = 1;
		prnMsg(_('You must select a User for this tab'), 'error');
	} elseif (($_POST['SelectTabs']) == '') {
		$InputError = 1;
		prnMsg(_('You must select a type of tab from the list'), 'error');
	} elseif (($_POST['SelectAssigner']) == '') {
		$InputError = 1;
		prnMsg(_('You must select a User to assign cash to this tab'), 'error');
	} elseif (($_POST['SelectAuthoriserCash']) == '') {
		$InputError = 1;
		prnMsg(_('You must select a User to authorise this tab'), 'error');
	} elseif (($_POST['GLAccountCash']) == '') {
		$InputError = 1;
		prnMsg(_('You must select a General ledger code for the cash to be assigned from'), 'error');
	} elseif (($_POST['GLAccountPcashTab']) == '') {
		$InputError = 1;
		prnMsg(_('You must select a General ledger code for this petty cash tab'), 'error');
	} elseif (($_POST['TaxGroup']) === '0') {
		$InputError = 1;
		prnMsg(_('You must select a tax group'), 'error');
	}
	if (isset($SelectedTab) and $InputError != 1) {
		$SQL = "UPDATE pctabs SET usercode = '" . $_POST['SelectUser'] . "',
									typetabcode = '" . $_POST['SelectTabs'] . "',
									currency = '" . $_POST['SelectCurrency'] . "',
									tablimit = '" . filter_number_format($_POST['TabLimit']) . "',
									assigner = '" . $_POST['SelectAssigner'] . "',
									authorizer = '" . $_POST['SelectAuthoriserCash'] . "',
									authorizerexpenses = '" . $_POST['SelectAuthoriserExpenses'] . "',
									glaccountassignment = '" . $_POST['GLAccountCash'] . "',
									glaccountpcash = '" . $_POST['GLAccountPcashTab'] . "',
									defaulttag = '" . $_POST['DefaultTag'] . "',
									taxgroupid='" . $_POST['TaxGroup'] . "'
				WHERE tabcode = '" . $SelectedTab . "'";
		$Msg = _('The Petty Cash Tab') . ' ' . $SelectedTab . ' ' . _('has been updated');
	} elseif ($InputError != 1) {
		// First check the type is not being duplicated
		$CheckSQL = "SELECT count(*)
					 FROM pctabs
					 WHERE tabcode = '" . $_POST['TabCode'] . "'";
		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);
		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(_('The Tab ') . ' ' . $_POST['TabCode'] . ' ' . _(' already exists'), 'error');
		} else {
			// Add new record on submit
			$SQL = "INSERT INTO pctabs	(tabcode,
							 			 usercode,
										 typetabcode,
										 currency,
										 tablimit,
										 assigner,
										 authorizer,
										 authorizerexpenses,
										 glaccountassignment,
										 glaccountpcash,
										 defaulttag,
										 taxgroupid)
								VALUES ('" . $_POST['TabCode'] . "',
									'" . $_POST['SelectUser'] . "',
									'" . $_POST['SelectTabs'] . "',
									'" . $_POST['SelectCurrency'] . "',
									'" . filter_number_format($_POST['TabLimit']) . "',
									'" . $_POST['SelectAssigner'] . "',
									'" . $_POST['SelectAuthoriserCash'] . "',
									'" . $_POST['SelectAuthoriserExpenses'] . "',
									'" . $_POST['GLAccountCash'] . "',
									'" . $_POST['GLAccountPcashTab'] . "',
									'" . $_POST['DefaultTag'] . "',
									'" . $_POST['TaxGroup'] . "'
								)";
			$Msg = _('The Petty Cash Tab') . ' ' . $_POST['TabCode'] . ' ' . _('has been created');
		}
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($SelectedTab);
		unset($_POST['SelectUser']);
		unset($_POST['TabCode']);
		unset($_POST['SelectTabs']);
		unset($_POST['SelectCurrency']);
		unset($_POST['TabLimit']);
		unset($_POST['SelectAssigner']);
		unset($_POST['SelectAuthoriserCash']);
		unset($_POST['GLAccountCash']);
		unset($_POST['GLAccountPcashTab']);
		unset($_POST['TaxGroup']);
	}
} elseif (isset($_GET['delete'])) {
	$SQL = "DELETE FROM pctabs WHERE tabcode='" . $SelectedTab . "'";
	$ErrMsg = _('The Tab record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('The Petty Cash Tab') . ' ' . $SelectedTab . ' ' . _('has been deleted'), 'success');
	unset($SelectedTab);
	unset($_GET['delete']);
}
if (!isset($SelectedTab)) {
	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTab will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of sales types will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	$SQL = "SELECT tabcode,
					usercode,
					typetabdescription,
					currabrev,
					tablimit,
					assigner,
					authorizer,
					authorizerexpenses,
					glaccountassignment,
					glaccountpcash,
					defaulttag,
					currencies.decimalplaces,
					chartmaster1.accountname AS glactassigntname,
					chartmaster2.accountname AS glactpcashname,
					taxgroupdescription
				FROM pctabs
				INNER JOIN currencies
					ON pctabs.currency=currencies.currabrev
				INNER JOIN pctypetabs
					ON pctabs.typetabcode=pctypetabs.typetabcode
				INNER JOIN chartmaster AS chartmaster1 ON
					pctabs.glaccountassignment = chartmaster1.accountcode
				INNER JOIN chartmaster AS chartmaster2 ON
					pctabs.glaccountpcash = chartmaster2.accountcode
				INNER JOIN taxgroups
					ON pctabs.taxgroupid=taxgroups.taxgroupid
				ORDER BY tabcode";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">
				<tr>
					<th>', _('Tab Code'), '</th>
					<th>', _('User Name'), '</th>
					<th>', _('Type Of Tab'), '</th>
					<th>', _('Currency'), '</th>
					<th>', _('Limit'), '</th>
					<th>', _('Cash Assigner'), '</th>
					<th>', _('Authoriser - Cash'), '</th>
					<th>', _('Authoriser - Expenses'), '</th>
					<th>', _('GL Account For Cash Assignment'), '</th>
					<th>', _('GL Account Petty Cash Tab'), '</th>
					<th>', _('Default Tag'), '</th>
					<th>', _('Tax Group'), '</th>
				</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['defaulttag'] . "'";
			$TagResult = DB_query($TagSQL);
			if (DB_num_rows($TagResult) > 0) {
				$TagRow = DB_fetch_array($TagResult);
			} else {
				$TagRow['tagdescription'] = '';
			}
			echo '<tr class="striped_row">
					<td>', $MyRow['tabcode'], '</td>
					<td>', $MyRow['usercode'], '</td>
					<td>', $MyRow['typetabdescription'], '</td>
					<td>', $MyRow['currabrev'], '</td>
					<td class="number">', locale_number_format($MyRow['tablimit'], $MyRow['decimalplaces']), '</td>
					<td>', $MyRow['assigner'], '</td>
					<td>', $MyRow['authorizer'], '</td>
					<td>', $MyRow['authorizerexpenses'], '</td>
					<td>', $MyRow['glaccountassignment'] . ' - ' . $MyRow['glactassigntname'], '</td>
					<td>', $MyRow['glaccountpcash'] . ' - ' . $MyRow['glactpcashname'], '</td>
					<td>', $TagRow['tagdescription'], '</td>
					<td>', $MyRow['taxgroupdescription'], '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedTab=', $MyRow['tabcode'], '">' . _('Edit') . '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedTab=', $MyRow['tabcode'], '&amp;delete=yes" onclick=\' return confirm("' . _('Are you sure you wish to delete this tab code?') . '", \'Confirm Delete\', this);\'>' . _('Delete') . '</a></td>
				</tr>';
		}
		//END WHILE LIST LOOP
		echo '</table>';
	} //if there are tabs to show
}
//end of ifs and buts!
if (isset($SelectedTab)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', _('Show All Tabs Defined'), '</a>
		</div>';
}
if (!isset($_GET['delete'])) {
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	if (isset($SelectedTab) and $SelectedTab != '') {
		$SQL = "SELECT tabcode,
						usercode,
						typetabcode,
						currency,
						tablimit,
						assigner,
						authorizer,
						authorizerexpenses,
						glaccountassignment,
						glaccountpcash,
						defaulttag,
						taxgroupid
					FROM pctabs
				WHERE tabcode='" . $SelectedTab . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$_POST['TabCode'] = $MyRow['tabcode'];
		$_POST['SelectUser'] = $MyRow['usercode'];
		$_POST['SelectTabs'] = $MyRow['typetabcode'];
		$_POST['SelectCurrency'] = $MyRow['currency'];
		$_POST['TabLimit'] = locale_number_format($MyRow['tablimit']);
		$_POST['SelectAssigner'] = $MyRow['assigner'];
		$_POST['SelectAuthoriserCash'] = $MyRow['authorizer'];
		$_POST['SelectAuthoriserExpenses'] = $MyRow['authorizerexpenses'];
		$_POST['GLAccountCash'] = $MyRow['glaccountassignment'];
		$_POST['GLAccountPcashTab'] = $MyRow['glaccountpcash'];
		$_POST['DefaultTag'] = $MyRow['defaulttag'];
		$_POST['TaxGroup'] = $MyRow['taxgroupid'];
		echo '<input type="hidden" name="SelectedTab" value="', $SelectedTab, '" />';
		echo '<input type="hidden" name="TabCode" value="', $_POST['TabCode'], '" />';
		echo '<fieldset>
				<legend>', _('Amend Petty Cash Type'), '</legend>
				<field>
					<label for="TabCode">', _('Tab Code'), ':</label>
					<fieldtext>', $_POST['TabCode'], '</fieldtext>
				</field>';
	} else {
		// This is a new type so the user may volunteer a type code
		echo '<fieldset>
				<legend>', _('Create Petty Cash Type'), '</legend>
				<field>
					<label for="TabCode">', _('Tab Code'), ':</label>
					<input type="text" required="required" maxlength="20" name="TabCode" />
				</field>';
	}
	if (!isset($_POST['typetabdescription'])) {
		$_POST['typetabdescription'] = '';
	}
	echo '<field>
			<label for="SelectUser">', _('User Name'), ':</label>
			<select required="required" name="SelectUser">';
	$SQL = "SELECT userid,
					realname
			FROM www_users ORDER BY userid";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectUser']) and $MyRow['userid'] == $_POST['SelectUser']) {
			echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		} else {
			echo '<option value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		}
	} //end while loop get user
	echo '</select>
		</field>';
	echo '<field>
			<label for="SelectTabs">', _('Type Of Tab'), ':</label>
			<select required="required" name="SelectTabs">';
	$SQL = "SELECT typetabcode,
					typetabdescription
			FROM pctypetabs
			ORDER BY typetabcode";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['typetabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="', $MyRow['typetabcode'], '">', $MyRow['typetabcode'], ' - ', $MyRow['typetabdescription'], '</option>';
		} else {
			echo '<option value="', $MyRow['typetabcode'], '">', $MyRow['typetabcode'], ' - ', $MyRow['typetabdescription'], '</option>';
		}
	} //end while loop get type of tab
	echo '</select>
		</field>';
	echo '<field>
			<label for="SelectCurrency">', _('Currency'), ':</label>
			<select required="required" name="SelectCurrency">';
	$SQL = "SELECT currency, currabrev FROM currencies";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectCurrency']) and $MyRow['currabrev'] == $_POST['SelectCurrency']) {
			echo '<option selected="selected" value="', $MyRow['currabrev'], '">', $MyRow['currency'], '</option>';
		} else {
			echo '<option value="', $MyRow['currabrev'], '">', $MyRow['currency'], '</option>';
		}
	} //end while loop get type of tab
	echo '</select>
		</field>';
	if (!isset($_POST['TabLimit'])) {
		$_POST['TabLimit'] = 0;
	}
	echo '<field>
			<label for="TabLimit">', _('Limit Of Tab'), ':</label>
			<input type="text" class="number" name="TabLimit" size="12" required="required" maxlength="11" value="', $_POST['TabLimit'], '" />
		</field>';
	echo '<field>
			<label for="SelectAssigner">', _('Cash Assigner'), ':</label>
			<select required="required" name="SelectAssigner">';
	$SQL = "SELECT userid,
					realname
			FROM www_users
			ORDER BY userid";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectAssigner']) and $MyRow['userid'] == $_POST['SelectAssigner']) {
			echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		} else {
			echo '<option value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		}
	} //end while loop get assigner
	echo '</select>
		</field>';
	echo '<field>
			<label for="SelectAuthoriserCash">', _('Authoriser - Cash'), ':</label>
			<select required="required" name="SelectAuthoriserCash">';
	$SQL = "SELECT userid,
					realname
			FROM www_users
			ORDER BY userid";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectAuthoriserCash']) and $MyRow['userid'] == $_POST['SelectAuthoriserCash']) {
			echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		} else {
			echo '<option value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		}
	} //end while loop get authoriser
	echo '</select>
		</field>';
	echo '<field>
			<label for="SelectAuthoriserExpenses">', _('Authoriser - Expenses'), ':</label>
			<select required="required" name="SelectAuthoriserExpenses">';
	$SQL = "SELECT userid,
					realname
			FROM www_users
			ORDER BY userid";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectAuthoriserExpenses']) and $MyRow['userid'] == $_POST['SelectAuthoriserExpenses']) {
			echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		} else {
			echo '<option value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		}
	} //end while loop get authoriser
	echo '</select>
		</field>';
	echo '<field>
			<label for="GLAccountCash">', _('GL Account Cash Assignment'), ':</label>
			<select required="required" name="GLAccountCash">';
	$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname
			FROM chartmaster
			INNER JOIN bankaccounts
				ON chartmaster.accountcode = bankaccounts.accountcode
			ORDER BY chartmaster.accountcode";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['GLAccountCash']) and $MyRow['accountcode'] == $_POST['GLAccountCash']) {
			echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), '</option>';
		} else {
			echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), '</option>';
		}
	} //end while loop
	echo '</select>
		</field>';
	echo '<field>
			<label for="GLAccountPcashTab">', _('GL Account Petty Cash Tab'), ':</label>
			<select required="required" name="GLAccountPcashTab">';
	$SQL = "SELECT accountcode,
					accountname
				FROM chartmaster
				ORDER BY accountcode";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['GLAccountPcashTab']) and $MyRow['accountcode'] == $_POST['GLAccountPcashTab']) {
			echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), '</option>';
		} else {
			echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), '</option>';
		}
	} //end while loop
	echo '</select>
		</field>';
	//Select the tag
	$SQL = "SELECT tagref,
					tagdescription
			FROM tags
			ORDER BY tagref";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="DefaultTag">', _('Default Tag'), ':</label>
			<select name="DefaultTag">';
	echo '<option value="0">0 - ', _('None'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['DefaultTag']) and $_POST['DefaultTag'] == $MyRow['tagref']) {
			echo '<option selected="selected" value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
		} else {
			echo '<option value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
		}
	}
	echo '</select>
		</field>';
	// End select tag
	$SQL = "SELECT taxgroupid,
					taxgroupdescription
			FROM taxgroups
			ORDER BY taxgroupdescription";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="TaxGroup">', _('Tax Group'), ':</label>
			<select name="TaxGroup">';
	echo '<option value="0">0 - ', _('None'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['TaxGroup']) and $_POST['TaxGroup'] == $MyRow['taxgroupid']) {
			echo '<option selected="selected" value="', $MyRow['taxgroupid'], '">', $MyRow['taxgroupid'], ' - ', $MyRow['taxgroupdescription'], '</option>';
		} else {
			echo '<option value="', $MyRow['taxgroupid'], '">', $MyRow['taxgroupid'], ' - ', $MyRow['taxgroupdescription'], '</option>';
		}
	}
	echo '</select>
		</field>';
	// End select tag
	echo '</fieldset>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="Submit" value="', _('Accept'), '" />
			<input type="submit" name="Cancel" value="', _('Cancel'), '" />
		</div>';
	echo '</form>';
} // end if user wish to delete
include('includes/footer.php');
?>