<?php
/* $Id: UnitsOfMeasure.php 4567 2011-05-15 04:34:49Z daintree $*/

include('includes/session.inc');

$title = _('Departamentos');

include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' .
		_('Top Sales Order Search') . '" alt="" />' . ' ' . $title . '</p>';

if ( isset($_GET['SelectedMeasureID']) )
	$SelectedMeasureID = $_GET['SelectedMeasureID'];
elseif (isset($_POST['SelectedMeasureID']))
	$SelectedMeasureID = $_POST['SelectedMeasureID'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strpos($_POST['MeasureName'],'&')>0 OR strpos($_POST['MeasureName'],"'")>0) {
		$InputError = 1;
		prnMsg( _('La descripcion del departamento no debe contener el caracter') . " '&' " . _('or the character') ." '",'error');
	}
	if (trim($_POST['MeasureName']) == '') {
		$InputError = 1;
		prnMsg( _('El Nombre del Departamento no debe estar vacio'), 'error');
	}

	if (isset($_POST['SelectedMeasureID']) AND $_POST['SelectedMeasureID']!='' AND $InputError !=1) {
 

		/*SelectedMeasureID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM departments
				WHERE departmentid <> '" . $SelectedMeasureID ."'
				AND description ".LIKE." '" . $_POST['MeasureName'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('No se Puede Registrar un departamento con el mismo nombre.'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very carefull here
			// idealy this is one of those sets that should be in a stored procedure simce even the checks are
			// relavant
			$sql = "SELECT description FROM departments
				WHERE departmentid = '" . $SelectedMeasureID . "'";
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$OldMeasureName = $myrow[0];
				$sql = array();
				$sql[] = "UPDATE departments
					SET description='" . $_POST['MeasureName'] . "'
					WHERE description ".LIKE." '".$OldMeasureName."'";
			} else {
				$InputError = 1;
				prnMsg( _('El Departamento no existe.'),'error');
			}
		}
		$msg = _('El Departamento fue modificado');
	} elseif ($InputError !=1) {
		/*SelectedMeasureID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM departments
				WHERE description " .LIKE. " '".$_POST['MeasureName'] ."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('Existe un Departamento Con el nombre indicado.'),'error');
		} else {
			$sql = "INSERT INTO departments (
						description )
				VALUES (
					'" . $_POST['MeasureName'] ."'
					)";
		}
		$msg = _('Se Registro un nuevo departamento');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin($db);
			$tmpErr = _('No se pudo registrar el departamento');
			$tmpDbg = _('The sql that failed was') . ':';
			foreach ($sql as $stmt ) {
				$result = DB_query($stmt,$db, $tmpErr,$tmpDbg,true);
				if(!$result) {
					$InputError = 1;
					break;
				}
			}
			if ($InputError!=1){
				$result = DB_Txn_Commit($db);
			} else {
				$result = DB_Txn_Rollback($db);
			}
		} else {
			$result = DB_query($sql,$db);
		}
		prnMsg($msg,'success');
	}
	unset ($SelectedMeasureID);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureName']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$sql = "SELECT description FROM departments
		WHERE departmentid = '" . $SelectedMeasureID . "'";
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('No se Puede Eliminar este Departamento'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldMeasureName = $myrow[0];
		$sql= "SELECT COUNT(*) FROM dispatch,departments WHERE dispatch.departmentid=departments.departmentid  and description ".LIKE." '" . $OldMeasureName . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se Puede Eliminar este departamento'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('Articulos relacionados con este departamento') . '</font>';
		} else {
			$sql="DELETE FROM departments WHERE description ".LIKE."'" . $OldMeasureName . "'";
			$result = DB_query($sql,$db);
			prnMsg( $OldMeasureName . ' ' . _('El departamento fue eliminado') . '!','success');
		}
	} //end if account group used in GL accounts
	unset ($SelectedMeasureID);
	unset ($_GET['SelectedMeasureID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureID']);
	unset ($_POST['MeasureName']);
}

 if (!isset($SelectedMeasureID)) {

/* An unit of measure could be posted when one has been edited and is being updated
  or GOT when selected for modification
  SelectedMeasureID will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT departmentid,
			description
			FROM departments
			ORDER BY departmentid";

	$ErrMsg = _('No Existen Departamentos Creados');
	$result = DB_query($sql,$db,$ErrMsg);

	echo '<table class="selection">
			<tr>
				<th>' . _('Departamentos') . '</th>
			</tr>';

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		echo '<td>' . $myrow[1] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?SelectedMeasureID=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?SelectedMeasureID=' . $myrow[0] . '&delete=1">' . _('Delete') .'</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';
} //end of ifs and buts!


if (isset($SelectedMeasureID)) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Verificar Departamentos') . '</a></div>';
}

echo '<p>';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedMeasureID)) {
		//editing an existing section

		$sql = "SELECT departmentid,
				description
				FROM departments
				WHERE departmentid='" . $SelectedMeasureID . "'";

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('No se pudo realizar la consulta por favor intente de nuevo.'),'warn');
			unset($SelectedMeasureID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['MeasureID'] = $myrow['departmentid'];
			$_POST['MeasureName']  = $myrow['description'];

			echo '<input type="hidden" name="SelectedMeasureID" value="' . $_POST['MeasureID'] . '">';
			echo '<table class="selection">';
		}

	}  else {
		$_POST['MeasureName']='';
		echo '<table>';
	}
	echo '<tr>
		<td>' . _('Departamento') . ':' . '</td>
		<td><input type="text" name="MeasureName" size="30" maxlength="30" value="' . $_POST['MeasureName'] . '"></td>
		</tr>';
	echo '</table>';

	echo '<div class="centre"><input type="submit" name="Submit" value=' . _('Enter Information') . '></div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>