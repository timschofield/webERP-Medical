<?php

/* $Id$*/

include('includes/session.inc');

$title = _('Authorise Internal Stock Requests');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . $title . '" alt="" />' . ' ' . $title . '</p>';

$emailsql="SELECT email FROM www_users WHERE userid='".$_SESSION['UserID']."'";
$emailresult=DB_query($emailsql, $db);
$emailrow=DB_fetch_array($emailresult);

if (isset($_POST['updateall'])) {
	foreach ($_POST as $key => $value) {
		if (mb_substr($key,0,6)=='status') {
			$RequestNo=mb_substr($key,6);
			$sql="UPDATE stockrequest
				SET authorised='1'
				WHERE dispatchid='".$RequestNo."'";
			$result=DB_query($sql, $db);
		}
	}
}

/* Retrieve the purchase order header information
 */
$sql="SELECT stockrequest.dispatchid,
			locations.locationname,
			stockrequest.despatchdate,
			stockrequest.narrative,
			departments.description,
			www_users.realname,
			www_users.email
		FROM stockrequest
		LEFT JOIN departments
			ON stockrequest.departmentid=departments.departmentid
		LEFT JOIN locations
			ON stockrequest.loccode=locations.loccode
		LEFT JOIN www_users
			ON www_users.userid=departments.authoriser
	WHERE stockrequest.authorised=0
		AND www_users.userid='".$_SESSION['UserID']."'";
$result=DB_query($sql, $db);

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection"><tr>';

/* Create the table for the purchase order header */
echo '<th>'._('Request Number').'</th>';
echo '<th>'._('Department').'</th>';
echo '<th>'._('Location Of Stock').'</th>';
echo '<th>'._('Requested Date').'</th>';
echo '<th>'._('Narrative').'</th>';
echo '<th>'._('Authorise').'</th>';
echo '</tr>';

while ($myrow=DB_fetch_array($result)) {

	echo '<tr>';
	echo '<td>'.$myrow['dispatchid'].'</td>';
	echo '<td>'.$myrow['description'].'</td>';
	echo '<td>'.$myrow['locationname'].'</td>';
	echo '<td>'.ConvertSQLDate($myrow['despatchdate']).'</td>';
	echo '<td>'.$myrow['narrative'].'</td>';
	echo '<td><input type="checkbox" name="status'.$myrow['dispatchid'].'" /></td>';
	echo '</tr>';
	$linesql="SELECT stockrequestitems.dispatchitemsid,
						stockrequestitems.stockid,
						stockrequestitems.decimalplaces,
						stockrequestitems.uom,
						stockmaster.description,
						stockrequestitems.quantity
				FROM stockrequestitems
				LEFT JOIN stockmaster
				ON stockmaster.stockid=stockrequestitems.itemid
			WHERE dispatchid='".$myrow['dispatchid'] . "'";
	$lineresult=DB_query($linesql, $db);

	echo '<tr><td></td><td colspan="5" align="left"><table class="selection" align="left">';
	echo '<th>'._('Product').'</th>';
	echo '<th>'._('Quantity Required').'</th>';
	echo '<th>'._('Units').'</th>';
	echo '</tr>';

	while ($linerow=DB_fetch_array($lineresult)) {
		echo '<tr>';
		echo '<td>'.$linerow['description'].'</td>';
		echo '<td class="number">'.locale_number_format($linerow['quantity'],$linerow['decimalplaces']).'</td>';
		echo '<td>'.$linerow['uom'].'</td>';
		echo '</tr>';
	} // end while order line detail
	echo '</table></td></tr>';
} //end while header loop
echo '</table>';
echo '<br /><div class="centre"><input type="submit" name="updateall" value="' . _('Update'). '" /></form>';

include('includes/footer.inc');
?>