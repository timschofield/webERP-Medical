<?php

//$PageSecurity = 15;
include('includes/session.php');
$Title = _('Upgrade webERP 3.04 - 3.05');
include('includes/header.php');


prnMsg(_('This script will run perform any modifications to the database required to allow the additional functionality in version 3.05 scripts'),'info');

echo '<p><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="submit" name="DoUpgrade" value="' . _('Perform Upgrade') . '" />';
echo '</form>';

if ($_POST['DoUpgrade'] == _('Perform Upgrade')){

	if ($DBType=='postgres'){

		$SQLScriptFile = file('./sql/pg/upgrade3.04-3.05.psql');

	} elseif ($DBType ='mysql') { //its a mysql db

		$SQLScriptFile = file('./sql/mysql/upgrade3.04-3.05.sql');
	}

	$ScriptFileEntries = sizeof($SQLScriptFile);
	$ErrMsg = _('The script to upgrade the database failed because');
	$SQL ='';
	$InAFunction = false;

	for ($i=0; $i<=$ScriptFileEntries; $i++) {

		$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);

		if (mb_substr($SQLScriptFile[$i], 0, 2) != '--'
			AND mb_substr($SQLScriptFile[$i], 0, 3) != 'USE'
			AND mb_strstr($SQLScriptFile[$i],'/*')==FALSE
			AND mb_strlen($SQLScriptFile[$i])>1){

			$SQL .= ' ' . $SQLScriptFile[$i];

			//check if this line kicks off a function definition - pg chokes otherwise
			if (mb_substr($SQLScriptFile[$i],0,15) == 'CREATE FUNCTION'){
				$InAFunction = true;
			}
			//check if this line completes a function definition - pg chokes otherwise
			if (mb_substr($SQLScriptFile[$i],0,8) == 'LANGUAGE'){
				$InAFunction = false;
			}
			if (mb_strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
				$SQL = mb_substr($SQL,0,mb_strlen($SQL)-1);
				$result = DB_query($SQL, $ErrMsg);
				$SQL='';
			}

		} //end if its a valid sql line not a comment
	} //end of for loop around the lines of the sql script


	/*Now run the data conversions required. */

	prnMsg(_('Upgrade script to put cost information against GRN records from purchorderdetails records .... please wait'),'info');

	$TestAlreadyDoneResult = DB_query('SELECT * FROM grns WHERE stdcostunit<>0');
	if (DB_num_rows($TestAlreadyDoneResult)>0){
		prnMsg(_('The upgrade script appears to have been run already successfully - there is no need to re-run it'),'info');
		include('includes/footer.php');
		exit;
	}


	$UpdateGRNCosts = DB_query('UPDATE grns INNER JOIN purchorderdetails ON grns.podetailitem=purchorderdetails.podetailitem SET grns.stdcostunit = purchorderdetails.stdcostunit');


	prnMsg(_('The GRN records have been updated with cost information from purchorderdetails successfully'),'success');
} /*Dont do upgrade */

include('includes/footer.php');
?>
