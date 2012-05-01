<?php

/* $Id$*/

include('includes/session.inc');

$title = _('Authorise Purchase Orders');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . $title . '" alt="" />' . ' ' . $title . '</p>';

$emailsql="SELECT email FROM www_users WHERE userid='".$_SESSION['UserID']."'";
$emailresult=DB_query($emailsql, $db);
$emailrow=DB_fetch_array($emailresult);

if (isset($_POST['updateall'])) {
	foreach ($_POST as $key => $value) {
		if (mb_substr($key,0,6)=='status') {
			$orderno=mb_substr($key,6);
			$status=$_POST['status'.$orderno];
			$comment=date($_SESSION['DefaultDateFormat']).' - '._('Authorised by').' '.'<a href="mailto:'. $emailrow['email'].'">'.$_SESSION['UserID'].'</a><br />'.$_POST['comment'];
			$sql="UPDATE purchorders
				SET status='".$status."',
				stat_comment='".$comment."',
				allowprint=1
				WHERE orderno='".$orderno."'";
			$result=DB_query($sql, $db);
		}
	}
}

/* Retrieve the purchase order header information
 */
$sql="SELECT purchorders.orderno,
			purchorders.orddate,
			purchorders.deliverydate,
			purchorders.stat_comment,
			suppliers.suppname,
			suppliers.currcode,
			www_users.realname,
			www_users.email
			FROM purchorders
		LEFT JOIN suppliers
			ON suppliers.supplierid=purchorders.supplierno
		LEFT JOIN www_users
			ON www_users.userid=purchorders.initiator
	WHERE status='Pending'";
$result=DB_query($sql, $db);

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection"><tr>';

/* Create the table for the purchase order header */
echo '<th>'._('Order Number').'</th>';
echo '<th>'._('Supplier').'</th>';
echo '<th>'._('Date Ordered').'</th>';
echo '<th>'._('Initiator').'</th>';
echo '<th>'._('Delivery Date').'</th>';
echo '<th>'._('Status').'</th>';
echo '</tr>';

while ($myrow=DB_fetch_array($result)) {

	$authsql="SELECT authlevel FROM purchorderauth
				WHERE userid='".$_SESSION['UserID']."'
				AND currabrev='".$myrow['currcode']."'";

	$authresult=DB_query($authsql, $db);
	$myauthrow=DB_fetch_array($authresult);
	$authlevel=$myauthrow['authlevel'];

	$ordervaluesql="SELECT sum(unitprice*quantityord) as ordervalue
			FROM purchorderdetails
			WHERE orderno='".$myrow['orderno'] . "'";

	$ordervalueresult=DB_query($ordervaluesql, $db);
	$myordervaluerow=DB_fetch_array($ordervalueresult);
	$ordervalue=$myordervaluerow['ordervalue'];

	if ($authlevel>=$ordervalue) {
		echo '<tr>';
		echo '<td>'.$myrow['orderno'].'</td>';
		echo '<td>'.$myrow['suppname'].'</td>';
		echo '<td>'.ConvertSQLDate($myrow['orddate']).'</td>';
		echo '<td><a href="mailto:'.$myrow['email'].'">'.$myrow['realname'].'</a></td>';
		echo '<td>'.ConvertSQLDate($myrow['deliverydate']).'</td>';
		echo '<td><select name=status'.$myrow['orderno'].'>';
		echo '<option selected="True" value="Pending">'._('Pending').'</option>';
		echo '<option value="Authorised">'._('Authorised').'</option>';
		echo '<option value="Rejected">'._('Rejected').'</option>';
		echo '<option value="Cancelled">'._('Cancelled').'</option>';
		echo '</select></td>';
		echo '</tr>';
		echo '<input type="hidden" name="comment" value="'.htmlentities($myrow['stat_comment']).'" />';
		$linesql="SELECT purchorderdetails.quantityord,
						purchorderdetails.unitprice,
						stockmaster.description,
						stockmaster.decimalplaces
				FROM purchorderdetails
				LEFT JOIN stockmaster
				ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE orderno='".$myrow['orderno'] . "'";
		$lineresult=DB_query($linesql, $db);

		echo '<tr><td></td><td colspan="5" align="left"><table class="selection" align="left">';
		echo '<th>'._('Product').'</th>';
		echo '<th>'._('Quantity Ordered').'</th>';
		echo '<th>'._('Currency').'</th>';
		echo '<th>'._('Price').'</th>';
		echo '<th>'._('Line Total').'</th>';
		echo '</tr>';

		while ($linerow=DB_fetch_array($lineresult)) {
			echo '<tr>';
			echo '<td>'.$linerow['description'].'</td>';
			echo '<td class="number">'.locale_number_format($linerow['quantityord'],$linerow['decimalplaces']).'</td>';
			echo '<td>'.$myrow['currcode'].'</td>';
			echo '<td class="number">'.locale_money_format($linerow['unitprice'],$myrow['currcode']).'</td>';
			echo '<td class="number">'.locale_money_format($linerow['unitprice']*$linerow['quantityord'],$myrow['currcode']).'</td>';
			echo '</tr>';
		} // end while order line detail
		echo '</table></td></tr>';
	}
} //end while header loop
echo '</table>';
echo '<br /><div class="centre"><button type="submit" name="updateall" value="" />' . _('Update'). '</button></div><br /></form>';

include('includes/footer.inc');
?>