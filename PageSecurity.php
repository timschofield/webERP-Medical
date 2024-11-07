<?php

include ('includes/session.php');
$Title = _('Page Security Levels');
$ViewTopic = 'SecuritySchema';
$BookMark = 'PageSecurity';
include ('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/security.png" title="' . _('Page Security Levels') . '" alt="" />' . ' ' . $Title . '</p><br />';

if ($AllowDemoMode) {
	prnMsg(_('The the system is in demo mode and the security model administration is disabled'), 'warn');
	exit;
}

if (isset($_POST['Update'])) {
	foreach ($_POST as $ScriptName => $PageSecurityValue) {
		if ($ScriptName != 'Update' and $ScriptName != 'FormID') {
			$ScriptName = mb_substr($ScriptName, 0, mb_strlen($ScriptName) - 4) . '.php';
			$SQL = "UPDATE scripts SET pagesecurity='" . $PageSecurityValue . "' WHERE script='" . $ScriptName . "'";
			$UpdateResult = DB_query($SQL, _('Could not update the page security value for the script because'));
		}
	}
}

$SQL = "SELECT script,
			pagesecurity,
			description
		FROM scripts";

$Result = DB_query($SQL);

echo '<form method="post" id="PageSecurity" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', _('Assign Security Levels to Scripts'), '</legend>';

$TokenSql = "SELECT tokenid,
					tokenname
			FROM securitytokens
			ORDER BY tokenname";
$TokenResult = DB_query($TokenSql);

while ($MyRow = DB_fetch_array($Result)) {
	echo '<field>
			<label for="' . $MyRow['script'] . '">' . $MyRow['script'] . '</label>
			<select name="' . $MyRow['script'] . '">';

	while ($myTokenRow = DB_fetch_array($TokenResult)) {
		if ($myTokenRow['tokenid'] == $MyRow['pagesecurity']) {
			echo '<option selected="selected" value="' . $myTokenRow['tokenid'] . '">' . $myTokenRow['tokenname'] . '</option>';
		} else {
			echo '<option value="' . $myTokenRow['tokenid'] . '">' . $myTokenRow['tokenname'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	DB_data_seek($TokenResult, 0);
}

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Update" value="' . _('Update Security Levels') . '" />
	</div>
	<br />
    </div>
	</form>';

include ('includes/footer.php');
?>