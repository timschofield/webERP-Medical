<?php
/* $Revision: 1.3 $ */
/* $Id$*/

include('includes/session.inc');
$title = _('View Currency Trends');

include('includes/header.inc');

$graph = '/graph120.png';

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];

if ( isset($_GET['CurrencyToShow']) ){
	$CurrencyToShow = $_GET['CurrencyToShow'];
} elseif ( isset($_POST['CurrencyToShow']) ) {
	$CurrencyToShow = $_POST['CurrencyToShow'];
}

// ************************
// SHOW OUR MAIN INPUT FORM
// ************************

	echo '<form method="post" name=update action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('View Currency Trend') . '" alt="" />' . ' ' . _('View Currency Trend') . '</p>';
	echo '<table class="selection">'; // First column

	$SQL = "SELECT currabrev,
					country,
					currency
				FROM currencies";
	$result=DB_query($SQL,$db);


	// CurrencyToShow Currency Picker
	echo '<tr><td><select name="CurrencyToShow" onChange="ReloadForm(update.submit)">';

		DB_data_seek($result,0);
		while ($myrow=DB_fetch_array($result)) {
			if ($myrow['currabrev']!=$_SESSION['CompanyRecord']['currencydefault']){
				if ( $CurrencyToShow==$myrow['currabrev'] )	{
					echo '<option selected="True" value="' . $myrow['currabrev'] . '">' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')'. '</option>';
				} else {
					echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')'. '</option>';
				}
			}
		}
		echo '</select></td></tr>';

   	echo '</table>'; /*close off the table in the third column */


	echo '<br /><div class="centre"><button type="submit" name="submit">' . _('Accept') . '</button></div>';
   	echo '</form>';



// **************
// SHOW OUR GRAPH
// **************


	$graph = $CurrencyToShow. '/' . $FunctionalCurrency . $graph;
	$image = 'http://www.x-rates.com/d/' . $graph;

	echo '<br /></div><table class="selection">';
	echo '<tr><th class="header">' . $FunctionalCurrency . ' / ' . $CurrencyToShow . '</th></tr>';
	echo '<tr><td><img src=' . $image . ' alt="Trend Currently Unavailable" /></td></tr>';
	echo '</table>';


include('includes/footer.inc');
?>