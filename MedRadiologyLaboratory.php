<?php
/* Selection of patient - from where all patient related maintenance, transactions and inquiries start */

include ('includes/session.php');
$Title = _('Radiology Department');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/radiology.png" title="', _('Radiology Department'), '" /> ', _('Radiology Department'), '
	</p>';

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

echo '<fieldset style="text-align:center">';

echo '<fieldset class="MenuList">
		<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" data-title="', _('Radiology Inquiries'), '" />', _('Radiology Inquiries'), '</legend>
		<ul>
			<li class="MenuItem">
				<a href="', $RootPath, '/MedPendingRadiologyTests.php">', _('Pending Radiology Tests'), '</a>
			</li>
		</ul>
	</fieldset>';

echo '<fieldset class="MenuList">
		<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" data-title="', _('Radiology Transactions'), '" />', _('Radiology Transactions'), '</legend>
		<ul>
			<li class="MenuItem">
				<a href="', $RootPath, '/MedRequestRadiologyTest.php?New=Yes">', _('Request a Radiology Test'), '</a>
			</li>
		</ul>
	</fieldset>';

echo '<fieldset class="MenuList">
		<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" data-title="', _('Radiology Maintenance'), '" />', _('Radiology Maintenance'), '</legend>
		<ul>
			<li class="MenuItem">
				<a href="', $RootPath, '/MedMaintainRadiologyTests.php">', _('Maintain Radiology Tests'), '</a>
			</li>
		</ul>
	</fieldset>';

echo '</fieldset>';

include ('includes/footer.php');
?>