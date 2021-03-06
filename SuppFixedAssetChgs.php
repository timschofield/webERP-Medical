<?php

/* $Id:  $*/

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of Asset objects called Assets- containing details of all asset additions on a supplier invoice
Asset additions are posted to the debit of fixed asset category cost account if the creditors GL link is on */

include('includes/DefineSuppTransClass.php');

/* Session started here for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Fixed Asset Charges or Credits');

include('includes/header.inc');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('Fixed asset additions or credits are entered against supplier invoices or credit notes respectively') . '. ' . _('To enter supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice or credit note must be clicked on'),'info');
	echo '<br /><a href="' . $rootpath . '/SelectSupplier.php">' . _('Select A Supplier') . '</a>';
	exit;
	/*It all stops here if there aint no supplier selected and invoice/credit initiated ie $_SESSION['SuppTrans'] started off*/
}

if (isset($_POST['Amount'])) {
	$_POST['Amount'] = filter_currency_input($_POST['Amount']);
}

if (isset($_POST['AddAssetToInvoice'])){

	$InputError = False;
	if ($_POST['AssetID'] == ''){
		if ($_POST['AssetSelection']==''){
			$InputError = True;
			prnMsg(_('A valid asset must be either selected from the list or entered'),'error');
		} else {
			$_POST['AssetID'] = $_POST['AssetSelection'];
		}
	} else {
		$result = DB_query("SELECT assetid FROM fixedassets WHERE assetid='" . $_POST['AssetID'] . "'",$db);
		if (DB_num_rows($result)==0) {
			prnMsg(_('The asset ID entered manually is not a valid fixed asset. If you do not know the asset reference, select it from the list'),'error');
			$InputError = True;
			unset($_POST['AssetID']);
		}
	}

	if (!is_numeric($_POST['Amount'])){
		prnMsg(_('The amount entered is not numeric. This fixed asset cannot be added to the invoice'),'error');
		$InputError = True;
		unset($_POST['Amount']);
	}

	if ($InputError == False){
		$_SESSION['SuppTrans']->Add_Asset_To_Trans($_POST['AssetID'], $_POST['Amount']);
		unset($_POST['AssetID']);
		unset($_POST['Amount']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_Asset_From_Trans($_GET['Delete']);
}

/*Show all the selected ShiptRefs so far from the SESSION['SuppInv']->Shipts array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice'){
	echo '<div class="centre"><p class="page_title_text">'. _('Fixed Assets on Invoice') . ' </p></div>';
} else {
	echo '<div class="centre"><p class="page_title_text">' . _('Fixed Asset credits on Credit Note') . ' </p>';
}
echo'<div class="centre"><p class="page_title_text">' . $_SESSION['SuppTrans']->SuppReference . ' ' ._('From') . ' ' . $_SESSION['SuppTrans']->SupplierName . '</p></div>';

echo '<table cellpadding="2" class="selection">';
$TableHeader = '<tr><th>' . _('Asset ID') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Amount') . '</th></tr>';
echo $TableHeader;

$TotalAssetValue = 0;

foreach ($_SESSION['SuppTrans']->Assets as $EnteredAsset){

	echo '<tr><td>' . $EnteredAsset->AssetID . '</td>
						<td>' . $EnteredAsset->Description . '</td>
		<td class="number">' . locale_money_format($EnteredAsset->Amount,$_SESSION['SuppTrans']->CurrCode) . '</td>
		<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '&Delete=' . $EnteredAsset->Counter . '">' . _('Delete') . '</a></td></tr>';

	$TotalAssetValue +=  $EnteredAsset->Amount;

}

echo '<tr>
	<th class="header">' . _('Total') . ':</th>
	<th class="header"><u>' . locale_money_format($TotalAssetValue,$_SESSION['SuppTrans']->CurrCode) . '</u></th>
</tr>
</table><br />';

if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo '<div class="centre"><a href="' . $rootpath . '/SupplierInvoice.php">' . _('Back to Invoice Entry') . '</a></div>';
} else {
	echo '<div class="centre"><a href="' . $rootpath . '/SupplierCredit.php">' . _('Back to Credit Note Entry') . '</a></div>';
}

/*Set up a form to allow input of new Shipment charges */
echo '<br /><form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['AssetID'])) {
	$_POST['AssetID']='';
}

prnMsg(_('If you know the code enter it in the Asset ID input box, otherwise select the asset from the list below. Only  assets with no cost will show in the list'),'info');

echo '<br /><table class="selection">';

echo '<tr><td>' . _('Enter Asset ID') . ':</td>
	<td><input type="text" name="AssetID" size="5" maxlength="6" value="' .  $_POST['AssetID'] . '" /> <a href="FixedAssetItems.php" target=_blank>'. _('New Fixed Asset') . '</a></td></tr>';

$sql = "SELECT assetid,
				description
			FROM fixedassets
			WHERE cost=0
			ORDER BY assetid DESC";

$result = DB_query($sql, $db);
echo '<tr><td><b>' . _('OR') .' </b>'. _('Select from list') . ':</td><td><select name="AssetSelection">';

while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['AssetSelection']) AND $myrow['AssetID']==$_POST['AssetSelection']) {
		echo '<option selected="True" value="' .  $myrow['assetid'] . '">' . $myrow['assetid'] . ' - ' . $myrow['description']  . '</option>';
	} else {
		echo '<option value="' .  $myrow['assetid'] . '">' . $myrow['assetid'] . ' - ' . $myrow['description']  . '</option>';
	}
}

echo '</select></td></tr>';

if (!isset($_POST['Amount'])) {
	$_POST['Amount']=0;
}
echo '<tr><td>' . _('Amount') . ':</td>
	<td><input type="text" class="number" name="Amount" size="12" maxlength="11" value="' .  locale_money_format($_POST['Amount'], $_SESSION['SuppTrans']->CurrCode) . '" /></td></tr>';
echo '</table>';

echo '<br /><div class="centre"><button type="submit" name="AddAssetToInvoice">' . _('Enter Fixed Asset') . '</button></div>';

echo '</form>';
include('includes/footer.inc');
?>