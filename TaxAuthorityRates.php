<?php
/* $Id$*/

if (isset($_POST['TaxAuthority'])){
	$TaxAuthority = $_POST['TaxAuthority'];
}
if (isset($_GET['TaxAuthority'])){
	$TaxAuthority = $_GET['TaxAuthority'];
}

include('includes/session.inc');
$title = _('Tax Rates');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Supplier Types') . '" alt="" />' . $title. '</p>';

/* <-- $Revision: 1.16 $ --> */

if (!isset($TaxAuthority)){
	prnMsg(_('This page can only be called after selecting the tax authority to edit the rates for') . '. ' . _('Please select the Rates link from the tax authority page') . ".<br /><a href='$rootpath/TaxAuthorities.php'>" . _('click here') . '</a> ' . _('to go to the Tax Authority page'),'error');
	include ('includes/footer.inc');
	exit;
}


if (isset($_POST['UpdateRates'])){

	$TaxRatesResult = DB_query("SELECT taxauthrates.taxcatid,
						taxauthrates.taxrate,
						taxauthrates.dispatchtaxprovince
						FROM taxauthrates
						WHERE taxauthrates.taxauthority='" . $TaxAuthority . "'",
						$db);

	while ($myrow=DB_fetch_array($TaxRatesResult)){

		$sql = "UPDATE taxauthrates SET taxrate=" . ($_POST[$myrow['dispatchtaxprovince'] . '_' . $myrow['taxcatid']]/100) . "
						WHERE taxcatid = '" . $myrow['taxcatid'] . "'
						AND dispatchtaxprovince = '" . $myrow['dispatchtaxprovince'] . "'
						AND taxauthority = '" . $TaxAuthority . "'";
		DB_query($sql,$db);
	}
	prnMsg(_('All rates updated successfully'),'info');
}

/* end of update code
*/

/*Display updated rates
*/

$TaxAuthDetail = DB_query("SELECT description FROM taxauthorities WHERE taxid='" . $TaxAuthority . "'",$db);
$myrow = DB_fetch_array($TaxAuthDetail);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<input type="hidden" name="TaxAuthority" value="' . $TaxAuthority . '" />';

$TaxRatesResult = DB_query("SELECT taxauthrates.taxcatid,
						taxcategories.taxcatname,
						taxauthrates.taxrate,
						taxauthrates.dispatchtaxprovince,
						taxprovinces.taxprovincename
						FROM taxauthrates INNER JOIN taxauthorities
							ON taxauthrates.taxauthority=taxauthorities.taxid
							INNER JOIN taxprovinces
							ON taxauthrates.dispatchtaxprovince= taxprovinces.taxprovinceid
							INNER JOIN taxcategories
							ON taxauthrates.taxcatid=taxcategories.taxcatid
						WHERE taxauthrates.taxauthority='" . $TaxAuthority . "'
						ORDER BY taxauthrates.dispatchtaxprovince,
						taxauthrates.taxcatid",
					$db);

if (DB_num_rows($TaxRatesResult)>0){

	echo '<table cellpadding="2" class="selection">';
	echo '<tr><th colspan="3" class="header">' . _('Update') . ' ' . $myrow['description'] . ' ' . _('Rates') . '</th></tr>';
	$TableHeader = '<tr><th>' . _('Deliveries From') . '<br />' . _('Tax Province') . '</th>
						<th>' . _('Tax Category') . '</th>
						<th>' . _('Tax Rate') . ' %</th></tr>';
	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
	$OldProvince='';

	while ($myrow = DB_fetch_array($TaxRatesResult)){

		if ($OldProvince!=$myrow['dispatchtaxprovince'] AND $OldProvince!=''){
			echo '<tr bgcolor="#555555"><font size="1"> </font><td colspan="3"></td></tr>';
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		printf('<td>%s</td>
			<td>%s</td>
			<td><input type="text" class="number" name="%s" maxlength="5" size="5" value="%s" /></td>
			</tr>',
			$myrow['taxprovincename'],
			$myrow['taxcatname'],
			$myrow['dispatchtaxprovince'] . '_' . $myrow['taxcatid'],
			$myrow['taxrate']*100 );

		$OldProvince = $myrow['dispatchtaxprovince'];

	}
//end of while loop
echo '</table>';
echo '<br /><div class="centre"><button type="submit" name="UpdateRates">' . _('Update Rates') . '</button>';
} //end if tax taxcatid/rates to show
	else {
	prnMsg(_('There are no tax rates to show - perhaps the dispatch tax province records have not yet been created?'),'warn');
}

echo '</form>';

echo '<br /><br /><a href="' . $rootpath . '/TaxAuthorities.php">' . _('Tax Authorities') .  '</a>';
echo '<br /><a href="' . $rootpath . '/TaxGroups.php">' . _('Tax Groupings') .  '</a>';
echo '<br /><a href="' . $rootpath . '/TaxCategories.php">' . _('Tax Categories') .  '</a>';
echo '<br /><a href="' . $rootpath . '/TaxProvinces.php">' . _('Dispatch Tax Provinces') .  '</a>';
echo '</div>';

include( 'includes/footer.inc' );
?>