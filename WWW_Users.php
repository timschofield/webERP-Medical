<?php
// WWW_Users.php
// Entry of users and security settings of users.

include('includes/session.php');
$Title = _('Users Maintenance');
$ViewTopic = 'GettingStarted';
$BookMark = 'UserMaintenance';

if(isset($_POST['UserID']) AND isset($_POST['ID'])) {
	if($_POST['UserID'] == $_POST['ID']) {
		if (isset($_POST['UserLanguage']) && !checkLanguageChoice($_POST['UserLanguage'])) {
			$_POST['UserLanguage'] = $DefaultLanguage;
		}
	} else if (isset($_POST['UserLanguage']) && !checkLanguageChoice($_POST['UserLanguage'])) {
		$_POST['UserLanguage'] = $DefaultLanguage;
	}
}

include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/group_add.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.

if($AllowDemoMode) {
	prnMsg(_('The the system is in demo mode and the security model administration is disabled'), 'warn');
	exit;
}
$ModuleList = array(
	_('Sales'),
	_('Receivables'),
	_('Purchases'),
	_('Payables'),
	_('Inventory'),
	_('Manufacturing'),
	_('General Ledger'),
	_('Asset Manager'),
	_('Petty Cash'),
	_('Setup'),
	_('Utilities')
);
$ModuleListLabel = array(
	_('Display Sales module'),
	_('Display Receivables module'),
	_('Display Purchases module'),
	_('Display Payables module'),
	_('Display Inventory module'),
	_('Display Manufacturing module'),
	_('Display General Ledger module'),
	_('Display Asset Manager module'),
	_('Display Petty Cash module'),
	_('Display Setup module'),
	_('Display Utilities module')
);
$PDFLanguages = array(
	_('Latin Western Languages - Times'),
	_('Eastern European Russian Japanese Korean Hebrew Arabic Thai'),
	_('Chinese'),
	_('Free Serif')
);

include('includes/SQL_CommonFunctions.inc');

// Make an array of the security roles
$SQL = "SELECT secroleid,
				secrolename
		FROM securityroles
		ORDER BY secrolename";

$Sec_Result = DB_query($SQL);
$SecurityRoles = array();
// Now load it into an a ray using Key/Value pairs
while( $Sec_row = DB_fetch_row($Sec_Result) ) {
	$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
}
DB_free_result($Sec_Result);

if(isset($_GET['SelectedUser'])) {
	$SelectedUser = $_GET['SelectedUser'];
} elseif(isset($_POST['SelectedUser'])) {
	$SelectedUser = $_POST['SelectedUser'];
}

