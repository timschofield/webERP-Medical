<?php
include ('includes/session.php');
$Title = _('Test parameters');
include ('includes/header.php');

if (isset($_POST['SelectedTest'])) {
	$SelectedTest = $_POST['SelectedTest'];
} elseif (isset($_GET['SelectedTest'])) {
	$SelectedTest = $_GET['SelectedTest'];
} else {
	$SelectedTest = '';
}

if (isset($_POST['SelectedGroup'])) {
	$SelectedGroup = $_POST['SelectedGroup'];
} elseif (isset($_GET['SelectedGroup'])) {
	$SelectedGroup = $_GET['SelectedGroup'];
} else {
	$SelectedGroup = 315;
}

if (isset($_POST['save'])) {
	$SQL = "UPDATE care_test_param SET name='" . $_POST['Name'] . "',
										msr_unit='" . $_POST['MsrUnit'] . "',
										method='" . $_POST['Method'] . "',
										status='" . $_POST['Status'] . "',
										id='" . $_POST['Group'] . "',
										median='" . $_POST['Median'] . "',
										hi_bound='" . $_POST['HiBound'] . "',
										lo_bound='" . $_POST['LoBound'] . "',
										hi_critical='" . $_POST['HiCritical'] . "',
										lo_critical='" . $_POST['LoCritical'] . "',
										hi_toxic='" . $_POST['HiToxic'] . "',
										lo_toxic='" . $_POST['LoToxic'] . "',
										median_f='" . $_POST['MedianF'] . "',
										hi_bound_f='" . $_POST['HiBoundF'] . "',
										lo_bound_f='" . $_POST['LoBoundF'] . "',
										hi_critical_f='" . $_POST['HiCriticalF'] . "',
										lo_critical_f='" . $_POST['LoCriticalF'] . "',
										hi_toxic_f='" . $_POST['HiToxicF'] . "',
										lo_toxic_f='" . $_POST['LoToxicF'] . "',
										median_n='" . $_POST['MedianN'] . "',
										hi_bound_n='" . $_POST['HiBoundN'] . "',
										lo_bound_n='" . $_POST['LoBoundN'] . "',
										hi_critical_n='" . $_POST['HiCriticalN'] . "',
										lo_critical_n='" . $_POST['LoCriticalN'] . "',
										hi_toxic_n='" . $_POST['HiToxicN'] . "',
										lo_toxic_n='" . $_POST['LoToxicN'] . "',
										median_y='" . $_POST['MedianY'] . "',
										hi_bound_y='" . $_POST['HiBoundY'] . "',
										lo_bound_y='" . $_POST['LoBoundY'] . "',
										hi_critical_y='" . $_POST['HiCriticalY'] . "',
										lo_critical_y='" . $_POST['LoCriticalY'] . "',
										hi_toxic_y='" . $_POST['HiToxicY'] . "',
										lo_toxic_y='" . $_POST['LoToxicY'] . "',
										median_c='" . $_POST['MedianC'] . "',
										hi_bound_c='" . $_POST['HiBoundC'] . "',
										lo_bound_c='" . $_POST['LoBoundC'] . "',
										hi_critical_c='" . $_POST['HiCriticalC'] . "',
										lo_critical_c='" . $_POST['LoCriticalC'] . "',
										hi_toxic_c='" . $_POST['HiToxicC'] . "',
										lo_toxic_c='" . $_POST['LoToxicC'] . "',
										modify_id='" . $_SESSION['UserID'] . "'
									WHERE nr='" . $SelectedTest . "'";
	$Result = DB_query($SQL);
	if (DB_error_no() == 0) {
		prnMsg(_('The Test parameter details have been successfully updated'), 'success');
	} else {
		prnMsg(_('There was a problem updating the test parameter details'), 'error');
	}
}

