<?php

/* $Id$ */

include('includes/session.inc');

$title = _('Page Security Levels');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/security.png" title="' . _('Page Security Levels') . '" alt="" />' . ' ' . $title.'</p><br />';

if (isset($_POST['Update'])) {
	foreach ($_POST as $ScriptName => $PageSecurityValue) {
		if ($ScriptName!='Update' and $ScriptName!='FormID') {
			$ScriptName=mb_substr($ScriptName, 0, mb_strlen($ScriptName)-4).'.php';
			$sql="UPDATE pagesecurity SET security='".$PageSecurityValue."' WHERE script='".$ScriptName."'";
			$UpdateResult=DB_query($sql, $db,_('Could not update the page security value for the script because'));
		}
	}
}

$sql="SELECT script,
			security
			FROM pagesecurity";

$result=DB_query($sql, $db);

echo '<br /><form method="post" id="PageSecurity" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';

$TokenSql = "SELECT tokenid,
					tokenname
				FROM securitytokens
				ORDER BY tokenname";
$TokenResult=DB_query($TokenSql, $db);

while ($myrow=DB_fetch_array($result)) {
	echo '<tr><td>'.$myrow['script'].'</td>';
	echo '<td><select name="'.$myrow['script'].'">';
	while ($mytokenrow=DB_fetch_array($TokenResult)) {
		if ($mytokenrow['tokenid']==$myrow['security']) {
			echo '<option selected="True" value="'.$mytokenrow['tokenid'].'">'.$mytokenrow['tokenname'].'</option>';
		} else {
			echo '<option value="'.$mytokenrow['tokenid'].'">'.$mytokenrow['tokenname'].'</option>';
		}
	}
	echo '</select></td></tr>';
	DB_data_seek($TokenResult, 0);
}

echo '</table><br />';

echo '<div class="centre"><button type="submit" name="Update">' . _('Update Security Levels') . '</button></div><br /></form>';

include('includes/footer.inc');
?>