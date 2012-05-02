<?php

/* $Id$*/

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

$title = _('Create GL Budgets');

include('includes/header.inc');

if (isset($_POST['SelectedAccount'])){
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])){
	$SelectedAccount = $_GET['SelectedAccount'];
}

if (isset($_POST['Previous'])) {
	$SelectedAccount = $_POST['PrevAccount'];
} elseif (isset($_POST['Next'])) {
	$SelectedAccount = $_POST['NextAccount'];
}

if (isset($_POST['update'])) {
	prnMsg(_('Budget updated successfully'), 'success');
}

//If an account has not been selected then select one here.
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $title.'</p>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" name="selectaccount">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

echo '<br /><tr><td>'.  _('Select GL Account').  ':</td><td><select name="SelectedAccount"
		onChange="ReloadForm(selectaccount.Select)">';

$SQL = "SELECT accountcode,
				accountname
			FROM chartmaster
			ORDER BY accountcode";

$result=DB_query($SQL,$db);
if (DB_num_rows($result)==0){
	echo '</select></td></tr>';
	prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('budgets cannot be allocated until the GL accounts are set up'),'warn');
} else {
	while ($myrow=DB_fetch_array($result)){
		$account = $myrow['accountcode'] . ' - ' . $myrow['accountname'];
		if (isset($SelectedAccount) and isset($LastCode) and $SelectedAccount==$myrow['accountcode']){
			echo '<option selected="True" value=' . $myrow['accountcode'] . '>' . $account . '</option>';
			$PrevCode=$LastCode;
		} else {
			echo '<option value=' . $myrow['accountcode'] . '>' . $account . '</option>';
			if (isset($SelectedAccount) and isset($LastCode) and $SelectedAccount == $LastCode) {
				$NextCode=$myrow['accountcode'];
			}
		}
		$LastCode=$myrow['accountcode'];
	}
	echo '</select></td></tr>';
}

if (!isset($PrevCode)) {$PrevCode='';}
if (!isset($NextCode)) {$NextCode='';}

echo '<input type="hidden" name="PrevAccount" value="'.$PrevCode.'" />';
echo '<input type="hidden" name="NextAccount" value="'.$NextCode.'" />';
echo '</table>';
echo '<br /><table><tr><td><button type="submit" name="Previous">' . _('Prev Account') . '</button></td>';
echo '<td><button type="submit" name="Select">' . _('Select Account') . '</button></td>';
echo '<td><button type="submit" name="Next">' . _('Next Account') . '</td></tr>';
echo '</table><br />';
echo '</form>';

// End of account selection

if (isset($SelectedAccount) and $SelectedAccount != '') {

	$CurrentYearEndPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],0)),$db);

// If the update button has been hit, then update chartdetails with the budget figures
// for this year and next.
	if (isset($_POST['update'])) {
		$ErrMsg = _('Cannot update GL budgets');
		$DbgMsg = _('The SQL that failed to update the GL budgets was');
		for ($i=1; $i<=12; $i++) {
			$SQL="UPDATE chartdetails SET budget='".filter_currency_input($_POST[$i.'last']). "'
					WHERE period='" . ($CurrentYearEndPeriod-(24-$i)) ."'
					AND  accountcode = '" . $SelectedAccount."'";
			$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
			$SQL="UPDATE chartdetails SET budget='".filter_currency_input($_POST[$i.'this']). "'
					WHERE period='" . ($CurrentYearEndPeriod-(12-$i)) ."'
					AND  accountcode = '" . $SelectedAccount."'";
			$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
			$SQL="UPDATE chartdetails SET budget='".filter_currency_input($_POST[$i.'next'])."'
					WHERE period='" .  ($CurrentYearEndPeriod+$i) ."'
					AND  accountcode = '" . $SelectedAccount."'";
			$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
		}
	}
// End of update

	$YearEndYear=Date('Y', YearEndDate($_SESSION['YearEnd'],0));

/* If the periods dont exist then create them */
	for ($i=1; $i <=36; $i++) {
		$MonthEnd=mktime(0,0,0,$_SESSION['YearEnd']+1+$i,0,$YearEndYear-2);
		$period=GetPeriod(Date($_SESSION['DefaultDateFormat'],$MonthEnd),$db, false);
		$PeriodEnd[$period]=Date('M Y',$MonthEnd);
	}
	include('includes/GLPostings.inc'); //creates chartdetails with correct values