if(isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if(mb_strlen($_POST['UserID'])<4) {
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'), 'error');
	} elseif(ContainsIlLegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'), 'error');
	} elseif(mb_strlen($_POST['Password'])<5) {
		if(!$SelectedUser) {
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'), 'error');
		}
	} elseif(mb_strstr($_POST['Password'],$_POST['UserID'])!= False) {
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'), 'error');
	} elseif((mb_strlen($_POST['Cust'])>0)
				AND (mb_strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		prnMsg(_('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'), 'error');
	} elseif($AllowDemoMode AND $_POST['UserID'] == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be modified.'), 'error');
		$InputError = 1;
	}

	if(!isset($SelectedUser)) {
		/* check to ensure the user id is not already entered */
		$Result = DB_query("SELECT userid FROM www_users WHERE userid='" . $_POST['UserID'] . "'");
		if(DB_num_rows($Result)==1) {
			$InputError =1;
			prnMsg(_('The user ID') . ' ' . $_POST['UserID'] . ' ' . _('already exists and cannot be used again'), 'error');
		}
	}

	if((mb_strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$SQL = "SELECT custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno='" . $_POST['Cust'] . "'
				AND custbranch.branchcode='" . $_POST['BranchCode'] . "'";

		$ErrMsg = _('The check on validity of the customer code and branch failed because');
		$DbgMsg = _('The SQL that was used to check the customer code and branch was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		if(DB_num_rows($Result)==0) {
			prnMsg(_('The entered Branch Code is not valid for the entered Customer Code'), 'error');
			$InputError = 1;
		}
	}

	/* Make a comma separated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = '';
	while($i < count($ModuleList)) {
		$FormVbl = 'Module_' . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	$_POST['ModulesAllowed']= $ModulesAllowed;

	if(isset($SelectedUser) AND $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the delete code below*/

		if(!isset($_POST['Cust']) OR $_POST['Cust']==NULL OR $_POST['Cust']=='') {
			$_POST['Cust']='';
			$_POST['BranchCode']='';
		}
		$UpdatePassword = '';
		if($_POST['Password'] != '') {
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$SQL = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',
						customerid='" . $_POST['Cust'] ."',
						phone='" . $_POST['Phone'] ."',
						email='" . $_POST['Email'] ."',
						timeout='" . $_POST['Timeout'] . "',
						" . $UpdatePassword . "
						branchcode='" . $_POST['BranchCode'] . "',
						supplierid='" . $_POST['SupplierID'] . "',
						salesman='" . $_POST['Salesman'] . "',
						pagesize='" . $_POST['PageSize'] . "',
						fullaccess='" . $_POST['Access'] . "',
						cancreatetender='" . $_POST['CanCreateTender'] . "',
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['UserLanguage'] . "',
						defaultlocation='" . $_POST['DefaultLocation'] ."',
						modulesallowed='" . $ModulesAllowed . "',
						showdashboard='" . $_POST['ShowDashboard'] . "',
						showpagehelp='" . $_POST['ShowPageHelp'] . "',
						showfieldhelp='" . $_POST['ShowFieldHelp'] . "',
						blocked='" . $_POST['Blocked'] . "',
						pdflanguage='" . $_POST['PDFLanguage'] . "',
						department='" . $_POST['Department'] . "'
					WHERE userid = '". $SelectedUser . "'";
		prnMsg(_('The selected user record has been updated'), 'success' );
		$_SESSION['ShowPageHelp'] = $_POST['ShowPageHelp'];
		$_SESSION['ShowFieldHelp'] = $_POST['ShowFieldHelp'];

	} elseif($InputError !=1) {

		$SQL = "INSERT INTO www_users (
					userid,
					realname,
					customerid,
					branchcode,
					supplierid,
					salesman,
					password,
					phone,
					email,
					timeout,
					pagesize,
					fullaccess,
					cancreatetender,
					defaultlocation,
					modulesallowed,
					showdashboard,
					showpagehelp,
					showfieldhelp,
					displayrecordsmax,
					theme,
					language,
					pdflanguage,
					department)
				VALUES ('" . $_POST['UserID'] . "',
					'" . $_POST['RealName'] ."',
					'" . $_POST['Cust'] ."',
					'" . $_POST['BranchCode'] ."',
					'" . $_POST['SupplierID'] ."',
					'" . $_POST['Salesman'] . "',
					'" . CryptPass($_POST['Password']) ."',
					'" . $_POST['Phone'] . "',
					'" . $_POST['Email'] ."',
					'" . $_POST['Timeout'] ."',
					'" . $_POST['PageSize'] ."',
					'" . $_POST['Access'] . "',
					'" . $_POST['CanCreateTender'] . "',
					'" . $_POST['DefaultLocation'] ."',
					'" . $ModulesAllowed . "',
					'" . $_POST['ShowDashboard'] . "',
					'" . $_POST['ShowPageHelp'] . "',
					'" . $_POST['ShowFieldHelp'] . "',
					'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
					'" . $_POST['Theme'] . "',
					'". $_POST['UserLanguage'] ."',
					'" . $_POST['PDFLanguage'] . "',
					'" . $_POST['Department'] . "')";
		prnMsg(_('A new user record has been inserted'), 'success' );

		$LocationSql = "INSERT INTO locationusers (loccode,
													userid,
													canview,
													canupd
												) VALUES (
													'" . $_POST['DefaultLocation'] . "',
													'" . $_POST['UserID'] . "',
													1,
													1
												)";
		$ErrMsg = _('The default user locations could not be processed because');
		$DbgMsg = _('The SQL that was used to create the user locations and failed was');
		$Result = DB_query($LocationSql, $ErrMsg, $DbgMsg);
		prnMsg(_('User has been authorized to use and update only his / her default location'), 'success' );

		$GLAccountsSql = "INSERT INTO glaccountusers (userid, accountcode, canview, canupd)
						 SELECT '" . $_POST['UserID'] . "', chartmaster.accountcode,1,1
						 FROM chartmaster;	";

		$ErrMsg = _('The default user GL Accounts could not be processed because');
		$DbgMsg = _('The SQL that was used to create the user GL Accounts and failed was');
		$Result = DB_query($GLAccountsSql, $ErrMsg, $DbgMsg);
		prnMsg(_('User has been authorized to use and update all GL accounts'), 'success' );
	}

	if($InputError!=1) {
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['SupplierID']);
		unset($_POST['Salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Timeout']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['CanCreateTender']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['ShowDashboard']);
		unset($_POST['ShowPageHelp']);
		unset($_POST['ShowFieldHelp']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['PDFLanguage']);
		unset($_POST['Department']);
		unset($SelectedUser);
	}

} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button


	if($AllowDemoMode AND $SelectedUser == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be deleted'), 'error');
	} else {
		$SQL = "SELECT userid FROM audittrail where userid='" . $SelectedUser ."'";
		$Result = DB_query($SQL);
		if(DB_num_rows($Result)!=0) {
			prnMsg(_('Cannot delete user as entries already exist in the audit trail'), 'warn');
		} else {
			$SQL = "DELETE FROM locationusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The Location - User could not be deleted because');
			$Result = DB_query($SQL, $ErrMsg);

			$SQL = "DELETE FROM glaccountusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The GL Account - User could not be deleted because');
			$Result = DB_query($SQL, $ErrMsg);

			$SQL = "DELETE FROM bankaccountusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The Bank Accounts - User could not be deleted because');
			$Result = DB_query($SQL, $ErrMsg);

			$SQL = "DELETE FROM www_users WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The User could not be deleted because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg(_('User Deleted'),'info');
		}
		unset($SelectedUser);
	}

}

