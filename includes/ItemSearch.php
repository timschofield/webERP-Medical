<?php
echo '<script type="text/javascript">
function ShowItems(Category, Code, Description, MaxItems, identifier)
{
if (Category=="")
  {
  document.getElementById("txtHint").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","includes/ItemShowSearch.php?Category="+Category+"&Code="+Code+"&Description="+Description+"&MaxItems="+MaxItems+"&identifier="+identifier,true);
xmlhttp.send();
}
</script>';

function ShowItemSearchFields($rootpath, $theme, $db, $identifier) {

	$PathPrefix=$_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']) . '/';
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Items') . '</p>';
	echo '<div class="page_help_text">' . _('Search for Items') . _(', Searches the database for items, you can narrow the results by selecting a stock category, or just enter a partial item description or partial item code') . '.</div><br />';
	echo '<table width="98%">
			<tr>
				<td width="33%" valign="top">';

	/* Search Criteria */
	echo '<table class="selection" width="98%">
			<tr>
				<td><b>' . _('Select a Stock Category') . ': </b></td>
				<td><select tabindex="1" name="StockCat" onchange="ShowItems(this.value, StockCode.value, Keywords.value, MaxItems.value,' . $identifier . ')">';

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
			<td><input tabindex="2" type="text" name="Keywords" size="20" maxlength="25" value="' . $_POST['Keywords'] . '" onkeyup="ShowItems(StockCat.value, StockCode.value, Keywords.value, MaxItems.value,' . $identifier . ')" /></td>
		</tr>
		<tr>
			<td><b>' . _('OR') . ' ' . _('Enter extract of the Stock Code') . ':</b></td>
			<td><input tabindex="3" type="text" name="StockCode" size="15" maxlength="18" value="' . $_POST['StockCode'] . '" onkeyup="ShowItems(StockCat.value, StockCode.value, Keywords.value, MaxItems.value,' . $identifier . ')" /></td>
		</tr>
		<tr>
			<td><b>' . _('Maximum number of Items to Show') . ':</b></td>
			<td>
				<select name="MaxItems" onchange="ShowItems(StockCat.value, StockCode.value, Keywords.value, MaxItems.value,' . $identifier . ')">
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
	include('includes/ItemShowSearch.php');
	echo '</td>
		</tr>
		</table>';
}

?>