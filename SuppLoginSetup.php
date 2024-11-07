<?php


include('includes/session.php');
$Title = _('Supplier Login Configuration');
include('includes/header.php');
$ViewTopic = 'Setup';
$BookMark = '';
include('includes/SQL_CommonFunctions.inc');
include ('includes/LanguagesArray.php');


if (!isset($_SESSION['SupplierID'])){
	echo '<br />
		<br />';
	prnMsg(_('A supplier must first be selected before logins can be defined for it') . '<br /><br /><a href="' . $RootPath . '/SelectSupplier.php">' . _('Select a supplier') . '</a>','info');
	include('includes/footer.php');
	exit;
}

$ModuleList = array(_('Orders'),
					_('Receivables'),
					_('Payables'),
					_('Purchasing'),
					_('Inventory'),
					_('Manufacturing'),
					_('General Ledger'),
					_('Asset Manager'),
					_('Petty Cash'),
					_('Setup'));

echo '<a href="' . $RootPath . '/SelectSupplier.php?" class="toplink">' . _('Back to Suppliers') . '</a><br />';

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Supplier') . '" alt="" />' . ' ' . _('Supplier') . ' : ' . $_SESSION['SupplierID'] . _(' has been selected') . '
	</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID'])<4){
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' & + \" \\ " . _('or a space'),'error');
	} elseif (mb_strlen($_POST['Password'])<5){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
	} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
	}

	/* Make a comma separated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = '';
	while ($i < count($ModuleList)){
		$ModulesAllowed .= ' '. ',';//no any modules allowed for the suppliers
		$i++;
	}


	if ($InputError !=1) {

		$SQL = "INSERT INTO www_users (userid,
										realname,
										supplierid,
										password,
										phone,
										email,
										pagesize,
										fullaccess,
										defaultlocation,
										lastvisitdate,
										modulesallowed,
										displayrecordsmax,
										theme,
										language)
						VALUES ('" . $_POST['UserID'] . "',
							'" . $_POST['RealName'] ."',
							'" . $_SESSION['SupplierID'] ."',
							'" . CryptPass($_POST['Password']) ."',
							'" . $_POST['Phone'] . "',
							'" . $_POST['Email'] ."',
							'" . $_POST['PageSize'] ."',
							'" . $_POST['Access'] . "',
							'" . $_POST['DefaultLocation'] ."',
							'" . date($_SESSION['DefaultDateFormat']) ."',
							'" . $ModulesAllowed . "',
							'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
							'" . $_POST['Theme'] . "',
							'". $_POST['UserLanguage'] ."')";
		$ErrMsg = _('The user could not be added because');
		$DbgMsg = _('The SQL that was used to insert the new user and failed was');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg);
		prnMsg( _('A new supplier login has been created'), 'success' );
		include('includes/footer.php');
		exit;
	}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', _('Supplier Login Details'), '</legend>
		<field>
			<label for="UserID">' . _('User Login') . ':</label>
			<input type="text" pattern="[^><+-]{4,20}" title="" required="required" placeholder="'._('More than 4 characters').'" name="UserID" size="22" maxlength="20" />
			<fieldhelp>'._('The user ID must has more than 4 legal characters').'</fieldhelp>
		</field>';


if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if (!isset($_POST['RealName'])) {
	$_POST['RealName']='';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone']='';
}
if (!isset($_POST['Email'])) {
	$_POST['Email']='';
}
echo '<field>
		<label for="Password">' . _('Password') . ':</label>
		<input type="password" pattern=".{5,20}" placeholder="'._('More than 5 characters').'" required="required" title=""  name="Password" size="22" maxlength="20" value="' . $_POST['Password'] . '" />
		<fieldhelp>'._('Password must be more than 5 characters').'</fieldhelp>
	</field>
	<field>
		<label for="RealName">' . _('Full Name') . ':</label>
		<input type="text" pattern=".{0,35}" title="" placeholder="'._('User name').'" name="RealName" value="' . $_POST['RealName'] . '" size="36" maxlength="35" />
		<fieldhelp>'._('Must be less than 35 characters').'</fieldhelp>
	</field>
	<field>
		<label for="Phone">' . _('Telephone No') . ':</label>
		<input type="tel" pattern="[\s+()-\d]{1,30}" title="" placeholder="'._('number and allowed charactrs').'" name="Phone" value="' . $_POST['Phone'] . '" size="32" maxlength="30" />
		<fieldhelp>'._('The input must be phone number').'</fieldhelp>
	</field>
	<field>
		<label for="Email">' . _('Email Address') .':</label>
		<input type="email" name="Email" title="" placeholder="'._('email address format').'" value="' . $_POST['Email'] .'" size="32" maxlength="55" />
		<fieldhelp>'._('The input must be email address').'</fieldhelp>
	</field>';

//Make an array of the security roles where only one role is active and is ID 1

//For the security role selection box, we will only show roles that have:
//- Only one entry in securitygroups AND the tokenid of this entry == 9

//First get all available security role ID's'
$RolesResult = DB_query("SELECT secroleid FROM securityroles");
$FoundTheSupplierRole = false;
while ($myroles = DB_fetch_array($RolesResult)){
	//Now look to find the tokens for the role - we just wnat the role that has just one token i.e. token 9
	$TokensResult = DB_query("SELECT tokenid
								FROM securitygroups
								WHERE secroleid = '" . $myroles['secroleid'] ."'");

	while ($mytoken = DB_fetch_row($TokensResult)) {
		if ($mytoken[0]==9){
			echo'<input type="hidden" name="Access" value ="' . $myroles['secroleid'] . '" />';
			$FoundTheSupplierRole = true;
			break;
		}
	}
}

if (!$FoundTheSupplierRole){
	echo '</fieldset>
		  </form>';
	prnMsg(_('The supplier login role is expected to contain just one token - number 9. There is no such role currently defined - so a supplier login cannot be set up until this role is defined'),'error');
	include('includes/footer.php');
	exit;
}


echo '<field>
		<label for="DefaultLocation">' . _('Default Location') . ':</label>
		<select name="DefaultLocation">';

$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
$Result = DB_query($SQL);

while ($MyRow=DB_fetch_array($Result)){

	if (isset($_POST['DefaultLocation'])
		AND $MyRow['loccode'] == $_POST['DefaultLocation']){

		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="PageSize">' . _('Reports Page Size') .':</label>
		<select name="PageSize">';

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A4'){
	echo '<option selected="selected" value="A4">' . _('A4') . '</option>';
} else {
	echo '<option value="A4">' . _('A4') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3'){
	echo '<option selected="selected" value="A3">' . _('A3') . '</option>';
} else {
	echo '<option value="A3">' . _('A3') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3_landscape'){
	echo '<option selected="selected" value="A3_landscape">' . _('A3') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="A3_landscape">' . _('A3') . ' ' . _('landscape') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter'){
	echo '<option selected="selected" value="letter">' . _('Letter') . '</option>';
} else {
	echo '<option value="letter">' . _('Letter') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter_landscape'){
	echo '<option selected="selected" value="letter_landscape">' . _('Letter') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="letter_landscape">' . _('Letter') . ' ' . _('landscape') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal'){
	echo '<option selected="selected" value="legal">' . _('Legal') . '</option>';
} else {
	echo '<option value="legal">' . _('Legal') . '</option>';
}
if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal_landscape'){
	echo '<option selected="selected" value="legal_landscape">' . _('Legal') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="legal_landscape">' . _('Legal') . ' ' . _('landscape') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="Theme">' . _('Theme') . ':</label>
		<select name="Theme">';

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){

		if (isset($_POST['Theme']) and $_POST['Theme'] == $ThemeName){
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else if (!isset($_POST['Theme']) and ($Theme==$ThemeName)) {
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
	<select name="UserLanguage">';

foreach ($LanguagesArray as $LanguageEntry => $LanguageName){
	if (isset($_POST['UserLanguage']) and $_POST['UserLanguage'] == $LanguageEntry){
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} elseif (!isset($_POST['UserLanguage']) and $LanguageEntry == $DefaultLanguage) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} else {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	}
}
echo '</select>
	</field>';

echo '</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
	</form>';

echo '<script  type="text/javascript">defaultControl(document.forms[0].UserID);</script>';

include('includes/footer.php');
?>
