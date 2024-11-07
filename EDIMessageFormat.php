<?php


include('includes/session.php');
$Title = _('EDI Message Format');
$ViewTopic = 'EDI';
$BookMark = '';
include('includes/header.php');

if (isset($_GET['PartnerCode'])){
	$PartnerCode = $_GET['PartnerCode'];
} elseif (isset($_POST['PartnerCode'])){
	$PartnerCode = $_POST['PartnerCode'];
}

if (isset($_GET['MessageType'])){
	$MessageType = $_GET['MessageType'];
} elseif (isset($_POST['MessageType'])){
	$MessageType = $_POST['MessageType'];
}

if (isset($_GET['SelectedMessageLine'])){
	$SelectedMessageLine = $_GET['SelectedMessageLine'];
}elseif (isset($_POST['SelectedMessageLine'])){
	$SelectedMessageLine = $_POST['SelectedMessageLine'];
}


if (isset($_POST['NewEDIInvMsg'])){
	$sql = "INSERT INTO edimessageformat (partnercode,
						messagetype,
						sequenceno,
						section,
						linetext)
			SELECT '".$PartnerCode."',
				'INVOIC',
				sequenceno,
				section,
				linetext
			FROM edimessageformat
			WHERE partnercode='DEFAULT'
			AND messagetype='INVOIC'";

	$ErrMsg = _('There was an error inserting the default template invoice message records for') . ' ' . $PartnerCode . ' ' . _('because');
	$result = DB_query($sql,$ErrMsg);
}

