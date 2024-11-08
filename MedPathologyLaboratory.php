<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Pathology Laboratory');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Pathology Laboratory'), '" /> ', _('Pathology Laboratory'), '
	</p>';

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

echo '<fieldset style="text-align:center">';

echo '<fieldset class="MenuList">
		<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" data-title="', _('Inquiries and Reports'), '" />', _('Lobaoratory Inquiries'), '</legend>
		<ul>
			<li class="MenuItem">
				<a href="', $RootPath, '/KCMCPendingPathologyTests.php">', _('Pending Pathology Tests'), '</a>
			</li>
		</ul>
	</fieldset>';

echo '<fieldset class="MenuList">
		<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" data-title="', _('Lobaoratory Transactions'), '" />', _('Lobaoratory Transactions'), '</legend>
		<ul>
			<li class="MenuItem">
				<a href="', $RootPath, '/KCMCRequestPathologyTest.php?New=Yes">', _('Request a Pathology Test'), '</a>
			</li>
		</ul>
	</fieldset>';

echo '<fieldset class="MenuList">
		<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" data-title="', _('Lobaoratory Maintenance'), '" />', _('Lobaoratory Maintenance'), '</legend>
		<ul>
		</ul>
	</fieldset>';

echo '</fieldset>';

include ('includes/footer.php');
?>