// End of create periods

	$SQL="SELECT period,
					budget,
					actual
				FROM chartdetails
				WHERE accountcode='" . $SelectedAccount . "'";

	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)) {
		$budget[$myrow['period']]=$myrow['budget'];
		$actual[$myrow['period']]=$myrow['actual'];
	}


	if (isset($_POST['apportion'])) {
		for ($i=1; $i<=12; $i++) {
			if ($_POST['AnnualAmountLY'] != '0' AND is_numeric(filter_currency_input($_POST['AnnualAmountLY']))){
				$budget[$CurrentYearEndPeriod+$i-24]	=filter_currency_input($_POST['AnnualAmountLY'])/12;
			}
			if ($_POST['AnnualAmountTY'] != '0' AND is_numeric(filter_currency_input($_POST['AnnualAmountTY']))){
				$budget[$CurrentYearEndPeriod+$i-12]	= filter_currency_input($_POST['AnnualAmountTY'])/12;
			}
			if ($_POST['AnnualAmount'] != '0' AND is_numeric(filter_currency_input($_POST['AnnualAmount']))){
				$budget[$CurrentYearEndPeriod+$i]	= filter_currency_input($_POST['AnnualAmount'])/12;
			}
		}
	}

	$LastYearActual=0;
	$LastYearBudget=0;
	$ThisYearActual=0;
	$ThisYearBudget=0;
	$NextYearActual=0;
	$NextYearBudget=0;

// Table Headers

	echo '<form name="form" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><table class="selection">';
	echo '<tr><th colspan="3">'. _('Last Financial Year') .'</th>';
	echo '<th colspan="3">'. _('This Financial Year') .'</th>';
	echo '<th colspan="3">'. _('Next Financial Year') .'</th></tr>';

	echo '<tr><th colspan="3">'. _('Year ended').' - '.
		Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],-1)) .'</th>';
	echo '<th colspan="3">'. _('Year ended').' - '.
		Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],0)) .'</th>';
	echo '<th colspan="3">'. _('Year ended').' - '.
		Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],1)) .'</th></tr>';

	echo '<tr>';
	for ($i=0; $i<3; $i++) {
		echo '<th>'. _('Period'). '</th>
				<th>'. _('Actual') . '</th>
				<th>'. _('Budget') . '</th>';
	}
	echo '</tr>';

// Main Table

	for ($i=1; $i<=12; $i++) {
		echo '<tr>';
		echo '<th>'. $PeriodEnd[$CurrentYearEndPeriod-(24-$i)] .'</th>';
		echo '<td bgcolor="d2e5e8" class="number">'.locale_money_format($actual[$CurrentYearEndPeriod-(24-$i)],$_SESSION['CompanyRecord']['currencydefault']).'</td>';
		echo '<td><input type="text" class="number" size="14" name="'.$i.'last" value="'.locale_money_format($budget[$CurrentYearEndPeriod-(24-$i)], $_SESSION['CompanyRecord']['currencydefault']) .'" /></td>';
		echo '<th>'. $PeriodEnd[$CurrentYearEndPeriod-(12-$i)] .'</th>';
		echo '<td bgcolor="d2e5e8" class="number">'.locale_money_format($actual[$CurrentYearEndPeriod-(12-$i)],$_SESSION['CompanyRecord']['currencydefault']).'</td>';
		echo '<td><input type="text" class="number" size="14" name="'.$i.'this" value="'. locale_money_format($budget[$CurrentYearEndPeriod-(12-$i)],$_SESSION['CompanyRecord']['currencydefault']) .'" /></td>';
		echo '<th>'. $PeriodEnd[$CurrentYearEndPeriod+($i)] .'</th>';
		echo '<td bgcolor="d2e5e8" class="number">'.locale_money_format($actual[$CurrentYearEndPeriod+$i],$_SESSION['CompanyRecord']['currencydefault']).'</td>';
		echo '<td><input type="text" class="number" size="14" name="'.$i.'next" value="'. locale_money_format($budget[$CurrentYearEndPeriod+$i],$_SESSION['CompanyRecord']['currencydefault']) .'" /></td>';
		echo '</tr>';
		$LastYearActual=$LastYearActual+$actual[$CurrentYearEndPeriod-(24-$i)];
		$LastYearBudget=$LastYearBudget+$budget[$CurrentYearEndPeriod-(24-$i)];
		$ThisYearActual=$ThisYearActual+$actual[$CurrentYearEndPeriod-(12-$i)];
		$ThisYearBudget=$ThisYearBudget+$budget[$CurrentYearEndPeriod-(12-$i)];
		$NextYearActual=$NextYearActual+$actual[$CurrentYearEndPeriod+($i)];
		$NextYearBudget=$NextYearBudget+$budget[$CurrentYearEndPeriod+($i)];
	}

