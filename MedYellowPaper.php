<?php
include ('includes/session.php');
include ('includes/HospitalFunctions.php');

if (isset($_GET['Encounter'])) {
	$Encounter = $_GET['Encounter'];
} elseif (isset($_POST['Encounter'])) {
	$Encounter = $_POST['Encounter'];
}

if (isset($_GET['Ward'])) {
	$Location = $_GET['Ward'];
}

if (isset($_GET['Dept'])) {
	$Location = $_GET['Dept'];
}

if (isset($_POST['Location'])) {
	$Location = $_POST['Location'];
}

$PID = GetPIDFRomEncounter($Encounter);
$SQL = "SELECT name_first, name_last FROM care_person WHERE pid='" . $PID . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

$Title = _('Yellow Paper') . ' : PID-' . $PID . ' - ' . $MyRow['name_first'] . ' ' . $MyRow['name_last'];

include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', $Title, '
	</p>';

/* Test whether a yellow page record exists for this encounter */
$SQL = "SELECT nr FROM care_yellow_paper WHERE encounter_nr='" . $Encounter . "'";
$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	$NewRecord = 'Yes';
} else {
	$MyRow = DB_fetch_array($Result);
	$Number = $MyRow['nr'];
}
/*............................................................. */

if (isset($_POST['save'])) {
	if (isset($NewRecord)) {
		$SQL = "INSERT INTO care_yellow_paper (encounter_nr,
												staff_name,
												location_id,
												create_id,
												create_time,
												sunto_anamnestico,
												altezza,
												peso,
												norm,
												stato_presente,
												dati_urine,
												dati_sangue,
												dati_altro,
												diagnosi,
												terapia,
												malattie_ereditarie,
												padre,
												madre,
												fratelli,
												coniuge,
												figli,
												paesi_esteri,
												abitazione,
												lavoro_pregresso,
												lavoro_presente,
												lavoro_attuale,
												ambiente_lavoro,
												gas_lavoro,
												tossiche_lavoro,
												conviventi,
												prematuro,
												eutocico,
												fisiologici_normali,
												fisiologici_tardivi,
												mestruazione,
												gravidanze,
												militare,
												alcolici,
												caffe,
												fumo,
												droghe,
												sete,
												alvo,
												diuresi,
												anamnesi_remota,
												anamnesi_prossima
										) VALUES (
											'" . $Encounter . "',
											'" . $_SESSION['UserID'] . "',
											'" . $Location . "',
											'" . $_SESSION['UserID'] . "',
											NOW(),
											'" . $_POST['Summary'] . "',
											'" . $_POST['Height'] . "',
											'" . $_POST['Weight'] . "',
											'" . $_POST['Age'] . "',
											'" . $_POST['Symptoms'] . "',
											'" . $_POST['Urine'] . "',
											'" . $_POST['Blood'] . "',
											'" . $_POST['Other'] . "',
											'" . $_POST['Diagnosis'] . "',
											'" . $_POST['Therapy'] . "',
											'" . $_POST['Hereditary'] . "',
											'" . $_POST['Father'] . "',
											'" . $_POST['Mother'] . "',
											'" . $_POST['Siblings'] . "',
											'" . $_POST['Partner'] . "',
											'" . $_POST['Children'] . "',
											'" . $_POST['Travel'] . "',
											'" . $_POST['Dwelling'] . "',
											'" . $_POST['PastWork'] . "',
											'" . $_POST['RecentWork'] . "',
											'" . $_POST['CurrentWork'] . "',
											'" . $_POST['WorkPlace'] . "',
											'" . $_POST['GasContact'] . "',
											'" . $_POST['ToxicSubstances'] . "',
											'" . $_POST['ToxicAgents'] . "',
											'" . $_POST['Premature'] . "',
											'" . $_POST['Eutocic'] . "',
											'" . $_POST['NeoNatalDevelopment'] . "',
											'" . $_POST['LateDevelopment'] . "',
											'" . $_POST['Menstruation'] . "',
											'" . $_POST['Pregnancies'] . "',
											'" . $_POST['Military'] . "',
											'" . $_POST['Alcohol'] . "',
											'" . $_POST['Coffee'] . "',
											'" . $_POST['Smoking'] . "',
											'" . $_POST['Medication'] . "',
											'" . $_POST['Thirst'] . "',
											'" . $_POST['Bowel'] . "',
											'" . $_POST['Diuresis'] . "',
											'" . $_POST['Remote'] . "',
											'" . $_POST['Other'] . "'
										)";
		$ErrMsg = _('The yellow paper record could not be added because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	} else {
		$SQL = "UPDATE care_yellow_paper SET staff_name='" . $_SESSION['UserID'] . "',
											location_id='" . $Location . "',
											sunto_anamnestico='" . $_POST['Summary'] . "',
											altezza='" . $_POST['Height'] . "',
											peso='" . $_POST['Weight'] . "',
											norm='" . $_POST['Age'] . "',
											stato_presente='" . $_POST['Symptoms'] . "',
											dati_urine='" . $_POST['Urine'] . "',
											dati_sangue='" . $_POST['Blood'] . "',
											dati_altro='" . $_POST['Other'] . "',
											diagnosi='" . $_POST['Diagnosis'] . "',
											terapia='" . $_POST['Therapy'] . "',
											malattie_ereditarie='" . $_POST['Hereditary'] . "',
											padre='" . $_POST['Father'] . "',
											madre='" . $_POST['Mother'] . "',
											fratelli='" . $_POST['Siblings'] . "',
											coniuge='" . $_POST['Partner'] . "',
											figli='" . $_POST['Children'] . "',
											paesi_esteri='" . $_POST['Travel'] . "',
											abitazione='" . $_POST['Dwelling'] . "',
											lavoro_pregresso='" . $_POST['PastWork'] . "',
											lavoro_presente='" . $_POST['RecentWork'] . "',
											lavoro_attuale='" . $_POST['CurrentWork'] . "',
											ambiente_lavoro='" . $_POST['WorkPlace'] . "',
											gas_lavoro='" . $_POST['GasContact'] . "',
											tossiche_lavoro='" . $_POST['ToxicSubstances'] . "',
											conviventi='" . $_POST['ToxicAgents'] . "',
											prematuro='" . $_POST['Premature'] . "',
											eutocico='" . $_POST['Eutocic'] . "',
											fisiologici_normali='" . $_POST['NeoNatalDevelopment'] . "',
											fisiologici_tardivi='" . $_POST['LateDevelopment'] . "',
											mestruazione='" . $_POST['Menstruation'] . "',
											gravidanze='" . $_POST['Pregnancies'] . "',
											militare='" . $_POST['Military'] . "',
											alcolici='" . $_POST['Alcohol'] . "',
											caffe='" . $_POST['Coffee'] . "',
											fumo='" . $_POST['Smoking'] . "',
											droghe='" . $_POST['Medication'] . "',
											sete='" . $_POST['Thirst'] . "',
											alvo='" . $_POST['Bowel'] . "',
											diuresi='" . $_POST['Diuresis'] . "',
											anamnesi_remota='" . $_POST['Remote'] . "',
											anamnesi_prossima='" . $_POST['Other'] . "',
											modify_id='" . $_SESSION['UserID'] . "',
											modify_time=NOW()
										WHERE encounter_nr='" . $Encounter . "'
											AND nr='" . $Number . "'";
		$ErrMsg = _('The yellow paper record could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	}
	if (DB_error_no($Result) == 0) {
		prnMsg(_('The information was correctly saved'), 'success');
	}
}

if (!isset($NewRecord)) {
	$SQL = "SELECT sunto_anamnestico,
					altezza,
					peso,
					norm,
					stato_presente,
					dati_urine,
					dati_sangue,
					dati_altro,
					diagnosi,
					terapia,
					malattie_ereditarie,
					padre,
					madre,
					fratelli,
					coniuge,
					figli,
					paesi_esteri,
					abitazione,
					lavoro_pregresso,
					lavoro_presente,
					lavoro_attuale,
					ambiente_lavoro,
					gas_lavoro,
					tossiche_lavoro,
					conviventi,
					prematuro,
					eutocico,
					fisiologici_normali,
					fisiologici_tardivi,
					mestruazione,
					gravidanze,
					militare,
					alcolici,
					caffe,
					fumo,
					droghe,
					sete,
					alvo,
					diuresi,
					anamnesi_remota,
					anamnesi_prossima
		FROM care_yellow_paper
		WHERE encounter_nr='" . $Encounter . "'
			AND nr='" . $Number . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['Summary'] = $MyRow['sunto_anamnestico'];
	$_POST['Height'] = $MyRow['altezza'];
	$_POST['Weight'] = $MyRow['peso'];
	$_POST['Age'] = $MyRow['norm'];
	$_POST['Symptoms'] = $MyRow['stato_presente'];
	$_POST['Urine'] = $MyRow['dati_urine'];
	$_POST['Blood'] = $MyRow['dati_sangue'];
	$_POST['Other'] = $MyRow['dati_altro'];
	$_POST['Diagnosis'] = $MyRow['diagnosi'];
	$_POST['Therapy'] = $MyRow['terapia'];
	$_POST['Hereditary'] = $MyRow['malattie_ereditarie'];
	$_POST['Father'] = $MyRow['padre'];
	$_POST['Mother'] = $MyRow['madre'];
	$_POST['Siblings'] = $MyRow['fratelli'];
	$_POST['Partner'] = $MyRow['coniuge'];
	$_POST['Children'] = $MyRow['figli'];
	$_POST['Travel'] = $MyRow['paesi_esteri'];
	$_POST['Dwelling'] = $MyRow['abitazione'];
	$_POST['PastWork'] = $MyRow['lavoro_pregresso'];
	$_POST['RecentWork'] = $MyRow['lavoro_presente'];
	$_POST['CurrentWork'] = $MyRow['lavoro_attuale'];
	$_POST['WorkPlace'] = $MyRow['ambiente_lavoro'];
	$_POST['GasContact'] = $MyRow['gas_lavoro'];
	$_POST['ToxicSubstances'] = $MyRow['tossiche_lavoro'];
	$_POST['ToxicAgents'] = $MyRow['conviventi'];
	$_POST['Premature'] = $MyRow['prematuro'];
	$_POST['Eutocic'] = $MyRow['eutocico'];
	$_POST['NeoNatalDevelopment'] = $MyRow['fisiologici_normali'];
	$_POST['LateDevelopment'] = $MyRow['fisiologici_tardivi'];
	$_POST['Menstruation'] = $MyRow['mestruazione'];
	$_POST['Pregnancies'] = $MyRow['gravidanze'];
	$_POST['Military'] = $MyRow['militare'];
	$_POST['Alcohol'] = $MyRow['alcolici'];
	$_POST['Coffee'] = $MyRow['caffe'];
	$_POST['Smoking'] = $MyRow['fumo'];
	$_POST['Medication'] = $MyRow['droghe'];
	$_POST['Thirst'] = $MyRow['sete'];
	$_POST['Bowel'] = $MyRow['alvo'];
	$_POST['Diuresis'] = $MyRow['diuresi'];
	$_POST['Remote'] = $MyRow['anamnesi_remota'];
	$_POST['Other'] = $MyRow['anamnesi_prossima'];
} else {
	$_POST['Summary'] = '';
	$_POST['Height'] = '';
	$_POST['Weight'] = '';
	$_POST['Age'] = '';
	$_POST['Symptoms'] = '';
	$_POST['Urine'] = '';
	$_POST['Blood'] = '';
	$_POST['Other'] = '';
	$_POST['Diagnosis'] = '';
	$_POST['Therapy'] = '';
	$_POST['Hereditary'] = '';
	$_POST['Father'] = '';
	$_POST['Mother'] = '';
	$_POST['Siblings'] = '';
	$_POST['Partner'] = '';
	$_POST['Children'] = '';
	$_POST['Travel'] = '';
	$_POST['Dwelling'] = '';
	$_POST['PastWork'] = '';
	$_POST['RecentWork'] = '';
	$_POST['CurrentWork'] = '';
	$_POST['WorkPlace'] = '';
	$_POST['GasContact'] = '';
	$_POST['ToxicSubstances'] = '';
	$_POST['ToxicAgents'] = '';
	$_POST['Premature'] = '';
	$_POST['Eutocic'] = '';
	$_POST['NeoNatalDevelopment'] = '';
	$_POST['LateDevelopment'] = '';
	$_POST['Menstruation'] = '';
	$_POST['Pregnancies'] = '';
	$_POST['Military'] = '';
	$_POST['Alcohol'] = 'Medium';
	$_POST['Coffee'] = 'Medium';
	$_POST['Smoking'] = 'Medium';
	$_POST['Medication'] = '';
	$_POST['Thirst'] = 'Normal';
	$_POST['Bowel'] = 'Normal';
	$_POST['Diuresis'] = 'Normal';
	$_POST['Remote'] = '';
	$_POST['Other'] = '';
}

echo '<div id="TabbedNotebook" class="centre">
		<button class="tablink" onclick="openPage(\'Medical\', this)" id="defaultOpen">', _('Medical History'), '</button>
		<button class="tablink" onclick="openPage(\'Family\', this)">', _('Family History'), '</button>
		<button class="tablink" onclick="openPage(\'Socio\', this)">', _('Socio-environmental History'), '</button>
		<button class="tablink" onclick="openPage(\'Physiological\', this)">', _('Physiological History'), '</button>
		<button class="tablink" onclick="openPage(\'Pathological\', this)">', _('Pathological History'), '</button>
		<button class="tablink" onclick="openPage(\'Remote\', this)">', _('Remote Pathological History'), '</button>';

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Encounter=', $Encounter, '&Ward=', $Location, '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<input type="hidden" name="PID" value="', $PID, '" />';

echo '<div id="Medical" class="tabcontent">
		<label for="Summary">', _('Summary'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Summary">', $_POST['Summary'], '</textarea>
		<h3>', _('Physical Details'), '</h3>
		<label for="Height">', _('Height'), '</label>
		<input type="text" class="number" name="Height" value="', $_POST['Height'], '" /><br />
		<label for="Weight">', _('Weight'), '</label>
		<input type="text" class="number" name="Weight" value="', $_POST['Weight'], '" /><br />
		<label for="Age">', _('Age'), '</label>
		<input type="text" class="number" name="Age" value="', $_POST['Age'], '" /><br />
		<h3>', _('Symptoms'), '</h3>
		<label for="Symptoms">', _('Symptoms'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Symptoms">', $_POST['Symptoms'], '</textarea>
		<h3>', _('Laboratory Data'), '</h3>
		<label for="Urine">', _('Urine'), '</label>
		<textarea cols=30 rows=5 wrap="physical" name="Urine">', $_POST['Urine'], '</textarea><br />
		<label for="Blood">', _('Blood'), '</label>
		<textarea cols=30 rows=5 wrap="physical" name="Blood">', $_POST['Blood'], '</textarea><br />
		<label for="Other">', _('Other'), '</label>
		<textarea cols=30 rows=5 wrap="physical" name="Other">', $_POST['Other'], '</textarea><br />
		<h3>', _('Other'), '</h3>
		<label for="Diagnosis">', _('Diagnosis'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Diagnosis">', $_POST['Diagnosis'], '</textarea><br />
		<label for="Therapy">', _('Therapy'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Therapy">', $_POST['Therapy'], '</textarea>
	</div>';

echo '<div id="Family" class="tabcontent">
		<label for="Hereditary">', _('Hereditary diseases'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Hereditary">', $_POST['Hereditary'], '</textarea><br />
		<label for="Father">', _('Father'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Father">', $_POST['Father'], '</textarea><br />
		<label for="Mother">', _('Mother'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Mother">', $_POST['Mother'], '</textarea><br />
		<label for="Partner">', _('Spouse/Partner'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Partner">', $_POST['Partner'], '</textarea><br />
		<label for="Children">', _('Children'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Children">', $_POST['Children'], '</textarea><br />
		<label for="Siblings">', _('Siblings'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Siblings">', $_POST['Siblings'], '</textarea>
	</div>';

echo '<div id="Socio" class="tabcontent">
		<label for="Travel">', _('Recent Travel'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Travel">', $_POST['Travel'], '</textarea><br />
		<label for="Dwelling">', _('Type and standard of dwelling'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Dwelling">', $_POST['Dwelling'], '</textarea><br />
		<label for="PastWork">', _('Past employment'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="PastWork">', $_POST['PastWork'], '</textarea><br />
		<label for="RecentWork">', _('Recent Employment'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="RecentWork">', $_POST['RecentWork'], '</textarea><br />
		<label for="CurrentWork">', _('Current Employment'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="CurrentWork">', $_POST['CurrentWork'], '</textarea><br />
		<label for="WorkPlace">', _('Work place'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="WorkPlace">', $_POST['WorkPlace'], '</textarea><br />
		<label for="GasContact">', _('Contact with gas'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="GasContact">', $_POST['GasContact'], '</textarea><br />
		<label for="ToxicSubstances">', _('Contact with toxic substances'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="ToxicSubstances">', $_POST['ToxicSubstances'], '</textarea><br />
		<label for="ToxicAgents">', _('Other toxic agents'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="ToxicAgents">', $_POST['ToxicAgents'], '</textarea>
	</div>';

echo '<div id="Physiological" class="tabcontent">
		<label for="Premature">', _('Premature Birth'), '</label>
		<select name="Premature">';
if ($_POST['Premature'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select><br />';

echo '<label for="Eutocic">', _('Eutocic birth'), '</label>
		<select name="Eutocic">';
if ($_POST['Eutocic'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select><br />';

echo '<label for="NeoNatalDevelopment">', _('Normal neonatal development'), '</label>
		<select name="NeoNatalDevelopment">';
if ($_POST['NeoNatalDevelopment'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select><br />';

echo '<label for="LateDevelopment">', _('Late development'), '</label>
		<select name="LateDevelopment">';
if ($_POST['LateDevelopment'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select><br />';

echo '<label for="Pregnancies">', _('Previous Pregnancies'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Pregnancies">', $_POST['Pregnancies'], '</textarea><br />
		<label for="Medication">', _('Medication'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Medication">', $_POST['Medication'], '</textarea><br />
		<label for="Menstruation">', _('Menstruation'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Menstruation">', $_POST['Menstruation'], '</textarea><br />
		<label for="Military">', _('Military Service'), '</label>
		<textarea cols=60 rows=5 wrap="physical" name="Military">', $_POST['Military'], '</textarea><br />
		<label for="Alcohol">', _('Alcohol Consumption'), '</label>
		<select name="Alcohol">';

if ($_POST['Alcohol'] == 'Low') {
	echo '<option value="Low" selected="selected">', _('Low or No Consumption'), '</option>';
	echo '<option value="Medium">', _('Medium Consumption'), '</option>';
	echo '<option value="High">', _('Heavy Drinker'), '</option>';
}
if ($_POST['Alcohol'] == 'Medium') {
	echo '<option value="Low">', _('Low or No Consumption'), '</option>';
	echo '<option value="Medium" selected="selected">', _('Medium Consumption'), '</option>';
	echo '<option value="High">', _('Heavy Drinker'), '</option>';
}
if ($_POST['Alcohol'] == 'High') {
	echo '<option value="Low">', _('Low or No Consumption'), '</option>';
	echo '<option value="Medium">', _('Medium Consumption'), '</option>';
	echo '<option value="High" selected="selected">', _('Heavy Drinker'), '</option>';
}

echo '</select><br />
		<label for="Coffee">', _('Coffee Consumption'), '</label>
		<select name="Coffee">';
if ($_POST['Coffee'] == 'Low') {
	echo '<option value="Low" selected="selected">', _('Low or No Consumption'), '</option>';
	echo '<option value="Medium">', _('Medium Consumption'), '</option>';
	echo '<option value="High">', _('High Consumption'), '</option>';
}
if ($_POST['Coffee'] == 'Medium') {
	echo '<option value="Low"">', _('Low or No Consumption'), '</option>';
	echo '<option value="Medium" selected="selected">', _('Medium Consumption'), '</option>';
	echo '<option value="High">', _('High Consumption'), '</option>';
}
if ($_POST['Coffee'] == 'High') {
	echo '<option value="Low"">', _('Low or No Consumption'), '</option>';
	echo '<option value="Medium">', _('Medium Consumption'), '</option>';
	echo '<option value="High" selected="selected">', _('High Consumption'), '</option>';
}
echo '</select><br />
		<label for="Smoking">', _('Smoking'), '</label>
		<select name="Smoking">';

if ($_POST['Smoking'] == 'Low') {
	echo '<option value="Low" selected="selected">', _('Non smoker'), '</option>';
	echo '<option value="Medium">', _('Occasional smoker'), '</option>';
	echo '<option value="High">', _('Heavy smoker'), '</option>';
	echo '<option value="Ex">', _('Ex smoker'), '</option>';
}
if ($_POST['Smoking'] == 'Medium') {
	echo '<option value="Low">', _('Non smoker'), '</option>';
	echo '<option value="Medium" selected="selected">', _('Occasional smoker'), '</option>';
	echo '<option value="High">', _('Heavy smoker'), '</option>';
	echo '<option value="Ex">', _('Ex smoker'), '</option>';
}
if ($_POST['Smoking'] == 'High') {
	echo '<option value="Low">', _('Non smoker'), '</option>';
	echo '<option value="Medium">', _('Occasional smoker'), '</option>';
	echo '<option value="High" selected="selected">', _('Heavy smoker'), '</option>';
	echo '<option value="Ex">', _('Ex smoker'), '</option>';
}
if ($_POST['Smoking'] == 'Ex') {
	echo '<option value="Low">', _('Non smoker'), '</option>';
	echo '<option value="Medium">', _('Occasional smoker'), '</option>';
	echo '<option value="High">', _('Heavy smoker'), '</option>';
	echo '<option value="Ex" selected="selected">', _('Ex smoker'), '</option>';
}
echo '</select><br />
		<label for="Thirst">', _('Thirst'), '</label>
		<select name="Thirst">';
if ($_POST['Thirst'] == 'Normal') {
	echo '<option value="Normal" selected="selected">', _('Normal'), '</option>';
	echo '<option value="Reduced">', _('Reduced'), '</option>';
	echo '<option value="Increased">', _('Increased'), '</option>';
}
if ($_POST['Thirst'] == 'Reduced') {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Reduced" selected="selected">', _('Reduced'), '</option>';
	echo '<option value="Increased">', _('Increased'), '</option>';
}
if ($_POST['Thirst'] == 'Increased') {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Reduced">', _('Reduced'), '</option>';
	echo '<option value="Increased" selected="selected">', _('Increased'), '</option>';
}
echo '</select><br />
		<label for="Bowel">', _('Bowel movements'), '</label>
		<select name="Bowel">';
if ($_POST['Bowel'] == 'Normal') {
	echo '<option value="Normal" selected="selected">', _('Normal'), '</option>';
	echo '<option value="Diarrhea">', _('Diarrhea'), '</option>';
	echo '<option value="Constipated">', _('Constipated'), '</option>';
	echo '<option value="Tenesmo">', _('Tenesmo'), '</option>';
}
if ($_POST['Bowel'] == 'Diarrhea') {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Diarrhea" selected="selected">', _('Diarrhea'), '</option>';
	echo '<option value="Constipated">', _('Constipated'), '</option>';
	echo '<option value="Tenesmo">', _('Tenesmo'), '</option>';
}
if ($_POST['Bowel'] == 'Constipated') {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Diarrhea">', _('Diarrhea'), '</option>';
	echo '<option value="Constipated" selected="selected">', _('Constipated'), '</option>';
	echo '<option value="Tenesmo">', _('Tenesmo'), '</option>';
}
if ($_POST['Bowel'] == 'Tenesmo') {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Diarrhea">', _('Diarrhea'), '</option>';
	echo '<option value="Constipated">', _('Constipated'), '</option>';
	echo '<option value="Tenesmo" selected="selected">', _('Tenesmo'), '</option>';
}
echo '</select><br />
		<label for="Diuresis">', _('Diuresis'), '</label>
		<select name="Diuresis">';
if ($_POST['Diuresis'] == 'Normal') {
	echo '<option value="Normal" selected="selected">', _('Normal'), '</option>';
	echo '<option value="Low">', _('Low'), '</option>';
	echo '<option value="High">', _('High'), '</option>';
}
if ($_POST['Diuresis'] == 'Low') {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Low" selected="selected">', _('Low'), '</option>';
	echo '<option value="High">', _('High'), '</option>';
}
if ($_POST['Diuresis'] == 'High') {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Low">', _('Low'), '</option>';
	echo '<option value="High" selected="selected">', _('High'), '</option>';
}
echo '</select>
	</div>';

echo '<div id="Pathological" class="tabcontent">
		<label for="Other">', _('Other Medical History'), '</label>
		<textarea cols=80 rows=25 wrap="physical" name="Other">', $_POST['Other'], '</textarea><br />
	</div>';

echo '<div id="Remote" class="tabcontent">
		<label for="Remote">', _('Remote Medical History'), '</label>
		<textarea cols=80 rows=25 wrap="physical" name="Remote">', $_POST['Remote'], '</textarea><br />
	</div>';

echo '</div>';

echo '<div class="centre">
		<input type="submit" name="save" value="', _('Save Data'), '" />
	</div>';

echo '</form>';
include ('includes/footer.php');

echo '<script async type="text/javascript" src = "', $PathPrefix, $RootPath, '/javascripts/tabs.js"></script>';
?>