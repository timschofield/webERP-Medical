<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Bacteriology Laboratory');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Bacteriology Laboratory'), '" /> ', _('Bacteriology Laboratory'), '
	</p>';

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

echo '<fieldset style="text-align:center">';

echo '<fieldset class="MenuList">
		<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" data-title="', _('Inquiries and Reports'), '" />', _('Laboratory Inquiries'), '</legend>
		<ul>
			<li class="MenuItem">
				<a href="', $RootPath, '/MedPendingBacteriologyTests.php">', _('Pending Bacteriology Tests'), '</a>
			</li>
		</ul>
	</fieldset>';

echo '<fieldset class="MenuList">
		<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" data-title="', _('Laboratory Transactions'), '" />', _('Laboratory Transactions'), '</legend>
		<ul>
			<li class="MenuItem">
				<a href="', $RootPath, '/MedRequestBacteriologyTest.php?New=Yes">', _('Request a Bacteriology Test'), '</a>
			</li>
		</ul>
	</fieldset>';

echo '<fieldset class="MenuList">
		<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" data-title="', _('Laboratory Maintenance'), '" />', _('Laboratory Maintenance'), '</legend>
		<ul>
			<li class="MenuItem">
				<a href="', $RootPath, '/MedMaintainBacteriologyTests.php">', _('Maintain Bacteriology Tests'), '</a>
			</li>
		</ul>
	</fieldset>';

echo '</fieldset>';

include ('includes/footer.php');
?>