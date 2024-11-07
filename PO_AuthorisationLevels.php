<?php


include('includes/session.php');
$Title = _('Purchase Order Authorisation Maintenance');
$ViewTopic = '';
$BookMark = 'PO_AuthorisationLevels';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/group_add.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.


/*Note: If CanCreate==0 then this means the user can create orders
 *     Also if OffHold==0 then the user can release purchase invocies
 *     This logic confused me a bit to start with
 */


if (isset($_POST['Submit'])) {
	if (isset($_POST['CanCreate']) AND $_POST['CanCreate']=='on') {
		$CanCreate=0;
	} else {
		$CanCreate=1;
	}
	if (isset($_POST['OffHold']) AND $_POST['OffHold']=='on') {
		$OffHold=0;
	} else {
		$OffHold=1;
	}
	if ($_POST['AuthLevel']=='') {
		$_POST['AuthLevel']=0;
	}
	$sql="SELECT COUNT(*)
		FROM purchorderauth
		WHERE userid='" . $_POST['UserID'] . "'
		AND currabrev='" . $_POST['CurrCode'] . "'";
	$result=DB_query($sql);
	$myrow=DB_fetch_array($result);
	if ($myrow[0]==0) {
		$sql="INSERT INTO purchorderauth ( userid,
						currabrev,
						cancreate,
						offhold,
						authlevel)
					VALUES( '".$_POST['UserID']."',
						'".$_POST['CurrCode']."',
						'".$CanCreate."',
						'".$OffHold."',
						'" . filter_number_format($_POST['AuthLevel'])."')";
	$ErrMsg = _('The authentication details cannot be inserted because');
	$Result=DB_query($sql,$ErrMsg);
	} else {
		prnMsg(_('There already exists an entry for this user/currency combination'), 'error');
		echo '<br />';
	}
}

if (isset($_POST['Update'])) {
	if (isset($_POST['CanCreate']) AND $_POST['CanCreate']=='on') {
		$CanCreate=0;
	} else {
		$CanCreate=1;
	}
	if (isset($_POST['OffHold']) AND $_POST['OffHold']=='on') {
		$OffHold=0;
	} else {
		$OffHold=1;
	}
	$sql="UPDATE purchorderauth SET
			cancreate='".$CanCreate."',
			offhold='".$OffHold."',
			authlevel='".filter_number_format($_POST['AuthLevel'])."'
			WHERE userid='".$_POST['UserID']."'
			AND currabrev='".$_POST['CurrCode']."'";

	$ErrMsg = _('The authentication details cannot be updated because');
	$Result=DB_query($sql,$ErrMsg);
}

if (isset($_GET['Delete'])) {
	$sql="DELETE FROM purchorderauth
		WHERE userid='".$_GET['UserID']."'
		AND currabrev='".$_GET['Currency']."'";

	$ErrMsg = _('The authentication details cannot be deleted because');
	$Result=DB_query($sql,$ErrMsg);
}

if (isset($_GET['Edit'])) {
	$sql="SELECT cancreate,
				offhold,
				authlevel
			FROM purchorderauth
			WHERE userid='".$_GET['UserID']."'
			AND currabrev='".$_GET['Currency']."'";
	$ErrMsg = _('The authentication details cannot be retrieved because');
	$result=DB_query($sql,$ErrMsg);
	$myrow=DB_fetch_array($result);
	$UserID=$_GET['UserID'];
	$Currency=$_GET['Currency'];
	$CanCreate=$myrow['cancreate'];
	$OffHold=$myrow['offhold'];
	$AuthLevel=$myrow['authlevel'];
}

$sql="SELECT purchorderauth.userid,
			www_users.realname,
			currencies.currabrev,
			currencies.currency,
			currencies.decimalplaces,
			purchorderauth.cancreate,
			purchorderauth.offhold,
			purchorderauth.authlevel
	FROM purchorderauth INNER JOIN www_users
		ON purchorderauth.userid=www_users.userid
	INNER JOIN currencies
		ON purchorderauth.currabrev=currencies.currabrev";

$ErrMsg = _('The authentication details cannot be retrieved because');
$Result=DB_query($sql,$ErrMsg);

echo '<table class="selection">
     <tr>
		<th>' . _('User ID') . '</th>
		<th>' . _('User Name') . '</th>
		<th>' . _('Currency') . '</th>
		<th>' . _('Create Order') . '</th>
		<th>' . _('Can Release') . '<br />' .  _('Invoices') . '</th>
		<th>' . _('Authority Level') . '</th>
		<th colspan="2">&nbsp;</th>
    </tr>';

