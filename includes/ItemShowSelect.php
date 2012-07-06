<?php
$PageSecurity=1;

if (!isset($PathPrefix)) {
	$PathPrefix=$_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']) . '/../';
	$rootpath = dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
}

include($PathPrefix . 'config.php');
require_once($PathPrefix . 'includes/session.inc');
if (isset($_GET['Category'])) {
	$Category=$_GET['Category'];
} else {
	$Category='%';
}
if (isset($_GET['identifier'])) {
	$identifier=$_GET['identifier'];
}
if (isset($_GET['Code'])) {
	$Code=$_GET['Code'];
} else {
	$Code='%';
}
if (isset($_GET['Description'])) {
	$Description=$_GET['Description'];
} else {
	$Description='%';
}

if (isset($_GET['MaxItems'])) {
	$MaxItems=$_GET['MaxItems'];
} else {
	$MaxItems=10;
}

function __autoload($Cart) {
	global $PathPrefix;
    include $PathPrefix . 'includes/DefineCartClass.php';
}

$db = mysqli_connect($host , $dbuser, $dbpassword,$_SESSION['DatabaseName'], $mysqlport);
$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.units,
				stockmaster.perishable,
				stockmaster.controlled,
				stockmaster.decimalplaces
			FROM stockmaster
			INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			WHERE ".$_SESSION['StockTypesSQL']."
				".$_SESSION['MBFlagSQL']."
				AND stockmaster.discontinued=0
				AND stockmaster.categoryid like '".$Category."'
				AND stockmaster.description like '%".$Description."%'
				AND stockmaster.stockid like '%".$Code."%'
			ORDER BY stockmaster.stockid
			LIMIT 0,".($MaxItems+1);
$SearchResult = mysqli_query($db, $SQL);

echo '<input type="hidden" name="identifier" value="' . $identifier . '" />';
echo '<table class="selection" width="98%" id="txtHint">
		<tr>
			<th>' . _('Code') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('UOM') . '</th>
			<th>' . _('Select') . '</th>
		</tr>';
$k=0;
$i=0;

while ($myrow=DB_fetch_array($SearchResult)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	echo '<input type="hidden" name="StockID'.$i.'" value="'.$myrow['stockid'].'" />';
	printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><input type="radio" name="Selected" value="' . $i . '" /></td>
		</tr>',
			$myrow['stockid'],
			$myrow['description'],
			$myrow['units']);
	echo '<input type="hidden" name="Units' . $i . '" value="' . $myrow['units'] . '" />';
	$i++;
}
#end of while loop
echo '<tr>
		<th colspan="9"><button type="submit" name="OrderItems">'._('Select').'</button></th>
	</tr>';
echo '</table>';

?>