if(!isset($SelectedUser)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="ascending">', _('User Login'), '</th>
				<th class="ascending">', _('Full Name'), '</th>
				<th class="ascending">', _('Telephone'), '</th>
				<th class="ascending">', _('Email'), '</th>
				<th class="ascending">', _('Timeout'), '</th>
				<th class="ascending">', _('Customer Code'), '</th>
				<th class="ascending">', _('Branch Code'), '</th>
				<th class="ascending">', _('Supplier Code'), '</th>
				<th class="ascending">', _('Salesperson'), '</th>
				<th class="ascending">', _('Last Visit'), '</th>
				<th class="ascending">', _('Security Role'), '</th>
				<th class="ascending">', _('Report Size'), '</th>
				<th class="ascending">', _('Theme'), '</th>
				<th class="ascending">', _('Language'), '</th>
				<th class="noprint" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	$Sql = "SELECT userid,
					realname,
					phone,
					email,
					timeout,
					customerid,
					branchcode,
					supplierid,
					salesman,
					lastvisitdate,
					fullaccess,
					cancreatetender,
					pagesize,
					theme,
					language
				FROM www_users";
	$Result = DB_query($Sql);

	while ($MyRow = DB_fetch_array($Result)) {
		if($MyRow[8] == '') {
			$LastVisitDate = _('No login record');
		} else {
			$LastVisitDate = ConvertSQLDate($MyRow[8]);
		}
		/*The SecurityHeadings array is defined in config.php */
		echo '<tr class="striped_row">
				<td class="text">', $MyRow['userid'], '</td>
				<td class="text">', $MyRow['realname'], '</td>
				<td class="text">', $MyRow['phone'], ' </td>
				<td class="text">', $MyRow['email'], '</td>
				<td class="number">', $MyRow['timeout'], 'mins</td>
				<td class="text">', $MyRow['customerid'], '</td>
				<td class="text">', $MyRow['branchcode'], '</td>
				<td class="text">', $MyRow['supplierid'], '</td>
				<td class="text">', $MyRow['salesman'], '</td>
				<td class="centre">', $LastVisitDate, '</td>
				<td class="text">', $SecurityRoles[($MyRow['fullaccess'])], '</td>
				<td class="text">', $MyRow['pagesize'], '</td>
				<td class="text">', $MyRow['theme'], '</td>
				<td class="text">', $LanguagesArray[$MyRow['language']]['LanguageName'], '</td>
				<td class="noprint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?', '&amp;SelectedUser=', $MyRow['userid'], '">', _('Edit'), '</a></td>
				<td class="noprint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?', '&amp;SelectedUser=', $MyRow['userid'], '&amp;delete=1" onclick="return confirm(\'', _('Are you sure you wish to delete this user?'), '\');">', _('Delete'), '</a></td>
			</tr>';
	}// END foreach($Result as $MyRow).
	echo '</tbody></table>';
} //end of ifs and buts!


