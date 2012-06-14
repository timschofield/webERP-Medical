<?php

echo '<script type="text/javascript" src = "'.$rootpath.'/javascripts/ItemSearch.js"></script>';

function ShowItemSearchFields($rootpath, $theme, $db, $identifier, $MBFlags, $StockTypes, $SearchOrSelect) {

	$PathPrefix=$_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']) . '/';
	echo '<table width="98%">
			<tr>
				<td width="33%" valign="top">';

	/* Search Criteria */
	echo '<table class="selection" width="98%">
			<tr>
				<td><b>' . _('Select a Stock Category') . ': </b></td>
				<td><select tabindex="1" name="StockCat" onchange="ShowItems(\'' . $SearchOrSelect . '\', StockCat.value, StockCode.value, Keywords.value, MaxItems.value,' . $identifier . ')">';

	if (!isset($_POST['Keywords'])) {
		$_POST['Keywords']='';
	}
	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode']='';
	}

	$SQL="SELECT categoryid,
				categorydescription
			FROM stockcategory
			WHERE stocktype='F' OR stocktype='D'
			ORDER BY categorydescription";
	$result1 = DB_query($SQL,$db);
	if (!isset($_POST['StockCat'])){
		echo '<option selected="True" value="%">' . _('All').'</option>';
		$_POST['StockCat'] ='All';
	} else {
		echo '<option value="%">' . _('All').'</option>';
	}
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($_POST['StockCat']==$myrow1['categoryid']){
			echo '<option selected="True" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
		} else {
			echo '<option value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
		}
	}

	echo '</select></td></tr>
		<tr>
			<td><b>' . _('Enter partial Description') . ':</b></td>
			<td><input tabindex="2" type="text" name="Keywords" size="20" maxlength="25" value="' . $_POST['Keywords'] . '" onkeyup="ShowItems(\'' . $SearchOrSelect . '\', StockCat.value, StockCode.value, Keywords.value, MaxItems.value,' . $identifier . ')" /></td>
		</tr>
		<tr>
			<td><b>' . _('OR') . ' ' . _('Enter extract of the Stock Code') . ':</b></td>
			<td><input tabindex="3" type="text" name="StockCode" size="15" maxlength="18" value="' . $_POST['StockCode'] . '" onkeyup="ShowItems(\'' . $SearchOrSelect . '\', StockCat.value, StockCode.value, Keywords.value, MaxItems.value,' . $identifier . ')" /></td>
		</tr>
		<tr>
			<td><b>' . _('Maximum number of Items to Show') . ':</b></td>
			<td>
				<select name="MaxItems" onchange="ShowItems(\'' . $SearchOrSelect . '\', StockCat.value, StockCode.value, Keywords.value, MaxItems.value,' . $identifier . ')">
					<option value="10">10</option>
					<option value="20">20</option>
					<option value="30">30</option>
					<option value="40">40</option>
					<option value="50">50</option>
					<option value="60">60</option>
					<option value="70">70</option>
					<option value="80">80</option>
					<option value="90">90</option>
					<option value="100">100</option>
				</select>
			</td>
		</tr>
		</table><br />';

	if (!isset($_POST['PartSearch'])) {
		echo '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.Keywords);}</script>';
	}
	echo '</td>';

	/* Search Results*/
	echo '<td width="67%" valign="top">';

	unset($_SESSION['MBFlagSQL']);
	unset($_SESSION['StockTypesSQL']);
	$MBFlagSQL=" AND (";
	foreach ($MBFlags as $MBFlag) {
		$MBFlagSQL .= "stockmaster.mbflag='".$MBFlag."' OR ";
	}
	$_SESSION['MBFlagSQL']=mb_substr($MBFlagSQL, 0, mb_strlen($MBFlagSQL)-3).")";

	$StockTypesSQL=" (";
	foreach ($StockTypes as $StockType) {
		$StockTypesSQL .= "stockcategory.stocktype='".$StockType."' OR ";
	}
	$_SESSION['StockTypesSQL']=mb_substr($StockTypesSQL, 0, mb_strlen($StockTypesSQL)-3).") ";

	if ($SearchOrSelect=='Search') {
		include('includes/ItemShowSearch.php');
	} else {
		include('includes/ItemShowSelect.php');
	}
	echo '</td>
		</tr>
		</table>';
}

?>