while ($myrow=DB_fetch_array($Result)) {
	if ($myrow['cancreate']==0) {
		$DisplayCanCreate=_('Yes');
	} else {
		$DisplayCanCreate=_('No');
	}
	if ($myrow['offhold']==0) {
		$DisplayOffHold=_('Yes');
	} else {
		$DisplayOffHold=_('No');
	}
	echo '<tr>
			<td>' . $myrow['userid'] . '</td>
			<td>' . $myrow['realname'] . '</td>
			<td>', _($myrow['currency']), '</td>
			<td>' . $DisplayCanCreate . '</td>
			<td>' . $DisplayOffHold . '</td>
			<td class="number">' . locale_number_format($myrow['authlevel'],$myrow['decimalplaces']) . '</td>
			<td><a href="'.$RootPath.'/PO_AuthorisationLevels.php?Edit=Yes&amp;UserID=' . $myrow['userid'] .
	'&amp;Currency='.$myrow['currabrev'].'">' . _('Edit') . '</a></td>
			<td><a href="'.$RootPath.'/PO_AuthorisationLevels.php?Delete=Yes&amp;UserID=' . $myrow['userid'] .
	'&amp;Currency='.$myrow['currabrev'].'" onclick="return confirm(\'' . _('Are you sure you wish to delete this authorisation level?') . '\');">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table><br /><br />';

if (!isset($_GET['Edit'])) {
	$UserID=$_SESSION['UserID'];
	$Currency=$_SESSION['CompanyRecord']['currencydefault'];
	$CanCreate=0;
	$OffHold=0;
	$AuthLevel=0;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="form1">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<legend>', _('Set Authorisation Levels'), '</legend>';

if (isset($_GET['Edit'])) {
	echo '<field>
			<label for="UserID">' . _('User ID') . '</label>
			<fieldtext>' . $_GET['UserID'] . '</fieldtext>
		</field>';
	echo '<input type="hidden" name="UserID" value="'.$_GET['UserID'].'" />';
} else {
	echo '<field>
			<label for="UserID">' . _('User ID') . '</label>
			<select name="UserID">';
	$usersql="SELECT userid FROM www_users";
	$userresult=DB_query($usersql);
	while ($myrow=DB_fetch_array($userresult)) {
		if ($myrow['userid']==$UserID) {
			echo '<option selected="selected" value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
		} else {
			echo '<option value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
		}
	}
	echo '</select>
		</field>';
}

if (isset($_GET['Edit'])) {
	$sql="SELECT cancreate,
				offhold,
				authlevel,
				currency,
				decimalplaces
			FROM purchorderauth INNER JOIN currencies
			ON purchorderauth.currabrev=currencies.currabrev
			WHERE userid='".$_GET['UserID']."'
			AND purchorderauth.currabrev='".$_GET['Currency']."'";
	$ErrMsg = _('The authentication details cannot be retrieved because');
	$result=DB_query($sql,$ErrMsg);
	$myrow=DB_fetch_array($result);
	$UserID=$_GET['UserID'];
	$Currency=$_GET['Currency'];
	$CanCreate=$myrow['cancreate'];
	$OffHold=$myrow['offhold'];
	$AuthLevel=$myrow['authlevel'];
	$CurrDecimalPlaces=$myrow['decimalplaces'];

	echo '<field>
			<label for="CurrCode">' . _('Currency') . '</label>
			<fieldtext>' . $myrow['currency'] . '</fieldtext>
		</field>';
	echo '<input type="hidden" name="CurrCode" value="'.$Currency.'" />';
} else {
	echo '<field>
			<label for="CurrCode">' . _('Currency') . '</label>
			<select name="CurrCode">';
	$currencysql="SELECT currabrev,currency,decimalplaces FROM currencies";
	$currencyresult=DB_query($currencysql);
	while ($myrow=DB_fetch_array($currencyresult)) {
		if ($myrow['currabrev']==$Currency) {
			echo '<option selected="selected" value="'.$myrow['currabrev'].'">' . $myrow['currency'] . '</option>';
		} else {
			echo '<option value="'.$myrow['currabrev'].'">' . $myrow['currency'] . '</option>';
		}
	}
	$CurrDecimalPlaces=$myrow['decimalplaces'];
	echo '</select>
		</field>';
}

echo '<field>
		<label for="CanCreate">' . _('User can create orders') . '</label>';
if ($CanCreate==1) {
	echo '<input type="checkbox" name="CanCreate" />
		</field>';
} else {
	echo '<input type="checkbox" checked="checked" name="CanCreate" />
		</field>';
}

echo '<field>
		<label for="OffHold">' . _('User can release invoices') . '</label>';
if ($OffHold==1) {
	echo '<input type="checkbox" name="OffHold" />
		</field>';
} else {
	echo '<input type="checkbox" checked="checked" name="OffHold" />
		</field>';
}

echo '<field>
		<label for="AuthLevel">' . _('User can authorise orders up to :') . '</label>
		<input type="text" name="AuthLevel" size="11" class="integer" title="" value="'  . locale_number_format($AuthLevel,$CurrDecimalPlaces) . '" />
		<fieldhelp>' . _('Enter the amount that this user is premitted to authorise purchase orders up to') . '</fieldhelp>
	</field>
	</fieldset>';

if (isset($_GET['Edit'])) {
	echo '<div class="centre">
			<input type="submit" name="Update" value="'._('Update Information').'" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="Submit" value="'._('Enter Information').'" />
		</div>';
}
echo '</div>
        </form>';
include('includes/footer.php');
?>
