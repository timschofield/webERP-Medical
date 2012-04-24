<?php

include('includes/session.inc');
$title = _('Synchronise with Care2x Item Table');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/Vial-Pills.png" title="' . _('Search') . '" alt="" />' . ' ' . $title . '</p>';

if (isset($_POST['UpdateLink'])) {
	for ($i=0; $i<$_POST['TotalItems']; $i++) {
		echo $i;
		$SQL="UPDATE ".$_SESSION['Care2xDatabase'].".care_tz_drugsandservices
				SET partcode='".$_POST['StockID'.$i]."'
				WHERE item_id='".$_POST['Care2xItem'.$i]."'";
		$result=DB_query($SQL, $db);
	}
}

if (!isset($_POST['CategoryID'])) {
	echo '<form enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'; // Nested table
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<td>' . _('Category') . ':</td>
				<td><select name="CategoryID">';

	$sql = "SELECT categoryid, categorydescription FROM stockcategory";
	$ErrMsg = _('The stock categories could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	while ($myrow=DB_fetch_array($result)){
		if (!isset($_POST['CategoryID']) or $myrow['categoryid']==$_POST['CategoryID']){
			echo '<option selected="True" value="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		} else {
			echo '<option value="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		}
		$Category=$myrow['categoryid'];
	}

	if (!isset($_POST['CategoryID'])) {
		$_POST['CategoryID']=$Category;
	}

	echo '</select></td>
			<td><a target="_blank" href="'. $rootpath . '/StockCategories.php">' . _('Add or Modify Stock Categories') . '</a></td>
		</tr>';

	echo '<tr>
			<td colspan="3"><div class="centre"><button type="submit" name="FindItems">' . _('Find Items') . '</button></div></td>
		</tr>';
	echo '</table></form>';
} else {
	echo '<form enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">'; // Nested table
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	$SQL = "SELECT stockid,
					description
				FROM stockmaster
				WHERE categoryid='" . $_POST['CategoryID'] . "'";
	$result = DB_query($SQL, $db);
	$Care2xSQL="SELECT item_id,
						partcode,
						item_full_description
					FROM ".$_SESSION['Care2xDatabase'].".care_tz_drugsandservices
					ORDER BY UCASE(item_full_description)";
	$Care2xResult=DB_query($Care2xSQL, $db);
	$Care2xItems=array();
	while ($Care2xRow=DB_fetch_array($Care2xResult)){
		$Care2xItems[$Care2xRow['item_id']]=$Care2xRow['item_full_description'];
		$Care2xPartCode[$Care2xRow['item_id']]=$Care2xRow['partcode'];
	}
	$i=0;
	while ($myrow=DB_fetch_array($result)) {
		echo '<tr>
				<td>' . $myrow['stockid'] . '</td>
				<td>' . $myrow['description'] . '</td>';
		echo '<td><select name="Care2xItem'.$i.'">';
		foreach ($Care2xItems as $ItemID=>$Description){
			if ($Care2xPartCode[$ItemID]==$myrow['stockid']){
				echo '<option selected="selected" value="'. $ItemID . '">' . $Description . '</option>';
			} else {
				echo '<option value="'. $ItemID . '">' . $Description . '</option>';
			}
		}
		echo '</select></td></tr>';
		echo '<input type="hidden" name="StockID' . $i . '" value="'.$myrow['stockid'].'" />';
		$i++;
	}
	echo '<input type="hidden" name="TotalItems" value="'.$i.'" />';
	echo '<tr>
			<td colspan="3"><div class="centre"><button type="submit" name="UpdateLink">' . _('Update') . '</button></div></td>
		</tr>';
	echo '</table></form>';
}



include('includes/footer.inc');

?>