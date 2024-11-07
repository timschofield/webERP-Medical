<?php

include('includes/session.php');
if (isset($_POST['SampleDate'])){$_POST['SampleDate'] = ConvertSQLDate($_POST['SampleDate']);};
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);};
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);};
$Title = _('Select QA Samples');
$ViewTopic= 'QualityAssurance';// Filename in ManualContents.php's TOC.
$BookMark = 'QA_Samples';// Anchor's id in the manual's html document.
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['SelectedSampleID'])){
	$SelectedSampleID =mb_strtoupper($_GET['SelectedSampleID']);
} elseif(isset($_POST['SelectedSampleID'])){
	$SelectedSampleID =mb_strtoupper($_POST['SelectedSampleID']);
}

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['LotNumber'])) {
	$LotNumber = $_GET['LotNumber'];
} elseif (isset($_POST['LotNumber'])) {
	$LotNumber = $_POST['LotNumber'];
}
if (isset($_GET['SampleID'])) {
	$SampleID = $_GET['SampleID'];
} elseif (isset($_POST['SampleID'])) {
	$SampleID = $_POST['SampleID'];
}
if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date(($_SESSION['DefaultDateFormat']), Mktime(0, 0, 0, Date('m'), Date('d')-15, Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedSampleID)) {

		$sql = "SELECT prodspeckey,
						lotkey,
						identifier,
						comments,
						cert,
						sampledate
				FROM qasamples
				WHERE sampleid='".$SelectedSampleID."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['ProdSpecKey'] = $myrow['prodspeckey'];
		$_POST['LotKey'] = $myrow['lotkey'];
		$_POST['Identifier'] = $myrow['identifier'];
		$_POST['Comments'] = $myrow['comments'];
		$_POST['SampleDate'] = ConvertSQLDate($myrow['sampledate']);
		$_POST['Cert'] = $myrow['cert'];

		echo '<input type="hidden" name="SelectedSampleID" value="' . $SelectedSampleID . '" />';
		echo '<fieldset>
				<legend>', _('Edit QA Sample Details'), '</legend>
				<field>
					<label>' . _('Sample ID') . ':</label>
					<fieldtext>' . str_pad($SelectedSampleID,10,'0',STR_PAD_LEFT)  . '</fieldtext>
				</field>';

		echo '<field>
				<label>' . _('Specification') . ':</label>
				<fieldtext>' . $_POST['ProdSpecKey']. '</fieldtext>
			</field>
			<field>
				<label>' . _('Lot') . ':</label>
				<fieldtext>' . $_POST['LotKey']. '</fieldtext>
			</field>
			<field>
				<label>' . _('Identifier') . ':</label>
				<input type="text" name="Identifier" size="10" maxlength="10" value="' . $_POST['Identifier']. '" />
			</field>
			<field>
				<label>' . _('Comments') . ':</label>
				<input type="text" name="Comments" size="30" maxlength="255" value="' . $_POST['Comments']. '" />
			</field>
			<field>
				<label>' . _('Sample Date') . ':</label>
				<input type="date" name="SampleDate" size="10" maxlength="10" value="' . FormatDateForSQL($_POST['SampleDate']). '" />
			</field>
			<field>
				<label>' . _('Use for Cert?') . ':</label>
				<select name="Cert">';
		if ($_POST['Cert']==1){
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		} else {
			echo '<option value="1">' . _('Yes') . '</option>';
		}
		if ($_POST['Cert']==0){
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
		} else {
			echo '<option value="0">' . _('No') . '</option>';
		}
		echo '</select>
			</field>
		</fieldset>
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Enter Information') . '" />
			</div>
			</form>';

	} else { //end of if $SelectedSampleID only do the else when a new record is being entered
		if (!isset($_POST['Cert'])) {
			$_POST['Cert']=0;
		}
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<fieldset>
				<legend>', _('Create QA Sample Details'), '</legend>';
		$SQLSpecSelect="SELECT DISTINCT(keyval),
								description
							FROM prodspecs LEFT OUTER JOIN stockmaster
							ON stockmaster.stockid=prodspecs.keyval";

		$ResultSelection=DB_query($SQLSpecSelect);
		echo '<field>
				<label for="ProdSpecKey">' . _('Specification') . ':</label>';
		echo '<select name="ProdSpecKey">';
		while ($MyRowSelection=DB_fetch_array($ResultSelection)){
			echo '<option value="' . $MyRowSelection['keyval'] . '">' . $MyRowSelection['keyval'].' - ' .htmlspecialchars($MyRowSelection['description'], ENT_QUOTES,'UTF-8', false)  . '</option>';
		}
		echo '</select>
			</field>
			<field>
				<label for="LotKey">' . _('Lot') . ':</label>
				<input type="text" required="required" name="LotKey" size="25" maxlength="25" value="' . (isset($_POST['LotKey'])? $_POST['LotKey']:'') . '" />
			</field>
			<field>
				<label for="Identifier">' . _('Identifier') . ':</label>
				<input type="text" name="Identifier" size="10" maxlength="10" value="' . (isset($_POST['Identifier'])? $_POST['Identifier']:'') . '" />
			</field>
			<field>
				<label for="Comments">' . _('Comments') . ':</label>
				<input type="text" name="Comments" size="30" maxlength="255" value="' . (isset($_POST['Comments'])? $_POST['Comments']:'') . '" />
			</field>
			<field>
				<label for="Cert">' . _('Use for Cert?') . ':</label>
				<select name="Cert">';
		if ($_POST['Cert']==1){
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		} else {
			echo '<option value="1">' . _('Yes') . '</option>';
		}
		if ($_POST['Cert']==0){
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
		} else {
			echo '<option value="0">' . _('No') . '</option>';
		}
		echo '</select>
			</field>';
		echo '<field>
				<label for="DuplicateOK">' . _('Duplicate for Lot OK?') . ':</label>
				<select name="DuplicateOK">';
		if ($_POST['DuplicateOK']==1){
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		} else {
			echo '<option value="1">' . _('Yes') . '</option>';
		}
		if ($_POST['DuplicateOK']==0){
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
		} else {
			echo '<option value="0">' . _('No') . '</option>';
		}
		echo '</select>
			</field>
			</fieldset>
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Enter Information') . '" />
			</div>
			</form>';
	}
} //end if record deleted no point displaying form to add record

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	if (isset($SelectedSampleID) AND $InputError !=1) {
		//check to see all values are in spec or at least entered
		$result = DB_query("SELECT count(sampleid) FROM sampleresults
							WHERE sampleid = '" . $SelectedSampleID . "'
							AND showoncert='1'
							AND testvalue=''");
		$myrow = DB_fetch_row($result);;
		if($myrow[0]>0 AND $_POST['Cert']=='1') {
			 $_POST['Cert']='0';
			 $msg = _('Test Results have not all been entered.  This Lot is not able to be used for a a Certificate of Analysis');
			 prnMsg($msg , 'error');
		}
		$result = DB_query("SELECT count(sampleid) FROM sampleresults
							WHERE sampleid = '".$SelectedSampleID."'
							AND showoncert='1'
							AND isinspec='0'");
		$myrow = DB_fetch_row($result);;
		if($myrow[0]>0 AND $_POST['Cert']=='1') {
			 $msg = _('Some Results are out of Spec');
			 prnMsg($msg , 'warning');
		}
		$sql = "UPDATE qasamples SET identifier='" . $_POST['Identifier'] . "',
									comments='" . $_POST['Comments'] . "',
									sampledate='" . FormatDateForSQL($_POST['SampleDate']) . "',
									cert='" . $_POST['Cert'] . "'
				WHERE sampleid = '" . $SelectedSampleID . "'";

		$msg = _('QA Sample record for') . ' ' . $SelectedSampleID  . ' ' .  _('has been updated');
		$ErrMsg = _('The update of the QA Sample failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$result = DB_query($sql,$ErrMsg, $DbgMsg);
		prnMsg($msg , 'success');
		if ( $_POST['Cert']==1) {
			$result = DB_query("SELECT prodspeckey, lotkey FROM qasamples
							WHERE sampleid = '".$SelectedSampleID."'");
			$myrow = DB_fetch_row($result);;
			if($myrow[0]>'') {
				$sql = "UPDATE qasamples SET cert='0'
						WHERE sampleid <> '".$SelectedSampleID . "'
						AND prodspeckey='" . $myrow[0] . "'
						AND lotkey='" . $myrow[1] . "'";
				$msg = _('All other samples for this Specification and Lot was marked as Cert=No');
				$ErrMsg = _('The update of the QA Sample failed because');
				$DbgMsg = _('The SQL that was used and failed was');
				$result = DB_query($sql,$ErrMsg, $DbgMsg);
				prnMsg($msg , 'success');
			}
		}

	} else {
		CreateQASample($_POST['ProdSpecKey'],$_POST['LotKey'], $_POST['Identifier'], $_POST['Comments'], $_POST['Cert'], $_POST['DuplicateOK']);
		$SelectedSampleID=DB_Last_Insert_ID('qasamples','sampleid');
		if ($SelectedSampleID > '') {
			$msg = _('Created New Sample');
			prnMsg($msg , 'success');
		}
	}
	unset($SelectedSampleID);
	unset($_POST['ProdSpecKey']);
	unset($_POST['LotKey']);
	unset($_POST['Identifier']);
	unset($_POST['Comments']);
	unset($_POST['Cert']);
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS

	$sql= "SELECT COUNT(*) FROM sampleresults WHERE sampleresults.sampleid='".$SelectedSampleID."'
											AND sampleresults.testvalue > ''";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this Sample ID because there are test results tied to it'),'error');
	} else {
		$sql="DELETE FROM sampleresults WHERE sampleid='" . $SelectedSampleID . "'";
		$ErrMsg = _('The sample results could not be deleted because');
		$result = DB_query($sql,$ErrMsg);
		$sql="DELETE FROM qasamples WHERE sampleid='" . $SelectedSampleID ."'";
		$ErrMsg = _('The QA Sample could not be deleted because');
		$result = DB_query($sql,$ErrMsg);
		echo $sql;
		prnMsg(_('QA Sample') . ' ' . $SelectedSampleID . _('has been deleted from the database'),'success');
		unset ($SelectedSampleID);
		unset($delete);
		unset ($_GET['delete']);
	}
}

if (!isset($SelectedSampleID)) {

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if (isset($_POST['ResetPart'])) {
		unset($SelectedStockItem);
	}

	if (isset($SampleID) AND $SampleID != '') {
		if (!is_numeric($SampleID)) {
			prnMsg(_('The Sample ID entered') . ' <U>' . _('MUST') . '</U> ' . _('be numeric'), 'error');
			unset($SampleID);
		} else {
			echo _('Sample ID') . ' - ' . $SampleID;
		}
	}
	if (!Is_Date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid From Date'),'error');
		$_POST['FromDate']=Date(($_SESSION['DefaultDateFormat']), mktime(0, 0, 0, Date('m'), Date('d')-15, Date('Y')));
	}
	if (!Is_Date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'),'error');
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}
	if (isset($_POST['SearchParts'])) {
		if ($_POST['Keywords'] AND $_POST['StockCode']) {
			prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
		}
		if ($_POST['Keywords']) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) as qoh,
					stockmaster.units,
				FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview=1
				WHERE stockmaster.description " . LIKE  . " '" . $SearchString ."'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
				ORDER BY stockmaster.stockid";
		} elseif ($_POST['StockCode']) {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
				INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview=1
				WHERE stockmaster.stockid " . LIKE  . " '%" . $_POST['StockCode'] . "%'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
				ORDER BY stockmaster.stockid";
		} elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster INNER JOIN locstock ON stockmaster.stockid = locstock.stockid
				INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview =1
				WHERE stockmaster.categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
				ORDER BY stockmaster.stockid";
		}

		$ErrMsg = _('No stock items were returned by the SQL because');
		$DbgMsg = _('The SQL used to retrieve the searched parts was');
		$StockItemsResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	}

	if (true or !isset($LotNumber) or $LotNumber == '') { //revisit later, right now always show all inputs
		if (!isset($LotNumber)){
			$LotNumber ='';
		}
		if (!isset($SampleID)){
			$SampleID='';
		}
		if (isset($SelectedStockItem)) {
			echo _('For the part') . ':<b>' . $SelectedStockItem . '</b> ' . _('and') . ' <input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';
		}
		echo '<fieldset>
				<legend class="search">', _('Search QA Samples'), '</legend>';

		echo '<field>
				<label for="LotNumber">', _('Lot Number') . ':</label>
				<input name="LotNumber" autofocus="autofocus" maxlength="20" size="12" value="' . $LotNumber . '"/>
			</field>
			<field>
				<label for="SampleID">' . _('Sample ID') . ':</label>
				<input name="SampleID" maxlength="10" size="10" value="' . $SampleID . '"/>
			</field>';
		echo '<field>
				<label>', _('From Sample Date') . ':</label>
				<input name="FromDate" size="10" type="date" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
			</field>
			<field' . _('To Sample Date') . ': <input name="ToDate" size="10" type="date" value="' . FormatDateForSQL($_POST['ToDate']) . '" /></field>
			</fieldset>';
		echo '<div class="centre">
				<input type="submit" name="SearchSamples" value="' . _('Search Samples') . '" />
			</div>';
	}
	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$result1 = DB_query($SQL);
	echo '<fieldset>
			<legend class="search">' . _('To search for QA Samples for a specific part use the part selection facilities below') . '</legend>
			<field>
				<label for="StockCat">' . _('Select a stock category') . ':</label>
				<select name="StockCat">';
	while ($myrow1 = DB_fetch_array($result1)) {
		if (isset($_POST['StockCat']) and $myrow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>
		<field>
			<label for="Keywords">' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</label>
			<input type="text" name="Keywords" size="20" maxlength="25" />
		</field>
		<h3>' . _('OR') . ' </h3>
		<field>
			<label for="StockCode">' . _('Enter extract of the') . '<b> ' . _('Stock Code') . '</b>:</label>
			<input type="text" name="StockCode" size="20" maxlength="25" />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="SearchParts" value="' . _('Search Parts Now') . '" />
			<input type="submit" name="ResetPart" value="' . _('Show All') . '" />
		</div>';

	if (isset($StockItemsResult)) {
		echo '<table class="selection">
			<thead>
				<tr>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('On Hand') . '</th>
							<th class="ascending">' . _('Units') . '</th>
				</tr>
			</thead>
			<tbody>';

		while ($myrow = DB_fetch_array($StockItemsResult)) {
			echo '<tr class="striped_row">
				<td><input type="submit" name="SelectedStockItem" value="' . $myrow['stockid'] . '"</td>
				<td>' . $myrow['description'] . '</td>
				<td class="number">' . locale_number_format($myrow['qoh'],$myrow['decimalplaces']) . '</td>
				<td>' . $myrow['units'] . '</td>
				</tr>';
		}
		//end of while loop
		echo '</tbody></table>';
	}
	//end if stock search results to show
	else {
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		if (isset($LotNumber) AND $LotNumber != '') {
			$SQL = "SELECT sampleid,
							prodspeckey,
							description,
							lotkey,
							identifier,
							createdby,
							sampledate,
							cert
						FROM qasamples
						LEFT OUTER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
						WHERE lotkey='" . filter_number_format($LotNumber) . "'";
		} elseif (isset($SampleID) AND $SampleID != '') {
			$SQL = "SELECT sampleid,
							prodspeckey,
							description,
							lotkey,
							identifier,
							createdby,
							sampledate,
							cert
						FROM qasamples
						LEFT OUTER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
						WHERE sampleid='" . filter_number_format($SampleID) . "'";
		} else {
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT sampleid,
							prodspeckey,
							description,
							lotkey,
							identifier,
							createdby,
							sampledate,
							cert
						FROM qasamples
						INNER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
						WHERE stockid='" . $SelectedStockItem . "'
						AND sampledate>='".$FromDate."'
						AND sampledate <='".$ToDate."'";
			} else {
				$SQL = "SELECT sampleid,
							prodspeckey,
							description,
							lotkey,
							identifier,
							createdby,
							sampledate,
							comments,
							cert
						FROM qasamples
						LEFT OUTER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
						WHERE sampledate>='".$FromDate."'
						AND sampledate <='".$ToDate."'";
			} //no stock item selected
		} //end no sample id selected
		$ErrMsg = _('No QA samples were returned by the SQL because');
		$SampleResult = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($SampleResult) > 0) {

			echo '<table cellpadding="2" width="90%" class="selection">
				<thead>
					<tr>
								<th class="ascending">' . _('Enter Results') . '</th>
								<th class="ascending">' . _('Specification') . '</th>
								<th class="ascending">' . _('Description') . '</th>
								<th class="ascending">' . _('Lot / Serial') . '</th>
								<th class="ascending">' . _('Identifier') . '</th>
								<th class="ascending">' . _('Created By') . '</th>
								<th class="ascending">' . _('Sample Date') . '</th>
								<th class="ascending">' . _('Comments') . '</th>
								<th class="ascending">' . _('Cert Allowed') . '</th>
					</tr>
				</thead>
				<tbody>';

			while ($myrow = DB_fetch_array($SampleResult)) {
				$ModifySampleID = $RootPath . '/TestPlanResults.php?SelectedSampleID=' . $myrow['sampleid'];
				$Edit = '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedSampleID=' . $myrow['sampleid'] .'">' . _('Edit') .'</a>';
				$Delete = '<a href="' .htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  .'?delete=yes&amp;SelectedSampleID=' . $myrow['sampleid'].'" onclick="return confirm(\'' . _('Are you sure you wish to delete this Sample ID ?') . '\');">' . _('Delete').'</a>';
				$FormatedSampleDate = ConvertSQLDate($myrow['sampledate']);

				//echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?CopyTest=yes&amp;SelectedSampleID=' .$SelectedSampleID .'">' . _('Copy These Results') . '</a></div>';
				//echo '<div class="centre"><a target="_blank" href="'. $RootPath . '/PDFTestPlan.php?SelectedSampleID=' .$SelectedSampleID .'">' . _('Print Testing Worksheet') . '</a></div>';
				if ($myrow['cert']==1) {
					$CertAllowed='<a target="_blank" href="'. $RootPath . '/PDFCOA.php?LotKey=' .$myrow['lotkey'] .'&ProdSpec=' .$myrow['prodspeckey'] .'">' . _('Yes') . '</a>';
				} else {
					$CertAllowed=_('No');
				}

				echo '<tr class="striped_row">
						<td><a href="' . $ModifySampleID . '">' . str_pad($myrow['sampleid'],10,'0',STR_PAD_LEFT) . '</a></td>
						<td>' . $myrow['prodspeckey'] . '</td>
						<td>' . $myrow['description'] . '</td>
						<td>' . $myrow['lotkey'] . '</td>
						<td>' .  $myrow['identifier']  . '</td>
						<td>' .  $myrow['createdby']  . '</td>
						<td>' . $FormatedSampleDate . '</td>
						<td>' . $myrow['comments'] . '</td>
						<td>' . $CertAllowed . '</td>
						<td>' . $Edit . '</td>
						<td>' . $Delete . '</td>
						</tr>';
			} //end of while loop
			echo '</tbody></table>';
		} // end if Pick Lists to show
	}
	echo '</div>
		  </form>';
} //end of ifs and buts!

if (isset($SelectedSampleID)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Samples') . '</a></div>';

}

include('includes/footer.php');
?>