$InputError = 0;
if ($InputError !=1 AND isset($_POST['update'])) {

	/*SelectedMessageLine could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
	if (!isset($SelectedMessageLine)) {
		$SelectedMessageLine='';
	}
	$sql = "UPDATE edimessageformat
			SET partnercode='" . $PartnerCode . "',
				messagetype='" . $MessageType . "',
				section='" . $_POST['Section'] . "',
				sequenceno='" . $_POST['SequenceNo'] . "',
				linetext='" . $_POST['LineText'] . "'
			WHERE id = '" . $SelectedMessageLine . "'";
	$result = DB_query($sql);
	$msg = _('Message line updated');
	unset ($SelectedMessageLine);

} elseif ($InputError !=1 AND isset($_POST['submit'])) {

/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new message line form */

	$sql = "INSERT INTO edimessageformat (
				partnercode,
				messagetype,
				section,
				sequenceno,
				linetext)
			VALUES (
				'" . $PartnerCode . "',
				'" . $MessageType . "',
				'" . $_POST['Section'] . "',
				'" . $_POST['SequenceNo'] . "',
				'" . $_POST['LineText'] . "'
				)";
		$msg = _('Message line added');
	//run the SQL from either of the above possibilites
	$result = DB_query($sql);
	unset ($SelectedMessageLine);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button


	$sql="DELETE FROM edimessageformat WHERE id='" . $_GET['delete']."'";
	$result = DB_query($sql);
	$msg=_('The selected message line has been deleted');

}
if (isset($msg)) {
	prnMsg($msg,'success');
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
	<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($SelectedMessageLine)) {

/* A message line could be posted when one has been edited and is being updated or GOT when selected for modification SelectedMessageLine will exist because it was sent with the page in a GET .
 If its the first time the page has been displayed with no parameters
then none of the above are true and the list of message lines will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT id,
				section,
				sequenceno,
				linetext
			FROM edimessageformat
			WHERE partnercode='" . $PartnerCode . "'
			AND messagetype='" . $MessageType . "'
			ORDER BY sequenceno";

	$result = DB_query($sql);

	echo '<table class="selection">';
	echo '<tr><th colspan="5"><h3>' . _('Definition of') . ' ' . $MessageType . ' ' . _('for') . ' ' . $PartnerCode . '</h3></th></tr>';
	$TableHeader = '<tr>
					<th>' . _('Section') . '</th>
					<th>' . _('Sequence') . '</th>
					<th>' . _('Format String') . '</th>
					</tr>';
	echo $TableHeader;

	while ($myrow = DB_fetch_row($result)) {

		printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td><a href="%s?SelectedMessageLine=%s">' . _('Edit') . '</a></td>
				<td><a href="%s?delete=%s">' . _('Delete') . '</a></td>
				</tr>',
				$myrow[1],
				$myrow[2],
				$myrow[3],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
				$myrow[0],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
				$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</table>';
	if (DB_num_rows($result)==0){
		echo '<div class="centre">
				<input tabindex="1" type="submit" name="NewEDIInvMsg" value="' . _('Create New EDI Invoice Message From Default Template') . '" />
			</div>';
	}
} //end of ifs SelectedLine is not set




if (isset($SelectedMessageLine)) {
	//editing an existing message line

	$sql = "SELECT messagetype,
			partnercode,
			section,
			sequenceno,
			linetext
		FROM edimessageformat
		WHERE id='" . $SelectedMessageLine . "'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$_POST['Section']  = $myrow['section'];
	$_POST['SequenceNo']  = $myrow['sequenceno'];
	$_POST['LineText']  = $myrow['linetext'];

	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?MessageType=INVOIC&amp;PartnerCode=' . $myrow['partnercode'] . '">' . _('Review Message Lines') . '</a></div>';

	echo '<input type="hidden" name="SelectedMessageLine" value="' . $SelectedMessageLine . '" />';
	echo '<input type="hidden" name="MessageType" value="' . $myrow['messagetype'] . '" />';
	echo '<input type="hidden" name="PartnerCode" value="' . $myrow['partnercode'] . '" />';
} else { //end of if $SelectedMessageLine only do the else when a new record is being entered
	echo '<input type="hidden" name="MessageType" value="' . $MessageType . '" />';
	echo '<input type="hidden" name="PartnerCode" value="' . $PartnerCode . '" />';
}

echo '<fieldset>
		<legend>', _('Message Format Details'), '</legend>';

if (isset($myrow['messagetype']) and $myrow['messagetype']!='') {
	echo '<field><th colspan="2">' . _('Definition of') . ' ' . $myrow['messagetype'] . ' ' . _('for') . ' ' . $myrow['partnercode'] . '</th></field>';
}

echo '<field>
		<label for="Section">' . _('Section') . ':</label>';
echo '<select tabindex="2" name="Section">';

if ($_POST['Section']=='Heading') {
	echo '<option selected="selected" value="Heading">' . _('Heading') . '</option>';
} else {
	echo '<option value="Heading">' . _('Heading') . '</option>';
}

if (isset($_POST['Section']) AND $_POST['Section']=='Detail') {
	echo '<option selected="selected" value="Detail">' . _('Detail') . '</option>';
} else {
	echo '<option value="Detail">' . _('Detail') . '</option>';
}
if (isset($_POST['Section']) AND $_POST['Section']=='Summary') {
	echo '<option selected="selected" value="Summary">' . _('Summary') . '</option>';
} else {
	echo '<option value="Summary">' . _('Summary') . '</option>';
}

echo '</select>';

if (!isset($_POST['SequenceNo'])) {$_POST['SequenceNo']='';}
if (!isset($_POST['LineText'])) {$_POST['LineText']='';}


echo '</field>';

echo '<field>
		<label for="SequenceNo">Sequence Number:</label>
		<input tabindex="3" type="text" name="SequenceNo" size="3" maxlength="3" value="'.$_POST['SequenceNo'].'" />
	</field>';

echo '<field>
		<label for="LineText">' .  _('Line Text') . ':'  . '</label>
		<input tabindex="4" type="text" name="LineText" size="50" maxlength="50" value="'.$_POST['LineText'] .'" />
	</field>';

echo '</fieldset>';

if (isset($_GET['SelectedMessageLine'])) {
	echo '<div class="centre">
			<input tabindex="5" type="submit" name="update" value="'.  _('Update Information'). '" />
		</div>';
} else {
	echo '<div class="centre">
			<input tabindex="5" type="submit" name="submit" value="'.  _('Enter Information'). '" />
		</div>';
}
echo '</form>';

include('includes/footer.php');
?>