// Total Line

	echo '<tr><th>'. _('Total') .'</th>';
	echo '<th align="right">'.locale_money_format($LastYearActual,$_SESSION['CompanyRecord']['currencydefault']). '</th>';
	echo '<th align="right">'.locale_money_format($LastYearBudget,$_SESSION['CompanyRecord']['currencydefault']). '</th>';
	echo '<th align="right"></th>';
	echo '<th align="right">'.locale_money_format($ThisYearActual,$_SESSION['CompanyRecord']['currencydefault']). '</th>';
	echo '<th align="right">'.locale_money_format($ThisYearBudget,$_SESSION['CompanyRecord']['currencydefault']). '</th>';
	echo '<th align="right"></th>';
	echo '<th align="right">'.locale_money_format($NextYearActual,$_SESSION['CompanyRecord']['currencydefault']). '</th>';
	echo '<th align="right">'.locale_money_format($NextYearBudget,$_SESSION['CompanyRecord']['currencydefault']). '</th></tr>';
	echo '<tr><td colspan="2">'._('Annual Budget').'</td>
				<td><input class="number" type="text" size="14" name="AnnualAmountLY" value="'.locale_money_format(0, $_SESSION['CompanyRecord']['currencydefault']).'" /></td>
				</td><td><td></td>
				<td><input class="number" type="text" size="14" name="AnnualAmountTY" value="'.locale_money_format(0, $_SESSION['CompanyRecord']['currencydefault']).'" /></td>
				<td></td>
				<td><input onChange="numberFormat(this,2)" class="number" type="text" size="14" name="AnnualAmount" value="'.locale_money_format(0, $_SESSION['CompanyRecord']['currencydefault']).'" /></td>';
	echo '<td><button type="submit" name="apportion">' . _('Apportion Budget') . '</button></td>';
	echo '</tr>';
	echo '</table>';
	echo '<input type="hidden" name="SelectedAccount" value="'.$SelectedAccount.'" />';

	echo '<script>defaultControl(document.form.1next);</script>';
	echo '<br /><div class="centre"><button type="submit" name="update">' . _('Update') . '</button></div><br /></form>';

	$SQL="SELECT MIN(periodno) FROM periods";
	$result=DB_query($SQL,$db);
	$MyRow=DB_fetch_array($result);
	$FirstPeriod=$MyRow[0];

	$SQL="SELECT MAX(periodno) FROM periods";
	$result=DB_query($SQL,$db);
	$MyRow=DB_fetch_array($result);
	$LastPeriod=$MyRow[0];

	for ($i=$FirstPeriod;$i<=$LastPeriod;$i++) {
		$sql="SELECT accountcode,
							period,
							budget,
							actual,
							bfwd,
							bfwdbudget
						FROM chartdetails
						WHERE period ='". $i . "' AND  accountcode = '" . $SelectedAccount . "'";

		$ErrMsg = _('Could not retrieve the ChartDetail records because');
		$result = DB_query($sql,$db,$ErrMsg);

		while ($myrow=DB_fetch_array($result)){

			$CFwdBudget = $myrow['bfwdbudget'] + $myrow['budget'];
			$sql = "UPDATE chartdetails
				SET bfwdbudget='" . $CFwdBudget . "'
				WHERE period='" . ($myrow['period'] +1) . "'
				AND  accountcode = '" . $SelectedAccount . "'";

			$ErrMsg =_('Could not update the chartdetails record because');
			$updresult = DB_query($sql,$db,$ErrMsg);
		}
	} /* end of for loop */
}

include('includes/footer.inc');

?>