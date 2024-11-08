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

$Title = _('Daily Ward Notes') . ' : PID-' . $PID . ' - ' . $MyRow['name_first'] . ' ' . $MyRow['name_last'];

include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', $Title, '
	</p>';

/* Test whether a yellow page record exists for this encounter */
$SQL = "SELECT nr FROM care_target_test WHERE encounter_nr='" . $Encounter . "'";
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
		$SQL = "INSERT INTO care_target_test (encounter_nr,
												staff_name,
												location_id,
												create_id,
												create_time,
												tipo_costituzionale,
												condizioni_generali,
												stato_nutrizione,
												decubito,
												psiche,
												cute,
												descrizione_mucose,
												annessi_cutanei,
												edemi,
												sottocutaneo_descrizione,
												temperatura,
												polso_battiti,
												polso,
												pressione_max,
												pressione_min,
												linfoghiandolare_descrizione,
												capo_descrizione,
												globi_oculari,
												sclere_descrizione,
												pupille,
												riflesso_corneale,
												orecchie,
												naso,
												cavo_orofaringeo,
												lingua,
												dentatura,
												tonsille,
												collo_forma,
												mobilita,
												atteggiamento,
												collo_descrizione,
												giugulari_turgide,
												tiroide_normale,
												torace_forma,
												mammelle,
												reperti_torace,
												ispezione_respiratoria,
												palpazione_respiratoria,
												percussione_respiratoria,
												ascoltazione_respiratoria,
												reperti_respiratoria,
												ispezione_cuore,
												palpazione_cuore,
												percussione_cuore,
												ascoltazione_cuore,
												reperti_cuore,
												vasi_periferici_descrizione,
												arterie,
												vene,
												reperti_vasi,
												addome_ispezione,
												addome_palpazione,
												addome_percussione,
												addome_ascoltazione,
												addome_descrizione,
												rettale,
												reperti_addome,
												fegato_descrizione,
												epatomegalia,
												murphy,
												colecisti_palpabile,
												reperti_fegato,
												milza_descrizione,
												reperti_milza,
												urogenitale_descrizione,
												esplorazione_vaginale,
												reperti_genitale,
												osteoarticolare_descrizione,
												muscolare_descrizione,
												reperti_muscolare,
												nervoso_descrizione,
												nervi_cranici,
												riflessi_superficiali,
												reperti_nervoso
										) VALUES (
											'" . $Encounter . "',
											'" . $_SESSION['UserID'] . "',
											'" . $Location . "',
											'" . $_SESSION['UserID'] . "',
											NOW(),
											'" . $_POST['Constitution'] . "',
											'" . $_POST['General'] . "',
											'" . $_POST['Nutritional'] . "',
											'" . $_POST['Decubitus'] . "',
											'" . $_POST['Psyche'] . "',
											'" . $_POST['Skin'] . "',
											'" . $_POST['Mucous'] . "',
											'" . $_POST['SkinAppendages'] . "',
											'" . $_POST['Edema'] . "',
											'" . $_POST['EdemaDescription'] . "',
											'" . $_POST['Temperature'] . "',
											'" . $_POST['CentralPulse'] . "',
											'" . $_POST['PeripheralPulse'] . "',
											'" . $_POST['DiastolicPressure'] . "',
											'" . $_POST['SystolicPressure'] . "',
											'" . $_POST['LymphGlands'] . "',
											'" . $_POST['Head'] . "',
											'" . $_POST['EyeBalls'] . "',
											'" . $_POST['Sclera'] . "',
											'" . $_POST['Pupils'] . "',
											'" . $_POST['CornealReflex'] . "',
											'" . $_POST['Ears'] . "',
											'" . $_POST['Nose'] . "',
											'" . $_POST['Mouth'] . "',
											'" . $_POST['Tongue'] . "',
											'" . $_POST['Teeth'] . "',
											'" . $_POST['Tonsils'] . "',
											'" . $_POST['NeckShape'] . "',
											'" . $_POST['NeckMobility'] . "',
											'" . $_POST['NeckPosture'] . "',
											'" . $_POST['NeckExam'] . "',
											'" . $_POST['TurgidJugular'] . "',
											'" . $_POST['Thyroid'] . "',
											'" . $_POST['ChestShape'] . "',
											'" . $_POST['Breasts'] . "',
											'" . $_POST['ChestExamined'] . "',
											'" . $_POST['RespiratoryInspection'] . "',
											'" . $_POST['RespiratoryPalpation'] . "',
											'" . $_POST['RespiratorySound'] . "',
											'" . $_POST['RespiratoryAuscultation'] . "',
											'" . $_POST['RespiratorySystemExamined'] . "',
											'" . $_POST['HeartInspection'] . "',
											'" . $_POST['HeartPalpation'] . "',
											'" . $_POST['HeartSound'] . "',
											'" . $_POST['HeartAuscultation'] . "',
											'" . $_POST['HeartSystemExamined'] . "',
											'" . $_POST['VesselDescription'] . "',
											'" . $_POST['Arteries'] . "',
											'" . $_POST['Veins'] . "',
											'" . $_POST['VesselsExamined'] . "',
											'" . $_POST['AbdomenInspection'] . "',
											'" . $_POST['AbdomenPalpation'] . "',
											'" . $_POST['AbdomenSound'] . "',
											'" . $_POST['AbdomenAuscultation'] . "',
											'" . $_POST['AbdomenDescription'] . "',
											'" . $_POST['RectalExploration'] . "',
											'" . $_POST['AbdomenExamined'] . "',
											'" . $_POST['LiverDescription'] . "',
											'" . $_POST['Hepatomegaly'] . "',
											'" . $_POST['Murphy'] . "',
											'" . $_POST['PalpableGallbladder'] . "',
											'" . $_POST['LiverExamined'] . "',
											'" . $_POST['SpleenDescription'] . "',
											'" . $_POST['SpleenExamined'] . "',
											'" . $_POST['UrogenitalMale'] . "',
											'" . $_POST['UrogenitalFemale'] . "',
											'" . $_POST['UrogenitalExamined'] . "',
											'" . $_POST['MusculoskeletalDescription'] . "',
											'" . $_POST['Musculature'] . "',
											'" . $_POST['MusculoskeletalExamined'] . "',
											'" . $_POST['NervousSystemDescription'] . "',
											'" . $_POST['CranialNerves'] . "',
											'" . $_POST['SuperficialReflexes'] . "',
											'" . $_POST['NervousSystemExamined'] . "'
										)";
		$ErrMsg = _('The daily ward notes record could not be added because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	} else {
		$SQL = "UPDATE care_target_test SET staff_name='" . $_SESSION['UserID'] . "',
											location_id='" . $Location . "',
											tipo_costituzionale='" . $_POST['Constitution'] . "',
											condizioni_generali='" . $_POST['General'] . "',
											stato_nutrizione='" . $_POST['Nutritional'] . "',
											decubito='" . $_POST['Decubitus'] . "',
											psiche='" . $_POST['Psyche'] . "',
											cute='" . $_POST['Skin'] . "',
											descrizione_mucose='" . $_POST['Mucous'] . "',
											annessi_cutanei='" . $_POST['SkinAppendages'] . "',
											edemi='" . $_POST['Edema'] . "',
											sottocutaneo_descrizione='" . $_POST['EdemaDescription'] . "',
											temperatura='" . $_POST['Temperature'] . "',
											polso_battiti='" . $_POST['CentralPulse'] . "',
											polso='" . $_POST['PeripheralPulse'] . "',
											pressione_max='" . $_POST['DiastolicPressure'] . "',
											pressione_min='" . $_POST['SystolicPressure'] . "',
											linfoghiandolare_descrizione='" . $_POST['LymphGlands'] . "',
											capo_descrizione='" . $_POST['Head'] . "',
											globi_oculari='" . $_POST['EyeBalls'] . "',
											sclere_descrizione='" . $_POST['Sclera'] . "',
											pupille='" . $_POST['Pupils'] . "',
											riflesso_corneale='" . $_POST['CornealReflex'] . "',
											orecchie='" . $_POST['Ears'] . "',
											naso='" . $_POST['Nose'] . "',
											cavo_orofaringeo='" . $_POST['Mouth'] . "',
											lingua='" . $_POST['Tongue'] . "',
											dentatura='" . $_POST['Teeth'] . "',
											tonsille='" . $_POST['Tonsils'] . "',
											collo_forma='" . $_POST['NeckShape'] . "',
											mobilita='" . $_POST['NeckMobility'] . "',
											atteggiamento='" . $_POST['NeckPosture'] . "',
											collo_descrizione='" . $_POST['NeckExam'] . "',
											giugulari_turgide='" . $_POST['TurgidJugular'] . "',
											tiroide_normale='" . $_POST['Thyroid'] . "',
											torace_forma='" . $_POST['ChestShape'] . "',
											mammelle='" . $_POST['Breasts'] . "',
											reperti_torace='" . $_POST['ChestExamined'] . "',
											ispezione_respiratoria='" . $_POST['RespiratoryInspection'] . "',
											palpazione_respiratoria='" . $_POST['RespiratoryPalpation'] . "',
											percussione_respiratoria='" . $_POST['RespiratorySound'] . "',
											ascoltazione_respiratoria='" . $_POST['RespiratoryAuscultation'] . "',
											reperti_respiratoria='" . $_POST['RespiratorySystemExamined'] . "',
											ispezione_cuore='" . $_POST['HeartInspection'] . "',
											palpazione_cuore='" . $_POST['HeartPalpation'] . "',
											percussione_cuore='" . $_POST['HeartSound'] . "',
											ascoltazione_cuore='" . $_POST['HeartAuscultation'] . "',
											reperti_cuore='" . $_POST['HeartSystemExamined'] . "',
											vasi_periferici_descrizione='" . $_POST['VesselDescription'] . "',
											arterie='" . $_POST['Arteries'] . "',
											vene='" . $_POST['Veins'] . "',
											reperti_vasi='" . $_POST['VesselsExamined'] . "',
											addome_ispezione='" . $_POST['AbdomenInspection'] . "',
											addome_palpazione='" . $_POST['AbdomenPalpation'] . "',
											addome_percussione='" . $_POST['AbdomenSound'] . "',
											addome_ascoltazione='" . $_POST['AbdomenAuscultation'] . "',
											addome_descrizione='" . $_POST['AbdomenDescription'] . "',
											rettale='" . $_POST['RectalExploration'] . "',
											reperti_addome='" . $_POST['AbdomenExamined'] . "',
											fegato_descrizione='" . $_POST['LiverDescription'] . "',
											epatomegalia='" . $_POST['Hepatomegaly'] . "',
											murphy='" . $_POST['Murphy'] . "',
											colecisti_palpabile='" . $_POST['PalpableGallbladder'] . "',
											reperti_fegato='" . $_POST['LiverExamined'] . "',
											milza_descrizione='" . $_POST['SpleenDescription'] . "',
											reperti_milza='" . $_POST['SpleenExamined'] . "',
											urogenitale_descrizione='" . $_POST['UrogenitalMale'] . "',
											esplorazione_vaginale='" . $_POST['UrogenitalFemale'] . "',
											reperti_genitale='" . $_POST['UrogenitalExamined'] . "',
											osteoarticolare_descrizione='" . $_POST['MusculoskeletalDescription'] . "',
											muscolare_descrizione='" . $_POST['Musculature'] . "',
											reperti_muscolare='" . $_POST['MusculoskeletalExamined'] . "',
											nervoso_descrizione='" . $_POST['NervousSystemDescription'] . "',
											nervi_cranici='" . $_POST['CranialNerves'] . "',
											riflessi_superficiali='" . $_POST['SuperficialReflexes'] . "',
											reperti_nervoso='" . $_POST['NervousSystemExamined'] . "',
											modify_id='" . $_SESSION['UserID'] . "',
											modify_time=NOW()
										WHERE encounter_nr='" . $Encounter . "'
											AND nr='" . $Number . "'";
		$ErrMsg = _('The daily ward notes record could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	}
	if (DB_error_no($Result) == 0) {
		prnMsg(_('The information was correctly saved'), 'success');
	}
}

if (!isset($NewRecord) and !isset($_POST['save'])) {
	$SQL = "SELECT tipo_costituzionale,
					condizioni_generali,
					stato_nutrizione,
					decubito,
					psiche,
					cute,
					descrizione_mucose,
					annessi_cutanei,
					edemi,
					sottocutaneo_descrizione,
					temperatura,
					polso_battiti,
					polso,
					pressione_max,
					pressione_min,
					linfoghiandolare_descrizione,
					capo_descrizione,
					globi_oculari,
					sclere_descrizione,
					pupille,
					riflesso_corneale,
					orecchie,
					naso,
					cavo_orofaringeo,
					lingua,
					dentatura,
					tonsille,
					collo_forma,
					mobilita,
					atteggiamento,
					collo_descrizione,
					giugulari_turgide,
					tiroide_normale,
					torace_forma,
					mammelle,
					reperti_torace,
					ispezione_respiratoria,
					palpazione_respiratoria,
					percussione_respiratoria,
					ascoltazione_respiratoria,
					reperti_respiratoria,
					ispezione_cuore,
					palpazione_cuore,
					percussione_cuore,
					ascoltazione_cuore,
					reperti_cuore,
					vasi_periferici_descrizione,
					arterie,
					vene,
					reperti_vasi,
					addome_ispezione,
					addome_palpazione,
					addome_percussione,
					addome_ascoltazione,
					addome_descrizione,
					rettale,
					reperti_addome,
					fegato_descrizione,
					epatomegalia,
					murphy,
					colecisti_palpabile,
					reperti_fegato,
					milza_descrizione,
					reperti_milza,
					urogenitale_descrizione,
					esplorazione_vaginale,
					reperti_genitale,
					osteoarticolare_descrizione,
					muscolare_descrizione,
					reperti_muscolare,
					nervoso_descrizione,
					nervi_cranici,
					riflessi_superficiali,
					reperti_nervoso
		FROM care_target_test
		WHERE encounter_nr='" . $Encounter . "'
			AND nr='" . $Number . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['Constitution'] = $MyRow['tipo_costituzionale'];
	$_POST['General'] = $MyRow['condizioni_generali'];
	$_POST['Nutritional'] = $MyRow['stato_nutrizione'];
	$_POST['Decubitus'] = $MyRow['decubito'];
	$_POST['Psyche'] = $MyRow['psiche'];
	$_POST['Skin'] = $MyRow['cute'];
	$_POST['Mucous'] = $MyRow['descrizione_mucose'];
	$_POST['SkinAppendages'] = $MyRow['annessi_cutanei'];
	$_POST['Edema'] = $MyRow['edemi'];
	$_POST['EdemaDescription'] = $MyRow['sottocutaneo_descrizione'];
	$_POST['Temperature'] = $MyRow['temperatura'];
	$_POST['CentralPulse'] = $MyRow['polso_battiti'];
	$_POST['PeripheralPulse'] = $MyRow['polso'];
	$_POST['DiastolicPressure'] = $MyRow['pressione_max'];
	$_POST['SystolicPressure'] = $MyRow['pressione_min'];
	$_POST['LymphGlands'] = $MyRow['linfoghiandolare_descrizione'];
	$_POST['Head'] = $MyRow['capo_descrizione'];
	$_POST['EyeBalls'] = $MyRow['globi_oculari'];
	$_POST['Sclera'] = $MyRow['sclere_descrizione'];
	$_POST['Pupils'] = $MyRow['pupille'];
	$_POST['CornealReflex'] = $MyRow['riflesso_corneale'];
	$_POST['Ears'] = $MyRow['orecchie'];
	$_POST['Nose'] = $MyRow['naso'];
	$_POST['Mouth'] = $MyRow['cavo_orofaringeo'];
	$_POST['Tongue'] = $MyRow['lingua'];
	$_POST['Teeth'] = $MyRow['dentatura'];
	$_POST['Tonsils'] = $MyRow['tonsille'];
	$_POST['NeckShape'] = $MyRow['collo_forma'];
	$_POST['NeckMobility'] = $MyRow['mobilita'];
	$_POST['NeckPosture'] = $MyRow['atteggiamento'];
	$_POST['NeckExam'] = $MyRow['collo_descrizione'];
	$_POST['TurgidJugular'] = $MyRow['giugulari_turgide'];
	$_POST['Thyroid'] = $MyRow['tiroide_normale'];
	$_POST['ChestShape'] = $MyRow['torace_forma'];
	$_POST['Breasts'] = $MyRow['mammelle'];
	$_POST['ChestExamined'] = $MyRow['reperti_torace'];
	$_POST['RespiratoryInspection'] = $MyRow['ispezione_respiratoria'];
	$_POST['RespiratoryPalpation'] = $MyRow['palpazione_respiratoria'];
	$_POST['RespiratorySound'] = $MyRow['percussione_respiratoria'];
	$_POST['RespiratoryAuscultation'] = $MyRow['ascoltazione_respiratoria'];
	$_POST['RespiratorySystemExamined'] = $MyRow['reperti_respiratoria'];
	$_POST['HeartInspection'] = $MyRow['ispezione_cuore'];
	$_POST['HeartPalpation'] = $MyRow['palpazione_cuore'];
	$_POST['HeartSound'] = $MyRow['percussione_cuore'];
	$_POST['HeartAuscultation'] = $MyRow['ascoltazione_cuore'];
	$_POST['HeartSystemExamined'] = $MyRow['reperti_cuore'];
	$_POST['VesselDescription'] = $MyRow['vasi_periferici_descrizione'];
	$_POST['Arteries'] = $MyRow['arterie'];
	$_POST['Veins'] = $MyRow['vene'];
	$_POST['VesselsExamined'] = $MyRow['reperti_vasi'];
	$_POST['AbdomenInspection'] = $MyRow['addome_ispezione'];
	$_POST['AbdomenPalpation'] = $MyRow['addome_palpazione'];
	$_POST['AbdomenSound'] = $MyRow['addome_percussione'];
	$_POST['AbdomenAuscultation'] = $MyRow['addome_ascoltazione'];
	$_POST['AbdomenDescription'] = $MyRow['addome_descrizione'];
	$_POST['RectalExploration'] = $MyRow['rettale'];
	$_POST['AbdomenExamined'] = $MyRow['reperti_addome'];
	$_POST['LiverDescription'] = $MyRow['fegato_descrizione'];
	$_POST['Hepatomegaly'] = $MyRow['epatomegalia'];
	$_POST['Murphy'] = $MyRow['murphy'];
	$_POST['PalpableGallbladder'] = $MyRow['colecisti_palpabile'];
	$_POST['LiverExamined'] = $MyRow['reperti_fegato'];
	$_POST['SpleenDescription'] = $MyRow['milza_descrizione'];
	$_POST['SpleenExamined'] = $MyRow['reperti_milza'];
	$_POST['UrogenitalMale'] = $MyRow['urogenitale_descrizione'];
	$_POST['UrogenitalFemale'] = $MyRow['esplorazione_vaginale'];
	$_POST['UrogenitalExamined'] = $MyRow['reperti_genitale'];
	$_POST['MusculoskeletalDescription'] = $MyRow['osteoarticolare_descrizione'];
	$_POST['Musculature'] = $MyRow['muscolare_descrizione'];
	$_POST['MusculoskeletalExamined'] = $MyRow['reperti_muscolare'];
	$_POST['NervousSystemDescription'] = $MyRow['nervoso_descrizione'];
	$_POST['CranialNerves'] = $MyRow['nervi_cranici'];
	$_POST['SuperficialReflexes'] = $MyRow['riflessi_superficiali'];
	$_POST['NervousSystemExamined'] = $MyRow['reperti_nervoso'];
} else {
	$_POST['Constitution'] = 'Mesomorph';
	$_POST['General'] = 'Good';
	$_POST['Nutrional'] = '';
	$_POST['Decubitus'] = '';
	$_POST['Psyche'] = '';
	$_POST['Skin'] = '';
	$_POST['Mucous'] = '';
	$_POST['SkinAppendages'] = '';
	$_POST['Edema'] = '';
	$_POST['EdemaDescription'] = '';
	$_POST['Temperature'] = '';
	$_POST['CentralPulse'] = '';
	$_POST['PeripheralPulse'] = '';
	$_POST['DiastolicPressure'] = '';
	$_POST['SystolicPressure'] = '';
	$_POST['LymphGlands'] = '';
	$_POST['Head'] = '';
	$_POST['EyeBalls'] = '';
	$_POST['Sclera'] = '';
	$_POST['Pupils'] = '';
	$_POST['CornealReflex'] = '';
	$_POST['Ears'] = '';
	$_POST['Nose'] = '';
	$_POST['Mouth'] = '';
	$_POST['Tongue'] = '';
	$_POST['Teeth'] = '';
	$_POST['Tonsils'] = '';
	$_POST['NeckShape'] = '';
	$_POST['NeckMobility'] = '';
	$_POST['NeckPosture'] = '';
	$_POST['NeckExam'] = '';
	$_POST['TurgidJugular'] = '';
	$_POST['Thyroid'] = '';
	$_POST['ChestShape'] = '';
	$_POST['Breasts'] = '';
	$_POST['ChestExamined'] = '';
	$_POST['RespiratoryInspection'] = '';
	$_POST['RespiratoryPalpation'] = '';
	$_POST['RespiratorySound'] = '';
	$_POST['RespiratoryAuscultation'] = '';
	$_POST['RespiratorySystemExamined'] = '';
	$_POST['HeartInspection'] = '';
	$_POST['HeartPalpation'] = '';
	$_POST['HeartSound'] = '';
	$_POST['HeartAuscultation'] = '';
	$_POST['HeartSystemExamined'] = '';
	$_POST['VesselDescription'] = '';
	$_POST['Arteries'] = '';
	$_POST['Veins'] = '';
	$_POST['VesselsExamined'] = '';
	$_POST['AbdomenInspection'] = '';
	$_POST['AbdomenPalpation'] = '';
	$_POST['AbdomenSound'] = '';
	$_POST['AbdomenAuscultation'] = '';
	$_POST['AbdomenDescription'] = '';
	$_POST['RectalExploration'] = '';
	$_POST['AbdomenExamined'] = '';
	$_POST['LiverDescription'] = '';
	$_POST['Hepatomegaly'] = '';
	$_POST['Murphy'] = '';
	$_POST['PalpableGallbladder'] = '';
	$_POST['LiverExamined'] = '';
	$_POST['SpleenDescription'] = '';
	$_POST['SpleenExamined'] = '';
	$_POST['UrogenitalMale'] = '';
	$_POST['UrogenitalFemale'] = '';
	$_POST['UrogenitalExamined'] = '';
	$_POST['MusculoskeletalDescription'] = '';
	$_POST['Musculature'] = '';
	$_POST['MusculoskeletalExamined'] = '';
	$_POST['NervousSystemDescription'] = '';
	$_POST['CranialNerves'] = '';
	$_POST['SuperficialReflexes'] = '';
	$_POST['NervousSystemExamined'] = '';
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Encounter=', $Encounter, '&Ward=', $Location, '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('Ward Notes'), '</legend>';

echo '<field>
		<label for="Constitution">', _('Physique'), '</label>
		<select name="Constitution">';
if ($_POST['Constitution'] == 'Ectomorph') {
	echo '<option value="Ectomorph" selected="selected">', _('Ectomorph'), '</option>';
	echo '<option value="Mesomorph">', _('Mesomorph'), '</option>';
	echo '<option value="Endomorph">', _('Endomorph'), '</option>';
}
if ($_POST['Constitution'] == 'Mesomorph') {
	echo '<option value="Ectomorph">', _('Ectomorph'), '</option>';
	echo '<option value="Mesomorph" selected="selected">', _('Mesomorph'), '</option>';
	echo '<option value="Endomorph">', _('Endomorph'), '</option>';
}
if ($_POST['Constitution'] == 'Endomorph') {
	echo '<option value="Ectomorph">', _('Ectomorph'), '</option>';
	echo '<option value="Mesomorph">', _('Mesomorph'), '</option>';
	echo '<option value="Endomorph" selected="selected">', _('Endomorph'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="General">', _('General Condition'), '</label>
		<select name="General">';
if ($_POST['General'] == 'Good') {
	echo '<option value="Good" selected="selected">', _('Good'), '</option>';
	echo '<option value="Fair">', _('Fair'), '</option>';
	echo '<option value="Deceased">', _('Deceased'), '</option>';
}
if ($_POST['General'] == 'Fair') {
	echo '<option value="Good">', _('Good'), '</option>';
	echo '<option value="Fair" selected="selected">', _('Fair'), '</option>';
	echo '<option value="Deceased">', _('Deceased'), '</option>';
}
if ($_POST['General'] == 'Deceased') {
	echo '<option value="Good">', _('Good'), '</option>';
	echo '<option value="Fair">', _('Fair'), '</option>';
	echo '<option value="Deceased" selected="selected">', _('Deceased'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Nutritional">', _('Nutritional status'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Nutritional">', $_POST['Nutritional'], '</textarea>
	</field>';

echo '<field>
		<label for="Decubitus">', _('Decubitus'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Decubitus">', $_POST['Decubitus'], '</textarea>
	</field>';

echo '<field>
		<label for="Psyche">', _('Psychological Report'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Psyche">', $_POST['Psyche'], '</textarea>
	</field>';

echo '<field>
		<label for="Skin">', _('Skin Condition'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Skin">', $_POST['Skin'], '</textarea>
	</field>';

echo '<field>
		<label for="Mucous">', _('Mucous'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Mucous">', $_POST['Mucous'], '</textarea>
	</field>';

echo '<field>
		<label for="SkinAppendages">', _('Skin appendages'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="SkinAppendages">', $_POST['SkinAppendages'], '</textarea>
	</field>';

echo '<field>
		<label for="Edema">', _('Edema'), '</label>
		<select name="Edema">';
if ($_POST['Edema'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="EdemaDescription">', _('Edema Description'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="EdemaDescription">', $_POST['EdemaDescription'], '</textarea>
	</field>';

echo '<field>
		<label for="Temperature">', _('Temperature'), '</label>
		<select name="Temperature">';
if ($_POST['Temperature'] == 'Apyrexia') {
	echo '<option value="Apyrexia" selected="selected">', _('Apyrexia'), '</option>';
	echo '<option value="Fever">', _('Fever'), '</option>';
	echo '<option value="Hypothermia">', _('Hypothermia'), '</option>';
} elseif ($_POST['Temperature'] == 'Fever') {
	echo '<option value="Apyrexia">', _('Apyrexia'), '</option>';
	echo '<option value="Fever" selected="selected">', _('Fever'), '</option>';
	echo '<option value="Hypothermia">', _('Hypothermia'), '</option>';
} elseif ($_POST['Temperature'] == 'Hypothermia') {
	echo '<option value="Apyrexia">', _('Apyrexia'), '</option>';
	echo '<option value="Fever">', _('Fever'), '</option>';
	echo '<option value="Hypothermia" selected="selected">', _('Hypothermia'), '</option>';
} else {
	echo '<option value="Apyrexia">', _('Apyrexia'), '</option>';
	echo '<option value="Fever">', _('Fever'), '</option>';
	echo '<option value="Hypothermia">', _('Hypothermia'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="CentralPulse">', _('Central Pulse'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="CentralPulse">', $_POST['CentralPulse'], '</textarea>
	</field>';

echo '<field>
		<label for="PeripheralPulse">', _('Peripheral Pulse'), '</label>
		<select name="PeripheralPulse">';
if ($_POST['PeripheralPulse'] == 'Regular') {
	echo '<option value="Regular" selected="selected">', _('Regular'), '</option>';
	echo '<option value="Slow">', _('Slow'), '</option>';
	echo '<option value="Weak">', _('Weak'), '</option>';
	echo '<option value="Fast">', _('Fast'), '</option>';
	echo '<option value="Arhythmic">', _('Arhythmic'), '</option>';
} elseif ($_POST['PeripheralPulse'] == 'Slow') {
	echo '<option value="Regular">', _('Regular'), '</option>';
	echo '<option value="Slow" selected="selected">', _('Slow'), '</option>';
	echo '<option value="Weak">', _('Weak'), '</option>';
	echo '<option value="Fast">', _('Fast'), '</option>';
	echo '<option value="Arhythmic">', _('Arhythmic'), '</option>';
} elseif ($_POST['PeripheralPulse'] == 'Weak') {
	echo '<option value="Regular">', _('Regular'), '</option>';
	echo '<option value="Slow">', _('Slow'), '</option>';
	echo '<option value="Weak" selected="selected">', _('Weak'), '</option>';
	echo '<option value="Fast">', _('Fast'), '</option>';
	echo '<option value="Arhythmic">', _('Arhythmic'), '</option>';
} elseif ($_POST['PeripheralPulse'] == 'Fast') {
	echo '<option value="Regular">', _('Regular'), '</option>';
	echo '<option value="Slow">', _('Slow'), '</option>';
	echo '<option value="Weak">', _('Weak'), '</option>';
	echo '<option value="Fast" selected="selected">', _('Fast'), '</option>';
	echo '<option value="Arhythmic">', _('Arhythmic'), '</option>';
} elseif ($_POST['PeripheralPulse'] == 'Arhythmic') {
	echo '<option value="Regular">', _('Regular'), '</option>';
	echo '<option value="Slow">', _('Slow'), '</option>';
	echo '<option value="Weak">', _('Weak'), '</option>';
	echo '<option value="Fast">', _('Fast'), '</option>';
	echo '<option value="Arhythmic" selected="selected">', _('Arhythmic'), '</option>';
} else {
	echo '<option value="Regular">', _('Regular'), '</option>';
	echo '<option value="Slow">', _('Slow'), '</option>';
	echo '<option value="Weak">', _('Weak'), '</option>';
	echo '<option value="Fast">', _('Fast'), '</option>';
	echo '<option value="Arhythmic">', _('Arhythmic'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="DiastolicPressure">', _('Diastolic pressure'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="DiastolicPressure">', $_POST['DiastolicPressure'], '</textarea>
	</field>';

echo '<field>
		<label for="SystolicPressure">', _('Systolic pressure'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="SystolicPressure">', $_POST['SystolicPressure'], '</textarea>
	</field>';

echo '<field>
		<label for="LymphGlands">', _('Lymph glands'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="LymphGlands">', $_POST['LymphGlands'], '</textarea>
	</field>';

echo '<field>
		<label for="Head">', _('Head'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Head">', $_POST['Head'], '</textarea>
	</field>';

echo '<field>
		<label for="EyeBalls">', _('Eye Balls'), '</label>
		<select name="EyeBalls">';
if ($_POST['EyeBalls'] == 'Normal') {
	echo '<option value="Normal" selected="selected">', _('Normal'), '</option>';
	echo '<option value="Exophthalmos">', _('Exophthalmos'), '</option>';
	echo '<option value="Enophthalmos">', _('Enophthalmos'), '</option>';
} elseif ($_POST['EyeBalls'] == 'Exophthalmos') {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Exophthalmos" selected="selected">', _('Exophthalmos'), '</option>';
	echo '<option value="Enophthalmos">', _('Enophthalmos'), '</option>';
} elseif ($_POST['EyeBalls'] == 'Enophthalmos') {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Exophthalmos">', _('Exophthalmos'), '</option>';
	echo '<option value="Enophthalmos" selected="selected">', _('Enophthalmos'), '</option>';
} else {
	echo '<option value="Normal">', _('Normal'), '</option>';
	echo '<option value="Exophthalmos">', _('Exophthalmos'), '</option>';
	echo '<option value="Enophthalmos">', _('Enophthalmos'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Sclera">', _('Sclera'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Sclera">', $_POST['Sclera'], '</textarea>
	</field>';

echo '<field>
		<label for="Pupils">', _('Pupils'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Pupils">', $_POST['Pupils'], '</textarea>
	</field>';

echo '<field>
		<label for="CornealReflex">', _('Corneal reflex'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="CornealReflex">', $_POST['CornealReflex'], '</textarea>
	</field>';

echo '<field>
		<label for="Ears">', _('Ears'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Ears">', $_POST['Ears'], '</textarea>
	</field>';

echo '<field>
		<label for="Nose">', _('Nose'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Nose">', $_POST['Nose'], '</textarea>
	</field>';

echo '<field>
		<label for="Mouth">', _('Mouth'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Mouth">', $_POST['Mouth'], '</textarea>
	</field>';

echo '<field>
		<label for="Tongue">', _('Tongue'), '</label>
		<select name="Tongue">';
if ($_POST['Tongue'] == 'Epithelialized') {
	echo '<option value="Epithelialized" selected="selected">', _('Well epithelialized'), '</option>';
	echo '<option value="Atrophic">', _('Atrophic'), '</option>';
	echo '<option value="Furred">', _('Furred'), '</option>';
	echo '<option value="Dry">', _('Dry'), '</option>';
} elseif ($_POST['Tongue'] == 'Atrophic') {
	echo '<option value="Epithelialized">', _('Well epithelialized'), '</option>';
	echo '<option value="Atrophic" selected="selected">', _('Atrophic'), '</option>';
	echo '<option value="Furred">', _('Furred'), '</option>';
	echo '<option value="Dry">', _('Dry'), '</option>';
} elseif ($_POST['Tongue'] == 'Furred') {
	echo '<option value="Epithelialized">', _('Well epithelialized'), '</option>';
	echo '<option value="Atrophic">', _('Atrophic'), '</option>';
	echo '<option value="Furred" selected="selected">', _('Furred'), '</option>';
	echo '<option value="Dry">', _('Dry'), '</option>';
} elseif ($_POST['Tongue'] == 'Dry') {
	echo '<option value="Epithelialized">', _('Well epithelialized'), '</option>';
	echo '<option value="Atrophic">', _('Atrophic'), '</option>';
	echo '<option value="Furred">', _('Furred'), '</option>';
	echo '<option value="Dry" selected="selected">', _('Dry'), '</option>';
} else {
	echo '<option value="Epithelialized">', _('Well epithelialized'), '</option>';
	echo '<option value="Atrophic">', _('Atrophic'), '</option>';
	echo '<option value="Furred">', _('Furred'), '</option>';
	echo '<option value="Dry">', _('Dry'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Teeth">', _('Teeth'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Teeth">', $_POST['Teeth'], '</textarea>
	</field>';

echo '<field>
		<label for="Tonsils">', _('Tonsils'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Tonsils">', $_POST['Tonsils'], '</textarea>
	</field>';

echo '<fieldset>
		<legend>', _('Neck'), '</legend>';

echo '<field>
		<label for="NeckShape">', _('Neck Shape'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="NeckShape">', $_POST['NeckShape'], '</textarea>
	</field>';

echo '<field>
		<label for="NeckMobility">', _('Neck Mobility'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="NeckMobility">', $_POST['NeckMobility'], '</textarea>
	</field>';

echo '<field>
		<label for="NeckPosture">', _('Neck Posture'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="NeckPosture">', $_POST['NeckPosture'], '</textarea>
	</field>';

echo '<field>
		<label for="NeckExam">', _('Neck Examination'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="NeckExam">', $_POST['NeckExam'], '</textarea>
	</field>';

echo '<field>
		<label for="TurgidJugular">', _('Turgid Jugular'), '</label>
		<select name="TurgidJugular">';
if ($_POST['TurgidJugular'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Thyroid">', _('Normal Thyroid'), '</label>
		<select name="Thyroid">';
if ($_POST['Thyroid'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', _('Chest'), '</legend>';

echo '<field>
		<label for="ChestShape">', _('Chest Shape'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="ChestShape">', $_POST['ChestShape'], '</textarea>
	</field>';

echo '<field>
		<label for="Breasts">', _('Breasts'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Breasts">', $_POST['Breasts'], '</textarea>
	</field>';

echo '<field>
		<label for="ChestExamined">', _('Chest Examined'), '</label>
		<select name="ChestExamined">';
if ($_POST['ChestExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', _('Respiratory system'), '</legend>';

echo '<field>
		<label for="RespiratoryInspection">', _('Respiratory Inspection'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="RespiratoryInspection">', $_POST['RespiratoryInspection'], '</textarea>
	</field>';

echo '<field>
		<label for="RespiratoryPalpation">', _('Respiratory Palpation'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="RespiratoryPalpation">', $_POST['RespiratoryPalpation'], '</textarea>
	</field>';

echo '<field>
		<label for="RespiratorySound">', _('Respiratory Sound'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="RespiratorySound">', $_POST['RespiratorySound'], '</textarea>
	</field>';

echo '<field>
		<label for="RespiratoryAuscultation">', _('Respiratory Auscultation'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="RespiratoryAuscultation">', $_POST['RespiratoryAuscultation'], '</textarea>
	</field>';

echo '<field>
		<label for="RespiratorySystemExamined">', _('Respiratory System Examined'), '</label>
		<select name="RespiratorySystemExamined">';
if ($_POST['RespiratorySystemExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', _('Heart'), '</legend>';

echo '<field>
		<label for="HeartInspection">', _('Heart Inspection'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="HeartInspection">', $_POST['HeartInspection'], '</textarea>
	</field>';

echo '<field>
		<label for="HeartPalpation">', _('Heart Palpation'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="HeartPalpation">', $_POST['HeartPalpation'], '</textarea>
	</field>';

echo '<field>
		<label for="HeartSound">', _('Heart Sound'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="HeartSound">', $_POST['HeartSound'], '</textarea>
	</field>';

echo '<field>
		<label for="HeartAuscultation">', _('Heart Auscultation'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="HeartAuscultation">', $_POST['HeartAuscultation'], '</textarea>
	</field>';

echo '<field>
		<label for="HeartSystemExamined">', _('Heart System Examined'), '</label>
		<select name="HeartSystemExamined">';
if ($_POST['HeartSystemExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', _('Peripheral Vessels'), '</legend>';

echo '<field>
		<label for="VesselDescription">', _('Vessel Description'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="VesselDescription">', $_POST['VesselDescription'], '</textarea>
	</field>';

echo '<field>
		<label for="Arteries">', _('Arteries'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Arteries">', $_POST['Arteries'], '</textarea>
	</field>';

echo '<field>
		<label for="Veins">', _('Veins'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Veins">', $_POST['Veins'], '</textarea>
	</field>';

echo '<field>
		<label for="VesselsExamined">', _('Vessels Examined'), '</label>
		<select name="VesselsExamined">';
if ($_POST['VesselsExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', _('Abdomen'), '</legend>';

echo '<field>
		<label for="AbdomenInspection">', _('Abdomen Inspection'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="AbdomenInspection">', $_POST['AbdomenInspection'], '</textarea>
	</field>';

echo '<field>
		<label for="AbdomenPalpation">', _('Abdomen Palpation'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="AbdomenPalpation">', $_POST['AbdomenPalpation'], '</textarea>
	</field>';

echo '<field>
		<label for="AbdomenSound">', _('Abdomen Sound'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="AbdomenSound">', $_POST['AbdomenSound'], '</textarea>
	</field>';

echo '<field>
		<label for="AbdomenAuscultation">', _('Abdomen Auscultation'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="AbdomenAuscultation">', $_POST['AbdomenAuscultation'], '</textarea>
	</field>';

echo '<field>
		<label for="AbdomenDescription">', _('Abdomen Description'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="AbdomenDescription">', $_POST['AbdomenDescription'], '</textarea>
	</field>';

echo '<field>
		<label for="RectalExploration">', _('Rectal Exploration'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="RectalExploration">', $_POST['RectalExploration'], '</textarea>
	</field>';

echo '<field>
		<label for="AbdomenExamined">', _('Abdomen Examined'), '</label>
		<select name="AbdomenExamined">';
if ($_POST['AbdomenExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', _('Liver'), '</legend>';

echo '<field>
		<label for="LiverDescription">', _('Description'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="LiverDescription">', $_POST['LiverDescription'], '</textarea>
	</field>';

echo '<field>
		<label for="Hepatomegaly">', _('Hepatomegaly'), '</label>
		<select name="Hepatomegaly">';
if ($_POST['Hepatomegaly'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Murphy">', _('Positive Murphy Test'), '</label>
		<select name="Murphy">';
if ($_POST['Murphy'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="PalpableGallbladder">', _('Palpable gallbladder'), '</label>
		<select name="PalpableGallbladder">';
if ($_POST['PalpableGallbladder'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="LiverExamined">', _('Liver Examined'), '</label>
		<select name="LiverExamined">';
if ($_POST['LiverExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', _('Spleen'), '</legend>';

echo '<field>
		<label for="SpleenDescription">', _('Description'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="SpleenDescription">', $_POST['SpleenDescription'], '</textarea>
	</field>';

echo '<field>
		<label for="SpleenExamined">', _('Spleen Examined'), '</label>
		<select name="SpleenExamined">';
if ($_POST['SpleenExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', _('Urogenital System'), '</legend>';

echo '<field>
		<label for="UrogenitalMale">', _('Male'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="UrogenitalMale">', $_POST['UrogenitalMale'], '</textarea>
	</field>';

echo '<field>
		<label for="UrogenitalFemale">', _('Female'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="UrogenitalFemale">', $_POST['UrogenitalFemale'], '</textarea>
	</field>';

echo '<field>
		<label for="UrogenitalExamined">', _('Urogenital System Examined'), '</label>
		<select name="UrogenitalExamined">';
if ($_POST['UrogenitalExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', _('Musculoskeletal system'), '</legend>';

echo '<field>
		<label for="MusculoskeletalDescription">', _('Description'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="MusculoskeletalDescription">', $_POST['MusculoskeletalDescription'], '</textarea>
	</field>';

echo '<field>
		<label for="Musculature">', _('Musculature'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="Musculature">', $_POST['Musculature'], '</textarea>
	</field>';

echo '<field>
		<label for="MusculoskeletalExamined">', _('Musculoskeletal System Examined'), '</label>
		<select name="MusculoskeletalExamined">';
if ($_POST['MusculoskeletalExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<fieldset>
		<legend>', ('Nervous System'), '</legend>';

echo '<field>
		<label for="NervousSystemDescription">', _('Description'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="NervousSystemDescription">', $_POST['NervousSystemDescription'], '</textarea>
	</field>';

echo '<field>
		<label for="CranialNerves">', _('Cranial Nerves'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="CranialNerves">', $_POST['CranialNerves'], '</textarea>
	</field>';

echo '<field>
		<label for="SuperficialReflexes">', _('Superficial Reflexes'), '</label>
		<textarea cols=40 rows=10 wrap="physical" name="SuperficialReflexes">', $_POST['SuperficialReflexes'], '</textarea>
	</field>';

echo '<field>
		<label for="NervousSystemExamined">', _('Nervous System Examined'), '</label>
		<select name="NervousSystemExamined">';
if ($_POST['NervousSystemExamined'] == 'Yes') {
	echo '<option value="Yes" selected="selected">', _('Yes'), '</option>';
	echo '<option value="No">', _('No'), '</option>';
} else {
	echo '<option value="Yes">', _('Yes'), '</option>';
	echo '<option value="No" selected="selected">', _('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="save" value="', _('Save Data'), '" />
	</div>';

echo '</form>';
include ('includes/footer.php');

?>