if(isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Existing Users') . '</a></div>';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if(isset($SelectedUser)) {
	//editing an existing User

	$SQL = "SELECT
				userid,
				realname,
				phone,
				email,
				timeout,
				customerid,
				password,
				branchcode,
				supplierid,
				salesman,
				pagesize,
				fullaccess,
				cancreatetender,
				defaultlocation,
				modulesallowed,
				showdashboard,
				showpagehelp,
				showfieldhelp,
				blocked,
				theme,
				language,
				pdflanguage,
				department
			FROM www_users
			WHERE userid='" . $SelectedUser . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['UserID'] = $MyRow['userid'];
	$_POST['RealName'] = $MyRow['realname'];
	$_POST['Phone'] = $MyRow['phone'];
	$_POST['Email'] = $MyRow['email'];
	$_POST['Timeout']	= $MyRow['timeout'];
	$_POST['Cust']	= $MyRow['customerid'];
	$_POST['BranchCode'] = $MyRow['branchcode'];
	$_POST['SupplierID'] = $MyRow['supplierid'];
	$_POST['Salesman'] = $MyRow['salesman'];
	$_POST['PageSize'] = $MyRow['pagesize'];
	$_POST['Access'] = $MyRow['fullaccess'];
	$_POST['CanCreateTender'] = $MyRow['cancreatetender'];
	$_POST['DefaultLocation'] = $MyRow['defaultlocation'];
	$_POST['ModulesAllowed'] = $MyRow['modulesallowed'];
	$_POST['ShowDashboard'] = $MyRow['showdashboard'];
	$_POST['ShowPageHelp'] = $MyRow['showpagehelp'];
	$_POST['ShowFieldHelp'] = $MyRow['showfieldhelp'];
	$_POST['Blocked'] = $MyRow['blocked'];
	$_POST['Theme'] = $MyRow['theme'];
	$_POST['UserLanguage'] = $MyRow['language'];
	$_POST['PDFLanguage'] = $MyRow['pdflanguage'];
	$_POST['Department'] = $MyRow['department'];

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '" />';
	echo '<input type="hidden" name="ModulesAllowed" value="' . $_POST['ModulesAllowed'] . '" />';

	echo '<fieldset>
			<legend>', _('Amend User Details'), '</legend>
			<field>
				<label for="UserID">' . _('User Code') . ':</label>
				<fieldtext>' . $_POST['UserID'] . '</fieldtext>
			</field>';

} else { //end of if $SelectedUser only do the else when a new record is being entered

	echo '<fieldset>
			<legend>', _('Create New User'), '</legend>
			<field>
				<label for="UserID">' . _('User Login') . ':</label>
				<input pattern="(?!^([aA]{1}[dD]{1}[mM]{1}[iI]{1}[nN]{1})$)[^?+.&\\>< ]{4,}" type="text" required="required" name="UserID" size="22" maxlength="20" placeholder="'._('At least 4 characters').'" title="" />
				<fieldhelp>'._('Please input not less than 4 characters and canot be admin or contains illegal characters').'</fieldhelp>
			</field>';

	/*set the default modules to show to all
	this had trapped a few people previously*/
	$i=0;
	if(!isset($_POST['ModulesAllowed'])) {
		$_POST['ModulesAllowed']='';
	}
	foreach($ModuleList as $ModuleName) {
		if($i>0) {
			$_POST['ModulesAllowed'] .=',';
		}
		$_POST['ModulesAllowed'] .= '1';
		$i++;
	}
	$_POST['ShowDashboard'] = 0;
	$_POST['ShowPageHelp'] = 1;
	$_POST['ShowFieldHelp'] = 1;
}

