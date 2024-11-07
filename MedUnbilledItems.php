<?php

include('includes/session.inc');
$title = _('Items prescribed but not billed');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $title.'</p>';

if (!isset($_POST['Show'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';
	echo '<tr><td>' . _('Transactions Dated From') . ':</td>
		<td><input type="text" name="FromTransDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" maxlength="10" size="11" onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' . date($_SESSION['DefaultDateFormat']) . '" /></td></tr>
		<tr><td>' . _('Transactions Dated To') . ':</td>
		<td><input type="text" name="ToTransDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" maxlength="10" size="11" onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' . date($_SESSION['DefaultDateFormat']) . '" /></td>
		</tr>';

	echo '</table>';
	echo '<br /><div class="centre"><button type="submit" name="Show">' . _('Show transactions'). '</button></div>';
	echo '</form>';
} else {
	$SQL="SELECT nr,
				pid,
				name,
				prescribe_date,
				description,
				total_dosage,
				decimalplaces
			FROM ".$_SESSION['Care2xDatabase'].".care_encounter_prescription
			INNER JOIN ".$_SESSION['Care2xDatabase'].".care_encounter
			ON ".$_SESSION['Care2xDatabase'].".care_encounter_prescription.encounter_nr=".$_SESSION['Care2xDatabase'].".care_encounter.encounter_nr
			INNER JOIN debtorsmaster
			ON ".$_SESSION['Care2xDatabase'].".care_encounter.pid=debtorno
			INNER JOIN ".$_SESSION['Care2xDatabase'].".care_tz_drugsandservices
			ON ".$_SESSION['Care2xDatabase'].".care_encounter_prescription.article_item_number=".$_SESSION['Care2xDatabase'].".care_tz_drugsandservices.item_id
			INNER JOIN stockmaster
			ON stockmaster.stockid=".$_SESSION['Care2xDatabase'].".care_tz_drugsandservices.partcode
			WHERE bill_number=''
			AND prescribe_date between '".FormatDateForSQL($_POST['FromTransDate'])."' AND '".FormatDateForSQL($_POST['ToTransDate'])."'";

	$result=DB_query($SQL, $db);

	echo '<div class="page_help_text">' . _('This is a list of all items that have been prescribed, but that a bill has not yet been produced.') . '<br />' . _('This list should be empty at the end of each day') . '</div>';

	echo '<br /><table class="selection">';
	echo '<tr>
			<th>' . _('Prescription Date') . '</th>
			<th>' . _('Patient Number') . '</th>
			<th>' . _('Patient Name') . '</th>
			<th>' . _('Item Description') . '</th>
			<th>' . _('Quantity') . '</th>
		</tr>';
	while ($myrow=DB_fetch_array($result)) {
		echo '<tr>
				<td>' . ConvertSQLDate($myrow['prescribe_date']) . '</td>
				<td>' . $myrow['pid'] . '</td>
				<td>' . $myrow['name'] . '</td>
				<td>' . $myrow['description'] . '</td>
				<td class="number">' . locale_number_format($myrow['total_dosage'], $myrow['decimalplaces']) . '</td>
			</tr>';
	}
	echo '</table>';
}
include('includes/footer.inc');

?>