if (!isset($SelectedTest) or ($SelectedTest == '')) {
	$GroupSQL = "SELECT group_id,
						name,
						id,
						sort_nr
					FROM care_test_param
					WHERE nr = '" . $SelectedGroup . "'";
	$GroupResult = DB_query($GroupSQL);
	$GroupRow = DB_fetch_array($GroupResult);

	echo '<p class="page_title_text" >
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', $Title, '" alt="', $Title, '" />', ' ', _('Test parameters for group'), ' - ', $GroupRow['name'], '
		</p>';

	echo '<table>
			<thead>
				<tr>
					<th colspan="10">', $GroupRow['name'], '</th>
				</tr>
				<tr>
					<th class="SortedColumn">', _('Parameter'), '</th>
					<th class="SortedColumn">', _('Measurement Unit'), '</th>
					<th>', _('Median'), '</th>
					<th>', _('Lower Boundary'), '</th>
					<th>', _('Upper Boundary'), '</th>
					<th>', _('Lower Critical'), '</th>
					<th>', _('Upper Critical'), '</th>
					<th>', _('Lower Toxic'), '</th>
					<th>', _('Upper Toxic'), '</th>
					<th></th>
				</tr>
			</thead>';

	$TestsSQL = "SELECT nr,
						name,
						id,
						sort_nr,
						msr_unit,
						median,
						lo_bound,
						hi_bound,
						lo_critical,
						hi_critical,
						lo_toxic,
						hi_toxic
					FROM care_test_param
					WHERE group_id = '" . $GroupRow['id'] . "'
					ORDER BY sort_nr ASC";
	$TestsResult = DB_query($TestsSQL);

	echo '<tbody>';
	while ($TestsRow = DB_fetch_array($TestsResult)) {
		echo '<tr class="striped_row">
				<td>', $TestsRow['name'], '</td>
				<td>', $TestsRow['msr_unit'], '</td>
				<td>', $TestsRow['median'], '</td>
				<td>', $TestsRow['lo_bound'], '</td>
				<td>', $TestsRow['hi_bound'], '</td>
				<td>', $TestsRow['lo_critical'], '</td>
				<td>', $TestsRow['hi_critical'], '</td>
				<td>', $TestsRow['lo_toxic'], '</td>
				<td>', $TestsRow['hi_toxic'], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedTest=', urlencode($TestsRow['nr']), '">', _('Edit Parameter'), '</a></td>
			</tr>';
	}
	echo '</tbody>';

	echo '</table>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post" id="TestParameterGroups">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Select parameter group'), '</legend>
			<field>
				<label for="SelectedGroup">', _('Parameter Group'), '</label>
				<select name="SelectedGroup">';

	$SQL = "SELECT nr,
					name,
					id,
					sort_nr
				FROM care_test_param
				WHERE group_id='-1'
				ORDER BY sort_nr";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		if ($SelectedGroup == $MyRow['nr']) {
			echo '<option value="', $MyRow['nr'], '" selected="selected">', $MyRow['name'], '</option>';
		} else {
			echo '<option value="', $MyRow['nr'], '">', $MyRow['name'], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="', _('Select'), '" />
		</div>';

	echo '</form>';
}