if(!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if(!isset($_POST['RealName'])) {
	$_POST['RealName']='';
}
if(!isset($_POST['Phone'])) {
	$_POST['Phone']='';
}
if(!isset($_POST['Email'])) {
	$_POST['Email']='';
}
if(!isset($_POST['Timeout'])) {
	$_POST['Timeout']=5;
}
echo '<field>
		<label for="Password">' . _('Password') . ':</label>
		<input type="password" pattern=".{5,}" name="Password" ' . (!isset($SelectedUser) ? 'required="required"' : '') . ' size="22" maxlength="20" value="' . $_POST['Password'] . '" placeholder="'._('At least 5 characters').'" title="" />
		<fieldhelp>'._('Passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'</fieldhelp>
	</field>';
echo '<field>
		<label for="RealName">' . _('Full Name') . ':</label>
		<input type="text" name="RealName" ' . (isset($SelectedUser) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['RealName'] . '" size="36" maxlength="35" />
	</field>';
echo '<field>
		<label for="Phone">' . _('Telephone No') . ':</label>
		<input type="tel" name="Phone" pattern="[0-9+()\s-]*" value="' . $_POST['Phone'] . '" size="32" maxlength="30" />
	</field>';
echo '<field>
		<label for="Email">' . _('Email Address') .':</label>
		<input type="email" name="Email" placeholder="' . _('e.g. user@domain.com') . '" required="required" value="' . $_POST['Email'] .'" size="32" maxlength="55" title="" />
		<fieldhelp>'._('A valid email address is required').'</fieldhelp>
	</field>';
echo '<field>
		<label for="Timeout">' . _('Timeout after') .':</label>
		<input type="text" class="number" name="Timeout" required="required" value="' . $_POST['Timeout'] .'" size="4" maxlength="5" title="" />minutes
		<fieldhelp>'._('Log the user out after this interval of non-use').'</fieldhelp>
	</field>';
echo '<field>
		<label for="Access">' . _('Security Role') . ':</label>
		<select name="Access">';

foreach($SecurityRoles as $SecKey => $SecVal) {
	if(isset($_POST['Access']) and $SecKey == $_POST['Access']) {
		echo '<option selected="selected" value="' . $SecKey . '">' . $SecVal . '</option>';
	} else {
		echo '<option value="' . $SecKey . '">' . $SecVal . '</option>';
	}
}
echo '</select>';
echo '<input type="hidden" name="ID" value="'.$_SESSION['UserID'].'" />
	</field>';

echo '<field>
		<label for="CanCreateTender">' . _('User Can Create Tenders') . ':</label>
		<select name="CanCreateTender">';

if($_POST['CanCreateTender']==0) {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
 	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="DefaultLocation">' . _('Default Location') . ':</label>
		<select name="DefaultLocation">';

$SQL = "SELECT loccode, locationname FROM locations";
$Result = DB_query($SQL);

while($MyRow=DB_fetch_array($Result)) {
	if(isset($_POST['DefaultLocation']) AND $MyRow['loccode'] == $_POST['DefaultLocation']) {
		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}

echo '</select>
	</field>';

if(!isset($_POST['Cust'])) {
	$_POST['Cust']='';
}
if(!isset($_POST['BranchCode'])) {
	$_POST['BranchCode']='';
}
if(!isset($_POST['SupplierID'])) {
	$_POST['SupplierID']='';
}
echo '<field>
		<label for="Cust">' . _('Customer Code') . ':</label>
		<input type="text" name="Cust" data-type="no-ilLegal-chars" title="" size="10" maxlength="10" value="' . $_POST['Cust'] . '" />
		<fieldhelp>' . _('If this user login is to be associated with a customer account, enter the customer account code') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="BranchCode">' . _('Branch Code') . ':</label>
		<input type="text" name="BranchCode" data-type="no-ilLegal-chars" title="" size="10" maxlength="10" value="' . $_POST['BranchCode'] .'" />
		<fieldhelp>' . _('If this user login is to be associated with a customer account a valid branch for the customer account must be entered.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="SupplierID">' . _('Supplier Code') . ':</label>
		<input type="text" name="SupplierID" data-type="no-ilLegal-chars" size="10" maxlength="10" value="' . $_POST['SupplierID'] .'" />
	</field>';

echo '<field>
		<label for="Salesman">' . _('Restrict to Sales Person') . ':</label>
		<select name="Salesman">';

$SQL = "SELECT salesmancode, salesmanname FROM salesman WHERE current = 1 ORDER BY salesmanname";
$Result = DB_query($SQL);
if((isset($_POST['Salesman']) AND $_POST['Salesman']=='') OR !isset($_POST['Salesman'])) {
	echo '<option selected="selected" value="">' . _('Not a salesperson only login') . '</option>';
} else {
	echo '<option value="">' . _('Not a salesperson only login') . '</option>';
}
while($MyRow=DB_fetch_array($Result)) {

	if(isset($_POST['Salesman']) AND $MyRow['salesmancode'] == $_POST['Salesman']) {
		echo '<option selected="selected" value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
	}

}

echo '</select>
	</field>';

echo '<field>
		<label for="PageSize">' . _('Reports Page Size') .':</label>
		<select name="PageSize">';

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='A4') {
	echo '<option selected="selected" value="A4">' . _('A4') . '</option>';
} else {
	echo '<option value="A4">' . _('A4') . '</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='A3') {
	echo '<option selected="selected" value="A3">' . _('A3') . '</option>';
} else {
	echo '<option value="A3">' . _('A3') . '</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='A3_Landscape') {
	echo '<option selected="selected" value="A3_Landscape">' . _('A3') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="A3_Landscape">' . _('A3') . ' ' . _('landscape') . '</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='Letter') {
	echo '<option selected="selected" value="Letter">' . _('Letter') . '</option>';
} else {
	echo '<option value="Letter">' . _('Letter') . '</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='Letter_Landscape') {
	echo '<option selected="selected" value="Letter_Landscape">' . _('Letter') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="Letter_Landscape">' . _('Letter') . ' ' . _('landscape') . '</option>';
}

if(isset($_POST['PageSize']) AND $_POST['PageSize']=='Legal') {
	echo '<option selected="selected" value="Legal">' . _('Legal') . '</option>';
} else {
	echo '<option value="Legal">' . _('Legal') . '</option>';
}
if(isset($_POST['PageSize']) AND $_POST['PageSize']=='Legal_Landscape') {
	echo '<option selected="selected" value="Legal_Landscape">' . _('Legal') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="Legal_Landscape">' . _('Legal') . ' ' . _('landscape') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="Theme">' . _('Theme') . ':</label>
		<select required="required" name="Theme">';

$ThemeDirectories = scandir('css/');

foreach($ThemeDirectories as $ThemeName) {

	if(is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn') {

		if(isset($_POST['Theme']) AND $_POST['Theme'] == $ThemeName) {
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else if(!isset($_POST['Theme']) AND ($Theme==$ThemeName)) {
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName . '</option>';
		}
	}
}

echo '</select>
	</field>';

echo '<field>
		<label for="UserLanguage">' . _('Language') . ':</label>
		<select required="required" name="UserLanguage">';

foreach($LanguagesArray as $LanguageEntry => $LanguageName) {
	if(isset($_POST['UserLanguage']) AND $_POST['UserLanguage'] == $LanguageEntry) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName'] . '</option>';
	} elseif(!isset($_POST['UserLanguage']) AND $LanguageEntry == $DefaultLanguage) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName'] . '</option>';
	} else {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName['LanguageName'] . '</option>';
	}
}
echo '</select>
	</field>';

/*Make an array out of the comma separated list of modules allowed*/
$ModulesAllowed = explode(',',$_POST['ModulesAllowed']);
$i = 0;
foreach($ModuleList as $ModuleName) {
	echo '<field>
			<label for="Module_', $i, '">', $ModuleListLabel[$i], ':</label>
			<select id="Module_', $i, '" name="Module_', $i, '">';
	if($ModulesAllowed[$i] == 0) {
		echo '<option selected="selected" value="0">', _('No'), '</option>',
			 '<option value="1">', _('Yes'), '</option>';
	} else {
		echo '<option value="0">', _('No'), '</option>',
	 		 '<option selected="selected" value="1">', _('Yes'), '</option>';
	}
	echo '</select>
		</field>';
	$i++;
}// END foreach($ModuleList as $ModuleName).

// Turn off/on dashboard:
echo '<field>
		<label for="ShowDashboard">', _('Display dashboard'), ':</label>
		<select id="ShowDashboard" name="ShowDashboard">';
if($_POST['ShowDashboard']==0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>',
		 '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>',
 		 '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>', fShowFieldHelp(_('Show dashboard page after login')), // Function fShowFieldHelp() in ~/includes/MiscFunctions.php
		'
	</field>';
// Turn off/on page help:
echo '<field>
		<label for="ShowPageHelp">', _('Display page help'), ':</label>
		<select id="ShowPageHelp" name="ShowPageHelp">';
if($_POST['ShowPageHelp']==0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>',
		 '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>',
 		 '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>', fShowFieldHelp(_('Show page help when available')), // Function fShowFieldHelp() in ~/includes/MiscFunctions.php
		'
	</field>';
// Turn off/on field help:
echo '<field>
		<label for="ShowFieldHelp">', _('Display field help'), ':</label>
		<select id="ShowFieldHelp" name="ShowFieldHelp">';
if($_POST['ShowFieldHelp']==0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>',
		 '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>',
 		 '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>', fShowFieldHelp(_('Show field help when available')), // Function fShowFieldHelp() in ~/includes/MiscFunctions.php
		'
	</field>';

if(!isset($_POST['PDFLanguage'])) {
	$_POST['PDFLanguage']=0;
}
echo '<field>
		<label for="PDFLanguage">', _('PDF Language Support'), ':</label>
		<select id="PDFLanguage" name="PDFLanguage">';
for($i=0;$i<count($PDFLanguages);$i++) {
	if($_POST['PDFLanguage']==$i) {
		echo '<option selected="selected" value="' . $i .'">' . $PDFLanguages[$i] . '</option>';
	} else {
		echo '<option value="' . $i .'">' . $PDFLanguages[$i]. '</option>';
	}
}
echo '</select>
	</field>';

/* Allowed Department for Internal Requests */

echo '<field>
		<label for="Department">' . _('Allowed Department for Internal Requests') . ':</label>';

$SQL = "SELECT departmentid,
			description
		FROM departments
		ORDER BY description";

$Result=DB_query($SQL);
echo '<select name="Department">';
if((isset($_POST['Department']) AND $_POST['Department']=='0') OR !isset($_POST['Department'])) {
	echo '<option selected="selected" value="0">' . _('Any Internal Department') . '</option>';
} else {
	echo '<option value="">' . _('Any Internal Department') . '</option>';
}
while($MyRow=DB_fetch_array($Result)) {
	if(isset($_POST['Department']) AND $MyRow['departmentid'] == $_POST['Department']) {
		echo '<option selected="selected" value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
	}
}
echo '</select>
	</field>';

/* Account status */

echo '<field>
		<label for="Blocked">' . _('Account Status') . ':</label>
		<select required="required" name="Blocked">';
if($_POST['Blocked']==0) {
	echo '<option selected="selected" value="0">' . _('Open') . '</option>';
	echo '<option value="1">' . _('Blocked') . '</option>';
} else {
 	echo '<option selected="selected" value="1">' . _('Blocked') . '</option>';
	echo '<option value="0">' . _('Open') . '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
	</form>';

include('includes/footer.php');
?>