<?php

$PageSecurity = 15;

include('includes/session.inc');

$title = _('Database Upgrade');

//ob_start(); /*what is this for? */

include('includes/header.inc');

function executeSQL($sql, $db, $TrapErrors=False) {
	global $SQLFile;
/* Run an sql statement and return an error code */
	if (!isset($SQLFile)) {
		$result = DB_query($sql, $db, '', '', false, $TrapErrors);
		return DB_error_no($db);
	} else {
		fwrite($SQLFile, $sql.";\n");
	}
}

function updateDBNo($NewNumber, $db) {
	global $SQLFile;
	if (!isset($SQLFile)) {
		$sql="UPDATE config SET confvalue='".$NewNumber."' WHERE confname='DBUpdateNumber'";
		executeSQL($sql, $db);
		$_SESSION['DBUpdateNumber']=$NewNumber;
	}
}

if ($dbType='mysql' or $dbType='mysqli') {
	include('includes/UpgradeDB_mysql.inc');
} else {
	prnMsg( _('Your database type is not covered by this upgrade script. Please see your system administrator'), 'error');
}

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $title.'</p>';

if (!isset($_POST['continue']) and !isset($_POST['CreateSQLFile'])) {
	echo '<form method="post" id="AccountGroups" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<div class="page_help_text">' . _('You have database updates that are required.').'<br />'.
		_('Please ensure that you have taken a backup of your current database before continuing.'). '</div><br />';

	echo '<div class="centre"><input type="submit" name="continue" value="'.('Continue With Updates').'" />
		<input type="submit" name="CreateSQLFile" value="'.('Create an SQL file to apply manually').'" /></div>';
	echo '</form>';
} else {
	$StartingUpdate=$_SESSION['DBUpdateNumber']+1;
	$EndingUpdate=$DBVersion;
	if (isset($_POST['CreateSQLFile'])) {
		$SQLFile=fopen("./companies/" . $_SESSION['DatabaseName'] . "/reportwriter/UpgradeDB" . $StartingUpdate ."-".$EndingUpdate.".sql","w");
	}
	unset($_SESSION['Updates']);
	$_SESSION['Updates']['Errors']=0;
	$_SESSION['Updates']['Successes']=0;
	$_SESSION['Updates']['Warnings']=0;
	for($UpdateNumber=$StartingUpdate; $UpdateNumber<=$EndingUpdate; $UpdateNumber++) {
//		echo '<tr><td>'.$UpdateNumber.'</td>';
		$sql="SET FOREIGN_KEY_CHECKS=0";
		$result=DB_Query($sql, $db);
		include('sql/mysql/updates/'.$UpdateNumber.'.php');
		$sql="SET FOREIGN_KEY_CHECKS=1";
		$result=DB_Query($sql, $db);
//		echo '</tr>';
	}
	echo '<table class="selection"><tr>';
	echo '<th colspan="4"><font size="2" color="navy"><b>'._('Database Updates Have Been Run').'</b></font></th></tr>';
	echo '<tr><td style="background-color: #fddbdb;color: red;">'.$_SESSION['Updates']['Errors'].' '._('updates have errors in them').'</td></tr>';
	echo '<tr><td style="background-color: #b9ecb4;color: #006400;">'.$_SESSION['Updates']['Successes'].' '._('updates have succeeded').'</td></tr>';
	echo '<tr><td style="background-color: #c7ccf6;color: navy;">'.$_SESSION['Updates']['Warnings'].' '._('updates have not been done as the update was unnecessary on this database').'</td></tr>';
	if ($_SESSION['Updates']['Errors']>0) {
		for ($i=0; $i<sizeOf($_SESSION['Updates']['Messages']); $i++) {
			echo '<tr><td>'.$_SESSION['Updates']['Messages'][$i].'</td></tr>';
		}
	}
	echo '</table><br />';
	prnMsg( _('You must logout and log back in to ensure that all the updates take affect') , 'warn');
}
if (isset($SQLFile)) {
//		header('Location: Z_UpgradeDatabase.php'); //divert to the db upgrade if the table doesn't exist
}

include('includes/footer.inc');
?>