if (isset($SelectedTest) and $SelectedTest !== '') {

	$SQL = "SELECT name,
					msr_unit,
					method,
					status,
					id,
					median,
					hi_bound,
					lo_bound,
					hi_critical,
					lo_critical,
					hi_toxic,
					lo_toxic,
					median_f,
					hi_bound_f,
					lo_bound_f,
					hi_critical_f,
					lo_critical_f,
					hi_toxic_f,
					lo_toxic_f,
					median_n,
					hi_bound_n,
					lo_bound_n,
					hi_critical_n,
					lo_critical_n,
					hi_toxic_n,
					lo_toxic_n,
					median_y,
					hi_bound_y,
					lo_bound_y,
					hi_critical_y,
					lo_critical_y,
					hi_toxic_y,
					lo_toxic_y,
					median_c,
					hi_bound_c,
					lo_bound_c,
					hi_critical_c,
					lo_critical_c,
					hi_toxic_c,
					lo_toxic_c
				FROM care_test_param
				WHERE nr='" . $SelectedTest . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', $Title, '" alt="', $Title, '" />', ' ', _('Edit Parameter'), '
		</p>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="SelectedTest" value="', $SelectedTest, '" />';

	echo '<div id="TabbedNotebook" class="centre">
			<button class="tablink" onclick="openPage(\'Paramater\', this);return false;" id="defaultOpen">', _('Paramater'), '</button>
			<button class="tablink" onclick="openPage(\'Males\', this);return false;">', _('Males'), '</button>
			<button class="tablink" onclick="openPage(\'Females\', this);return false;">', _('Females'), '</button>
			<button class="tablink" onclick="openPage(\'0Month\', this);return false;">', _('0-1 Month'), '</button>
			<button class="tablink" onclick="openPage(\'1Month\', this);return false;">', _('1-12 Month'), '</button>
			<button class="tablink" onclick="openPage(\'1Year\', this);return false;">', _('1-14 Year'), '</button>
			';

	echo '<div id="Paramater" class="tabcontent">
			<label for="Name">', _('Paramater'), '</label>
			<input type="text" name="Name" size=15 maxlength=15 value="', $MyRow['name'], '"><br />
			<label for="MsrUnit">', _('Unit of measurement'), '</label>
			<input type="text" name="MsrUnit" size=15 maxlength=15 value="', $MyRow['msr_unit'], '"><br />
			<label for="Method">', _('Method'), '</label>
			<input type="text" name="Method" size=15 maxlength=15 value="', $MyRow['method'], '"><br />
			<label for="Status">', _('Status'), '</label>';
	echo '<select name="Status">';
	if ($MyRow['status'] == 'Show') {
		echo '<option value="Show" selected="selected">', _('Show'), '</option>';
		echo '<option value="Hide">', _('Hide'), '</option>';
		echo '<option value="Delete">', _('Delete'), '</option>';
	}
	if ($MyRow['status'] == 'Hide') {
		echo '<option value="Show">', _('Show'), '</option>';
		echo '<option value="Hide" selected="selected">', _('Hide'), '</option>';
		echo '<option value="Delete">', _('Delete'), '</option>';
	}
	if ($MyRow['status'] == 'Delete') {
		echo '<option value="Show">', _('Show'), '</option>';
		echo '<option value="Hide">', _('Hide'), '</option>';
		echo '<option value="Delete" selected="selected">', _('Delete'), '</option>';
	}
	echo '</select><br />';

	$GroupSQL = "SELECT id, name FROM care_test_param WHERE group_id='-1' and status='Show' ORDER BY sort_nr";
	$GroupResult = DB_query($GroupSQL);
	echo '<label for="Group">', _('Group'), '</label>
			<select name="Group">';

	while ($GroupRow = DB_fetch_array($GroupResult)) {
		if ($GroupRow['id'] == $MyRow['id']) {
			echo '<option value="', $GroupRow['id'], '" selected="selected">', $GroupRow['name'], '</option>';
		} else {
			echo '<option value="', $GroupRow['id'], '">', $GroupRow['name'], '</option>';
		}
	}

	echo '</select>';

	echo '</div>';

	echo '<div id="Males" class="tabcontent">
			<label for="Median">', _('Median'), '</label>
			<input type="text" name="Median" class="number" size=15 maxlength=15 value="', $MyRow['median'], '"><br />
			<label for="HiBound">', _('Upper boundary'), '</label>
			<input type="text" name="HiBound" class="number" size=15 maxlength=15 value="', $MyRow['hi_bound'], '"><br />
			<label for="LoBound">', _('Lower boundary'), '</label>
			<input type="text" name="LoBound" class="number" size=15 maxlength=15 value="', $MyRow['lo_bound'], '"><br />
			<label for="HiCritical">', _('Upper critical'), '</label>
			<input type="text" name="HiCritical" class="number" size=15 maxlength=15 value="', $MyRow['hi_critical'], '"><br />
			<label for="LoCritical">', _('Lower critical'), '</label>
			<input type="text" name="LoCritical" class="number" size=15 maxlength=15 value="', $MyRow['lo_critical'], '"><br />
			<label for="HiToxic">', _('Upper toxic'), '</label>
			<input type="text" name="HiToxic" class="number" size=15 maxlength=15 value="', $MyRow['hi_toxic'], '"><br />
			<label for="LoToxic">', _('Lower toxic'), '</label>
			<input type="text" name="LoToxic" class="number" size=15 maxlength=15 value="', $MyRow['lo_toxic'], '"><br />
		</div>';

	echo '<div id="Females" class="tabcontent">
			<label for="MedianF">', _('Median'), '</label>
			<input type="text" name="MedianF" class="number" size=15 maxlength=15 value="', $MyRow['median_f'], '"><br />
			<label for="HiBoundF">', _('Upper boundary'), '</label>
			<input type="text" name="HiBoundF" class="number" size=15 maxlength=15 value="', $MyRow['hi_bound_f'], '"><br />
			<label for="LoBoundF">', _('Lower boundary'), '</label>
			<input type="text" name="LoBoundF" class="number" size=15 maxlength=15 value="', $MyRow['lo_bound_f'], '"><br />
			<label for="HiCriticalF">', _('Upper critical'), '</label>
			<input type="text" name="HiCriticalF" class="number" size=15 maxlength=15 value="', $MyRow['hi_critical_f'], '"><br />
			<label for="LoCriticalF">', _('Lower critical'), '</label>
			<input type="text" name="LoCriticalF" class="number" size=15 maxlength=15 value="', $MyRow['lo_critical_f'], '"><br />
			<label for="HiToxicF">', _('Upper toxic'), '</label>
			<input type="text" name="HiToxicF" class="number" size=15 maxlength=15 value="', $MyRow['hi_toxic_f'], '"><br />
			<label for="LoToxicF">', _('Lower toxic'), '</label>
			<input type="text" name="LoToxicF" class="number" size=15 maxlength=15 value="', $MyRow['lo_toxic_f'], '"><br />
		</div>';

	echo '<div id="0Month" class="tabcontent">
			<label for="MedianN">', _('Median'), '</label>
			<input type="text" name="MedianN" class="number" size=15 maxlength=15 value="', $MyRow['median_n'], '"><br />
			<label for="HiBoundN">', _('Upper boundary'), '</label>
			<input type="text" name="HiBoundN" class="number" size=15 maxlength=15 value="', $MyRow['hi_bound_n'], '"><br />
			<label for="LoBoundN">', _('Lower boundary'), '</label>
			<input type="text" name="LoBoundN" class="number" size=15 maxlength=15 value="', $MyRow['lo_bound_n'], '"><br />
			<label for="HiCriticalN">', _('Upper critical'), '</label>
			<input type="text" name="HiCriticalN" class="number" size=15 maxlength=15 value="', $MyRow['hi_critical_n'], '"><br />
			<label for="LoCriticalN">', _('Lower critical'), '</label>
			<input type="text" name="LoCriticalN" class="number" size=15 maxlength=15 value="', $MyRow['lo_critical_n'], '"><br />
			<label for="HiToxicN">', _('Upper toxic'), '</label>
			<input type="text" name="HiToxicN" class="number" size=15 maxlength=15 value="', $MyRow['hi_toxic_n'], '"><br />
			<label for="LoToxicN">', _('Lower toxic'), '</label>
			<input type="text" name="LoToxicN" class="number" size=15 maxlength=15 value="', $MyRow['lo_toxic_n'], '"><br />
		</div>';

	echo '<div id="1Month" class="tabcontent">
			<label for="MedianY">', _('Median'), '</label>
			<input type="text" name="MedianY" class="number" size=15 maxlength=15 value="', $MyRow['median_y'], '"><br />
			<label for="HiBoundY">', _('Upper boundary'), '</label>
			<input type="text" name="HiBoundY" class="number" size=15 maxlength=15 value="', $MyRow['hi_bound_y'], '"><br />
			<label for="LoBoundY">', _('Lower boundary'), '</label>
			<input type="text" name="LoBoundY" class="number" size=15 maxlength=15 value="', $MyRow['lo_bound_y'], '"><br />
			<label for="HiCriticalY">', _('Upper critical'), '</label>
			<input type="text" name="HiCriticalY" class="number" size=15 maxlength=15 value="', $MyRow['hi_critical_y'], '"><br />
			<label for="LoCriticalY">', _('Lower critical'), '</label>
			<input type="text" name="LoCriticalY" class="number" size=15 maxlength=15 value="', $MyRow['lo_critical_y'], '"><br />
			<label for="HiToxicY">', _('Upper toxic'), '</label>
			<input type="text" name="HiToxicY" class="number" size=15 maxlength=15 value="', $MyRow['hi_toxic_y'], '"><br />
			<label for="LoToxicY">', _('Lower toxic'), '</label>
			<input type="text" name="LoToxicY" class="number" size=15 maxlength=15 value="', $MyRow['lo_toxic_y'], '"><br />
		</div>';

	echo '<div id="1Year" class="tabcontent">
			<label for="MedianC">', _('Median'), '</label>
			<input type="text" name="MedianC" class="number" size=15 maxlength=15 value="', $MyRow['median_c'], '"><br />
			<label for="HiBoundC">', _('Upper boundary'), '</label>
			<input type="text" name="HiBoundC" class="number" size=15 maxlength=15 value="', $MyRow['hi_bound_c'], '"><br />
			<label for="LoBoundC">', _('Lower boundary'), '</label>
			<input type="text" name="LoBoundC" class="number" size=15 maxlength=15 value="', $MyRow['lo_bound_c'], '"><br />
			<label for="HiCriticalC">', _('Upper critical'), '</label>
			<input type="text" name="HiCriticalC" class="number" size=15 maxlength=15 value="', $MyRow['hi_critical_c'], '"><br />
			<label for="LoCriticalC">', _('Lower critical'), '</label>
			<input type="text" name="LoCriticalC" class="number" size=15 maxlength=15 value="', $MyRow['lo_critical_c'], '"><br />
			<label for="HiToxicC">', _('Upper toxic'), '</label>
			<input type="text" name="HiToxicC" class="number" size=15 maxlength=15 value="', $MyRow['hi_toxic_c'], '"><br />
			<label for="LoToxicC">', _('Lower toxic'), '</label>
			<input type="text" name="LoToxicC" class="number" size=15 maxlength=15 value="', $MyRow['lo_toxic_c'], '"><br />
		</div>';

	echo '</div>';

	echo '<div class="centre">
			<input type="submit" name="save" value="', _('Save Data'), '" />
		</div>';

	echo '</form>';

	echo '<script async type="text/javascript" src = "', $PathPrefix, $RootPath, '/javascripts/tabs.js"></script>';
}

include ('includes/footer.php');
?>