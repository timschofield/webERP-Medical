<?php

/* $Id: MRP.php 7959 2018-02-13 04:04:59Z turbopt $*/

include('includes/session.php');
$Title = _('Run MRP Calculation');
$ViewTopic= 'MRP';
$BookMark = 'MRP_Overview';
include('includes/header.php');
if (isset($_POST['submit'])) {

	if (!isset($_POST['Leeway']) OR !is_numeric(filter_number_format($_POST['Leeway']))) {
		$_POST['Leeway'] = 0;
	}

	// MRP - Create levels table based on bom
	echo '<br />'  ._('Start time') . ': ' . date('h:i:s') . '<br />';
	echo '<br />' . _('Initialising tables .....') . '<br />';
	flush();
	$result = DB_query("DROP TABLE IF EXISTS tempbom");
	$result = DB_query("DROP TABLE IF EXISTS passbom");
	$result = DB_query("DROP TABLE IF EXISTS passbom2");
	$result = DB_query("DROP TABLE IF EXISTS bomlevels");
	$result = DB_query("DROP TABLE IF EXISTS levels");

	$sql = "CREATE TEMPORARY TABLE passbom (part char(20),
											sortpart text) DEFAULT CHARSET=utf8";
	$ErrMsg = _('The SQL to create passbom failed with the message');
	$result = DB_query($sql,$ErrMsg);

	$sql = "CREATE TEMPORARY TABLE tempbom (parent char(20),
											component char(20),
											sortpart text,
											level int) DEFAULT CHARSET=utf8";
	$result = DB_query($sql,_('Create of tempbom failed because'));
	// To create levels, first, find parts in bom that are top level assemblies.
	// Do this by doing a LEFT JOIN from bom to bom (as bom2), linking
	// bom.PARENT to bom2.COMPONENT and using WHERE bom2.COMPONENT IS NULL
	// Put those top level assemblies in passbom, use COMPONENT in passbom
	// to link to PARENT in bom to find next lower level and accumulate
	// those parts into tempbom

	prnMsg(_('Creating first level'),'info');
	flush();
	// This finds the top level
	$sql = "INSERT INTO passbom (part, sortpart)
					   SELECT bom.component AS part,
							  CONCAT(bom.parent,'%',bom.component) AS sortpart
							  FROM bom LEFT JOIN bom as bom2
							  ON bom.parent = bom2.component
					  WHERE bom2.component IS NULL";
	$result = DB_query($sql);

	$lctr = 2;
	// $lctr is the level counter
	$sql = "INSERT INTO tempbom (parent, component, sortpart, level)
			  SELECT bom.parent AS parent, bom.component AS component,
					 CONCAT(bom.parent,'%',bom.component) AS sortpart,
					 '". $lctr. "' as level
					 FROM bom LEFT JOIN bom as bom2 ON bom.parent = bom2.component
			  WHERE bom2.component IS NULL";
	$result = DB_query($sql);
	//echo "<br />sql is $sql<br />";
	// This while routine finds the other levels as long as $compctr - the
	// component counter - finds there are more components that are used as
	// assemblies at lower levels
	prnMsg(_('Creating other levels'),'info');
	flush();
	$compctr = 1;
	while ($compctr > 0) {
		$lctr++;
		$sql = "INSERT INTO tempbom (parent, component, sortpart, level)
		  SELECT bom.parent AS parent, bom.component AS component,
			 CONCAT(passbom.sortpart,'%',bom.component) AS sortpart,
			 '" .$lctr . "' as level
			 FROM bom,passbom WHERE bom.parent = passbom.part";
		$result = DB_query($sql);

		$result = DB_query("DROP TABLE IF EXISTS passbom2");
		$result = DB_query("ALTER TABLE passbom RENAME AS passbom2");
		$result = DB_query("DROP TABLE IF EXISTS passbom");

		$sql = "CREATE TEMPORARY TABLE passbom (part char(20),
												sortpart text) DEFAULT CHARSET=utf8";
		$result = DB_query($sql);

		$sql = "INSERT INTO passbom (part, sortpart)
				   SELECT bom.component AS part,
						  CONCAT(passbom2.sortpart,'%',bom.component) AS sortpart
						  FROM bom,passbom2
				   WHERE bom.parent = passbom2.part";
		$result = DB_query($sql);


		$sql = "SELECT COUNT(*) FROM bom
						INNER JOIN passbom ON bom.parent = passbom.part
						GROUP BY bom.parent";
		$result = DB_query($sql);

		$myrow = DB_fetch_row($result);
		$compctr = $myrow[0];

	} // End of while $compctr > 0

	prnMsg(_('Creating bomlevels table'),'info');
	flush();
	$sql = "CREATE TEMPORARY TABLE bomlevels (
									part char(20),
									level int) DEFAULT CHARSET=utf8";
	$result = DB_query($sql);

	// Read tempbom and split sortpart into separate parts. For each separate part, calculate level as
	// the sortpart level minus the position in the @parts array of the part. For example, the first
	// part in the array for a level 4 sortpart would be created as a level 3 in levels, the fourth
	// and last part in sortpart would have a level code of zero, meaning it has no components

	$sql = "SELECT * FROM tempbom";
	$result = DB_query($sql);
	while ($myrow=DB_fetch_array($result)) {
			$Parts = explode('%',$myrow['sortpart']);
			$Level = $myrow['level'];
			$ctr = 0;
			foreach ($Parts as $Part) {
			   $ctr++;
			   $newlevel = $Level - $ctr;
			   $sql = "INSERT INTO bomlevels (part, level) VALUES('" . $Part . "','" . $newlevel . "')";
			   $result2 = DB_query($sql);
			} // End of foreach
	}  //end of while loop

	prnMsg(_('Creating levels table'),'info');
	flush();
	// Create levels from bomlevels using the highest level number found for a part

	$sql = "CREATE TABLE levels (
							part char(20),
							level int,
							leadtime smallint(6) NOT NULL default '0',
							pansize double NOT NULL default '0',
							shrinkfactor double NOT NULL default '0',
							eoq double NOT NULL default '0') DEFAULT CHARSET=utf8";
	$result = DB_query($sql);
	$sql = "INSERT INTO levels (part,
							level,
							leadtime,
							pansize,
							shrinkfactor,
							eoq)
		   SELECT bomlevels.part,
				   MAX(bomlevels.level),
				   0,
				   pansize,
				   shrinkfactor,
				   stockmaster.eoq
			 FROM bomlevels
			   	 INNER JOIN stockmaster ON bomlevels.part = stockmaster.stockid
			 GROUP BY bomlevels.part,
					  pansize,
					  shrinkfactor,
					  stockmaster.eoq";
	$result = DB_query($sql);
	$sql = "ALTER TABLE levels ADD INDEX part(part)";
	$result = DB_query($sql);

	// Create levels records with level of zero for all parts in stockmaster that
	// are not in bom

	$sql = "INSERT INTO levels (part,
							level,
							leadtime,
							pansize,
							shrinkfactor,
							eoq)
			SELECT  stockmaster.stockid AS part,
					0,
					0,
					stockmaster.pansize,
					stockmaster.shrinkfactor,
					stockmaster.eoq
			FROM stockmaster
			LEFT JOIN levels ON stockmaster.stockid = levels.part
			WHERE levels.part IS NULL";
	$result = DB_query($sql);

	// Update leadtime in levels from purchdata. Do it twice so can make sure leadtime from preferred
	// vendor is used
	$sql = "UPDATE levels,purchdata
					SET levels.leadtime = purchdata.leadtime
					WHERE levels.part = purchdata.stockid
					AND purchdata.leadtime > 0";
	$result = DB_query($sql);
	$sql = "UPDATE levels,purchdata
						SET levels.leadtime = purchdata.leadtime
					WHERE levels.part = purchdata.stockid
					AND purchdata.preferred = 1
					AND purchdata.leadtime > 0";
	$result = DB_query($sql);

	prnMsg(_('Levels table has been created'),'info');
	flush();

	// Get rid if temporary tables
	$sql = "DROP TABLE IF EXISTS tempbom";
	//$result = DB_query($sql);
	$sql = "DROP TABLE IF EXISTS passbom";
	//$result = DB_query($sql);
	$sql = "DROP TABLE IF EXISTS passbom2";
	//$result = DB_query($sql);
	$sql = "DROP TABLE IF EXISTS bomlevels";
	//$result = DB_query($sql);

	// In the following section, create mrprequirements from open sales orders and
	// mrpdemands
	prnMsg(_('Creating requirements table'),'info');
	flush();
	$result = DB_query("DROP TABLE IF EXISTS mrprequirements");
	// directdemand is 1 if demand is directly for this part, is 0 if created because have netted
	// out supply and demands for a top level part and determined there is still a net
	// requirement left and have to pass that down to the BOM parts using the
	// CreateLowerLevelRequirement() function. Mostly do this so can distinguish the type
	// of requirements for the MRPShortageReport so don't show double requirements.
	$sql = "CREATE TABLE mrprequirements (	part char(20),
											daterequired date,
											quantity double,
											mrpdemandtype varchar(6),
											orderno int(11),
											directdemand smallint,
											whererequired char(20),
											KEY part (part)
															) DEFAULT CHARSET=utf8";
	$result = DB_query($sql,_('Create of mrprequirements failed because'));

	prnMsg(_('Loading requirements from sales orders'),'info');
	flush();
	$sql = "INSERT INTO mrprequirements	(part,
										 daterequired,
										 quantity,
										 mrpdemandtype,
										 orderno,
										 directdemand,
										 whererequired)
							   SELECT stkcode,
									  itemdue,
									  (quantity - qtyinvoiced) AS netqty,
									  'SO',
									  salesorderdetails.orderno,
									  '1',
									  stkcode
							  FROM salesorders INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
								INNER JOIN stockmaster
								ON stockmaster.stockid = salesorderdetails.stkcode
							  WHERE stockmaster.discontinued = 0
							  AND (quantity - qtyinvoiced) > 0
							  AND salesorderdetails.completed = 0
							  AND salesorders.quotation = 0";
	$result = DB_query($sql);

	prnMsg(_('Loading requirements from work orders'),'info');
	flush();
	// Definition of demand from SelectProduct.php
	$sql = "INSERT INTO mrprequirements	(part,
										 daterequired,
										 quantity,
										 mrpdemandtype,
										 orderno,
										 directdemand,
										 whererequired)
							   SELECT worequirements.stockid,
									workorders.requiredby,
									(qtypu*woitems.qtyreqd +
									SUM(CASE WHEN stockmoves.qty IS NOT NULL
										THEN stockmoves.qty
										ELSE 0
										END))
									AS netqty,
									'WO',
									woitems.wo,
									'1',
									parentstockid
								FROM woitems
									INNER JOIN worequirements
										ON woitems.stockid=worequirements.parentstockid
									INNER JOIN workorders
									  ON woitems.wo=workorders.wo
									  AND woitems.wo=worequirements.wo
									  INNER JOIN stockmaster
										ON woitems.stockid = stockmaster.stockid
										LEFT JOIN stockmoves ON (stockmoves.stockid = worequirements.stockid AND stockmoves.reference=woitems.wo AND type=28)
								GROUP BY workorders.wo,worequirements.stockid,workorders.requiredby,woitems.qtyreqd,worequirements.qtypu,woitems.wo,worequirements.stockid,workorders.closed,stockmaster.discontinued,stockmoves.reference,workorders.closed
								HAVING workorders.closed=0
								AND stockmaster.discontinued = 0
								AND netqty > 0";
	$result = DB_query($sql);

	if ($_POST['UseMRPDemands'] == 'y') {
		$sql = "INSERT INTO mrprequirements	(part,
											 daterequired,
											 quantity,
											 mrpdemandtype,
											 orderno,
											 directdemand,
											 whererequired)
								   SELECT mrpdemands.stockid,
										  mrpdemands.duedate,
										  mrpdemands.quantity,
										  mrpdemands.mrpdemandtype,
										  mrpdemands.demandid,
										  '1',
										  mrpdemands.stockid
									 FROM mrpdemands, stockmaster
									 WHERE mrpdemands.stockid = stockmaster.stockid
										AND stockmaster.discontinued = 0";
		$result = DB_query($sql);
		prnMsg(_('Loading requirements based on mrpdemands'),'info');
		flush();
	}
	if ($_POST['UseRLDemands'] == 'y') {
		$sql = "INSERT INTO mrprequirements	(part,
											 daterequired,
											 quantity,
											 mrpdemandtype,
											 orderno,
											 directdemand,
											 whererequired)
								   SELECT locstock.stockid,
										  '" . date('Y-m-d') . "',
										  (locstock.reorderlevel - locstock.quantity) AS reordqty,
										  'REORD',
										  '1',
										  '1',
										  locstock.stockid
									 FROM locstock, stockmaster
									 WHERE stockmaster.stockid = locstock.stockid
										AND stockmaster.discontinued = 0
										AND reorderlevel - quantity > 0";
		$result = DB_query($sql);
		prnMsg(_('Loading requirements based on reorder level'),'info');
		flush();
	}

	// In the following section, create mrpsupplies from open purchase orders,
	// open work orders, and current quantity onhand from locstock
	prnMsg(_('Creating supplies table'),'info');
	flush();
	$result = DB_query("DROP TABLE IF EXISTS mrpsupplies");
	// updateflag is set to 1 in UpdateSupplies if change date when matching requirements to
	// supplies. Actually only change update flag in the array created from mrpsupplies
	$sql = "CREATE TABLE mrpsupplies (	id int(11) NOT NULL auto_increment,
										part char(20),
										duedate date,
										supplyquantity double,
										ordertype varchar(6),
										orderno int(11),
										mrpdate date,
										updateflag smallint(6),
										PRIMARY KEY (id)) DEFAULT CHARSET=utf8";
	$result = DB_query($sql,_('Create of mrpsupplies failed because'));

	prnMsg(_('Loading supplies from purchase orders'),'info');
	flush();
	$sql = "INSERT INTO mrpsupplies	(id,
									 part,
									 duedate,
									 supplyquantity,
									 ordertype,
									 orderno,
									 mrpdate,
									 updateflag)
						   SELECT Null,
								  purchorderdetails.itemcode,
								  purchorderdetails.deliverydate,
								  (quantityord - quantityrecd) AS netqty,
								  'PO',
								  purchorderdetails.orderno,
								  purchorderdetails.deliverydate,
								  0
							  FROM purchorderdetails,
								   purchorders
						  WHERE purchorderdetails.orderno = purchorders.orderno
							AND purchorders.status != 'Cancelled'
							AND purchorders.status != 'Rejected'
							AND purchorders.status != 'Completed'
							AND(quantityord - quantityrecd) > 0
							AND purchorderdetails.completed = 0";
	$result = DB_query($sql);

	prnMsg(_('Loading supplies from inventory on hand'),'info');
	flush();
	// Set date for inventory already onhand to 0000-00-00 so it is first in sort
	if ($_POST['location'][0] == 'All') {
		$WhereLocation = ' ';
	} elseif (sizeof($_POST['location']) == 1) {
		$WhereLocation = " AND loccode ='" . $_POST['location'][0] . "' ";
	} else {
		$WhereLocation = " AND loccode IN(";
		$commactr = 0;
		foreach ($_POST['location'] as $key => $value) {
			$WhereLocation .= "'" . $value . "'";
			$commactr++;
			if ($commactr < sizeof($_POST['location'])) {
				$WhereLocation .= ",";
			} // End of if
		} // End of foreach
		$WhereLocation .= ')';
	}
	$sql = "INSERT INTO mrpsupplies	(id,
									 part,
									 duedate,
									 supplyquantity,
									 ordertype,
									 orderno,
									 mrpdate,
									 updateflag)
						   SELECT Null,
								  stockid,
								  '0000-00-00',
								  SUM(quantity),
								  'QOH',
								  1,
								  '0000-00-00',
								  0
							  FROM locstock
							  WHERE quantity > 0 " .
							  $WhereLocation .
						  "GROUP BY stockid";
	$result = DB_query($sql);

	prnMsg(_('Loading supplies from work orders'),'info');
	flush();
	$sql = "INSERT INTO mrpsupplies	(id,
									 part,
									 duedate,
									 supplyquantity,
									 ordertype,
									 orderno,
									 mrpdate,
									 updateflag)
						   SELECT Null,
								  stockid,
								  workorders.requiredby,
								  (woitems.qtyreqd-woitems.qtyrecd) AS netqty,
								  'WO',
								  woitems.wo,
								  workorders.requiredby,
								  0
							  FROM woitems INNER JOIN workorders
								ON woitems.wo=workorders.wo
								WHERE workorders.closed=0
								AND (woitems.qtyreqd-woitems.qtyrecd) > 0";
	$result = DB_query($sql);

	$sql = "ALTER TABLE mrpsupplies ADD INDEX part(part)";
	$result = DB_query($sql);

	// Create mrpplannedorders table to create a record for any unmet requirments
	// In the following section, create mrpsupplies from open purchase orders,
	// open work orders, and current quantity onhand from locstock
	prnMsg(_('Creating planned orders table'),'info');
	flush();
	$result = DB_query("DROP TABLE IF EXISTS mrpplannedorders");
	$sql = "CREATE TABLE mrpplannedorders (id int(11) NOT NULL auto_increment,
											part char(20),
											duedate date,
											supplyquantity double,
											ordertype varchar(6),
											orderno int(11),
											mrpdate date,
											updateflag smallint(6),
											PRIMARY KEY (id)) DEFAULT CHARSET=utf8";
	$result = DB_query($sql,_('Create of mrpplannedorders failed because'));

	// Find the highest and lowest level number
	$sql = "SELECT MAX(level),MIN(level) from levels";
	$result = DB_query($sql);

	$myrow = DB_fetch_row($result);
	$MaxLevel = $myrow[0];
	$MinLevel = $myrow[1];

	// At this point, have all requirements in mrprequirements and all supplies to satisfy
	// those requirements in mrpsupplies.  Starting at the top level, will read all parts one
	// at a time, compare the requirements and supplies to see if have to re-schedule or create
	// planned orders to satisfy requirements. If there is a net requirement from a higher level
	// part, that serves as a gross requirement for a lower level part, so will read down through
	// the Bill of Materials to generate those requirements in function LevelNetting().
	for ($Level = $MaxLevel; $Level >= $MinLevel; $Level--) {
		$sql = "SELECT * FROM levels WHERE level = '" . $Level ."' LIMIT 50000"; //should cover most eventualities!! ... yes indeed :-)

		prnMsg('------ ' . _('Processing level') .' ' . $Level . ' ------','info');
		flush();
		$result = DB_query($sql);
		while ($myrow=DB_fetch_array($result)) {
			LevelNetting($myrow['part'],$myrow['eoq'],$myrow['pansize'],$myrow['shrinkfactor'], $myrow['leadtime']);
		}  //end of while loop
	} // end of for
	echo '<br />' . _('End time') . ': ' . date('h:i:s') . '<br />';

	// Create mrpparameters table
	$sql = "DROP TABLE IF EXISTS mrpparameters";
	$result = DB_query($sql);
	$sql = "CREATE TABLE mrpparameters  (
						runtime datetime,
						location varchar(50),
						pansizeflag varchar(5),
						shrinkageflag varchar(5),
						eoqflag varchar(5),
						usemrpdemands varchar(5),
						userldemands varchar(5),
						leeway smallint) DEFAULT CHARSET=utf8";
	$result = DB_query($sql);
	// Create entry for location field from $_POST['location'], which is an array
	// since multiple locations can be selected
	$commactr = 0;
	$locparm='';
	foreach ($_POST['location'] as $key => $value) {
		$locparm .=  $value ;
		$commactr++;
		if ($commactr < sizeof($_POST['location'])) {
			$locparm .= " - ";
		} // End of if
	} // End of foreach
	$sql = "INSERT INTO mrpparameters (runtime,
									location,
									pansizeflag,
									shrinkageflag,
									eoqflag,
									usemrpdemands,
									userldemands,
									leeway)
									VALUES (CURRENT_TIMESTAMP,
								'" . $locparm . "',
								'" .  $_POST['PanSizeFlag']  . "',
								'" .  $_POST['ShrinkageFlag']  . "',
								'" .  $_POST['EOQFlag']  . "',
								'" .  $_POST['UseMRPDemands']  . "',
								'" .  $_POST['UseRLDemands']  . "',
								'" . filter_number_format($_POST['Leeway']) . "')";
	$result = DB_query($sql);

} else { // End of if submit isset
	// Display form if submit has not been hit
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	// Display parameters from last run
	$SQL = "SHOW TABLES WHERE Tables_in_" . $_SESSION['DatabaseName'] . "='mrpparameters'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$sql = "SELECT * FROM mrpparameters";
		$result = DB_query($sql,'','',false,false);
		$myrow = DB_fetch_array($result);
	}
	if (DB_error_no()==0){


		if (isset($myrow['leeway'])) {
			$Leeway = $myrow['leeway'];
		} else {
			$Leeway = 0;
		}
		$UseMRPDemands = _('No');
		if (isset($myrow['usemrpdemands']) and $myrow['usemrpdemands'] == 'y') {
			 $UseMRPDemands = _('Yes');
		}
		$UseRLDemands = _('No');
		if (isset($myrow['userldemands']) and $myrow['userldemands'] == 'y') {
			 $UseRLDemands = _('Yes');
		}
		$useeoq = _('No');
		if (isset($myrow['eoqflag']) and $myrow['eoqflag'] == 'y') {
			 $useeoq = _('Yes');
		}
		$usepansize = _('No');
		if (isset($myrow['pansizeflag']) and $myrow['pansizeflag'] == 'y') {
			 $usepansize = _('Yes');
		}
		$useshrinkage = _('No');
		if (isset($myrow['shrinkageflag']) and $myrow['shrinkageflag'] == 'y') {
			 $useshrinkage = _('Yes');
		}
		if (!isset($myrow['runtime'])) {
			$myrow['runtime'] = 0;
		}
		if (!isset($myrow['location'])) {
			$myrow['location'] = '';
		}
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<fieldset>
				<legend>' . _('Last Run Details') . '</legend>
				<field>
					<label>' . _('Last Run Time') . ':</label>
					<fieldtext>' . $myrow['runtime'] . '</fieldtext>
				</field>
				<field>
					<label>' . _('Location') . ':</label>
					<fieldtext>' . $myrow['location'] . '</fieldtext>
				</field>
				<field>
					<label>' . _('Days Leeway') . ':</label>
					<fieldtext>' . $Leeway . '</fieldtext>
				</field>
				<field>
					<label>' . _('Use MRP Demands') . ':</label>
					<fieldtext>' . $UseMRPDemands . '</fieldtext>
				</field>
				<field>
					<label>' . _('Use Reorder Level Demands') . ':</label>
					<fieldtext>' . $UseRLDemands . '</fieldtext>
				</field>
				<field>
					<label>' . _('Use EOQ') . ':</label>
					<fieldtext>' . $useeoq . '</fieldtext>
				</field>
				<field>
					<label>' . _('Use Pan Size') . ':</label>
					<fieldtext>' . $usepansize . '</fieldtext>
				</field>
				<field>
					<label>' . _('Use Shrinkage') . ':</label>
					<fieldtext>' . $useshrinkage . '</fieldtext>
				</field>
				</fieldset>';
	}
	echo '<fieldset>
			<legend>' . _('This Run Details') . '</legend>
			<field>
				<label for="Location">' . _('Location') . '</label>
				<select required="required" autofocus="autofocus" name="location[]" multiple="multiple">
					<option value="All" selected="selected">' . _('All') . '</option>';
	 $sql = "SELECT loccode,
				locationname
			   FROM locations";
	$Result = DB_query($sql);
	while ($myrow = DB_fetch_array($Result)) {
		echo '<option value="';
		echo $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	} //end while loop
	echo '</select>
		</field>';
	if (!isset($Leeway)){
		$Leeway =0;
	}

	echo '<field>
			<label for="Leeway">' . _('Days Leeway') . ':</label>
			<input type="text" required="required" name="Leeway" class="integer" size="4" value="' . $Leeway . '" />
		</field>
		<field>
			<label for="UseMRPDemands">' ._('Use MRP Demands?') . ':</label>
			<input type="checkbox" name="UseMRPDemands" value="y" checked="checked" />
		</field>
		<field>
			<label for="UseRLDemands">' ._('Use Reorder Level Demands?') . ':</label>
			<input type="checkbox" name="UseRLDemands" value="y" checked="checked" />
		</field>
		<field>
			<label for="EOQFlag">' ._('Use EOQ?') . ':</label>
			<input type="checkbox" name="EOQFlag" value="y" checked="checked" />
		</field>
		<field>
			<label for="PanSizeFlag">' ._('Use Pan Size?') . ':</label>
			<input type="checkbox" name="PanSizeFlag" value="y" checked="checked" />
		</field>
		<field>
			<label for="ShrinkageFlag">' ._('Use Shrinkage?') . ':</label>
			<input type="checkbox" name="ShrinkageFlag" value="y" checked="checked" />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Run MRP') . '" />
		</div>
		</form>';
}  // End of Main program logic -------------------------------------------------------



function LevelNetting($Part,$eoq,$PanSize,$ShrinkFactor,$LeadTime) {
		// Create an array of mrprequirements and an array of mrpsupplies, then read through
		// them seeing if all requirements are covered by supplies. Create a planned order
		// for any unmet requirements. Change dates if necessary for the supplies.
		//echo '<br />Part is ' . "$Part" . '<br />';

		// Get decimal places from stockmaster for rounding of shrinkage factor
	$sql = "SELECT decimalplaces FROM stockmaster WHERE stockid = '" . $Part . "'";
	$result = DB_query($sql);
	$myrow=DB_fetch_row($result);
	$DecimalPlaces = $myrow[0];

	// Load mrprequirements into $Requirements array
	$sql = "SELECT * FROM mrprequirements WHERE part = '" .$Part. "' ORDER BY daterequired";
	$result = DB_query($sql);
	$Requirements = array();
	$i = 0;
	while ($myrow=DB_fetch_array($result)) {
			array_push($Requirements,$myrow);
			$i++;
	}  //end of while loop

	// Load mrpsupplies into $Supplies array
	$sql = "SELECT * FROM mrpsupplies WHERE part = '" . $Part. "' ORDER BY duedate";
	$result = DB_query($sql);
	$Supplies = array();
	$i = 0;
	while ($myrow=DB_fetch_array($result)) {
			array_push($Supplies,$myrow);
			$i++;
	}  //end of while loop
	// Go through all requirements and check if have supplies to cover them
	$RequirementCount = count($Requirements);
	$SupplyCount = count($Supplies);
	$reqi = 0; //Index for requirements
	$supi = 0; // index for supplies
	$TotalRequirement = 0;
	$TotalSupply = 0;

	if ($RequirementCount > 0 && $SupplyCount > 0) {
			$TotalRequirement += $Requirements[$reqi]['quantity'];
			$TotalSupply += $Supplies[$supi]['supplyquantity'];
		while ($TotalRequirement > 0 && $TotalSupply > 0) {
			$Supplies[$supi]['updateflag'] = 1;
			// ******** Put leeway calculation in here ********
			$DueDate = ConvertSQLDate($Supplies[$supi]['duedate']);
			$ReqDate = ConvertSQLDate($Requirements[$reqi]['daterequired']);
			$DateDiff = DateDiff($DueDate,$ReqDate,'d');
			if ($DateDiff > abs(filter_number_format($_POST['Leeway']))) {
				$sql = "UPDATE mrpsupplies SET mrpdate = '" . $Requirements[$reqi]['daterequired'] .
				   "' WHERE id = '" . $Supplies[$supi]['id'] . "' AND duedate = mrpdate";
				$result = DB_query($sql);
			}
			if ($TotalRequirement > $TotalSupply) {
				$TotalRequirement -= $TotalSupply;
				$Requirements[$reqi]['quantity'] -= $TotalSupply;
				$TotalSupply = 0;
				$Supplies[$supi]['supplyquantity'] = 0;
				$supi++;
				if ($SupplyCount > $supi) {
					$TotalSupply += $Supplies[$supi]['supplyquantity'];
				}
			} elseif ($TotalRequirement < $TotalSupply) {
				$TotalSupply -= $TotalRequirement;
				$Supplies[$supi]['supplyquantity'] -= $TotalRequirement;
				$TotalRequirement = 0;
				$Requirements[$reqi]['quantity'] = 0;
				$reqi++;
				if ($RequirementCount > $reqi) {
					$TotalRequirement += $Requirements[$reqi]['quantity'];
				}
			} else {
				$TotalSupply -= $TotalRequirement;
				$Supplies[$supi]['supplyquantity'] -= $TotalRequirement;
				$TotalRequirement = 0;
				$Requirements[$reqi]['quantity'] = 0;
				$reqi++;
				if ($RequirementCount > $reqi) {
					$TotalRequirement += $Requirements[$reqi]['quantity'];
				}
				$TotalRequirement -= $TotalSupply;
				if(isset($Requirements[$reqi]['quantity'])){
					$Requirements[$reqi]['quantity'] -= $TotalSupply;
				}
				$TotalSupply = 0;
				$Supplies[$supi]['supplyquantity'] = 0;
				$supi++;
				if ($SupplyCount > $supi) {
					$TotalSupply += $Supplies[$supi]['supplyquantity'];
				}
			}
		} // End of while
	} // End of if

	// When get to this part of code, have gone through all requirements, If there is any
	// unmet requirements, create an mrpplannedorder to cover it. Also call the
	// CreateLowerLevelRequirement() function to create gross requirements for lower level parts.

	// There is an excess quantity if the eoq is higher than the actual required amount.
	// If there is a subsuquent requirement, the excess quantity is subtracted from that
	// quantity. For instance, if the first requirement was for 2 and the eoq was 5, there
	// would be an excess of 3; if there was another requirement for 3 or less, the excess
	// would cover it, so no planned order would have to be created for the second requirement.
	$ExcessQty = 0;
	foreach ($Requirements as $key => $row) {
		 $DateRequired[$key] = $row['daterequired'];
	}
	if (count($Requirements)) {
		array_multisort($DateRequired, SORT_ASC, $Requirements);
	}
	foreach($Requirements as $Requirement) {
		// First, inflate requirement if there is a shrinkage factor
		// Should the quantity be rounded?
		if ($_POST['ShrinkageFlag'] == 'y' AND $ShrinkFactor > 0) {
			$Requirement['quantity'] = ($Requirement['quantity'] * 100) / (100 - $ShrinkFactor);
			$Requirement['quantity'] = round($Requirement['quantity'],$DecimalPlaces);
		}
		if ($ExcessQty >= $Requirement['quantity']) {
			$PlannedQty = 0;
			$ExcessQty -= $Requirement['quantity'];
		} else {
			$PlannedQty = $Requirement['quantity'] - $ExcessQty;
			$ExcessQty = 0;
		}
		if ($PlannedQty > 0) {
			if ($_POST['EOQFlag'] == 'y' AND $eoq > $PlannedQty) {
				$ExcessQty = $eoq - $PlannedQty;
				$PlannedQty = $eoq;
			}
			// Pansize calculation here
			// if $PlannedQty not evenly divisible by $PanSize, calculate as $PlannedQty
			// divided by $PanSize and rounded up to the next highest integer and then
			// multiplied by the pansize. For instance, with a planned qty of 17 with a pansize
			// of 5, divide 17 by 5 to get 3 with a remainder of 2, which is rounded up to 4
			// and then multiplied by 5 - the pansize - to get 20
			if ($_POST['PanSizeFlag'] == 'y' AND $PanSize != 0 AND $PlannedQty != 0) {
				$PlannedQty = ceil($PlannedQty / $PanSize) * $PanSize;
			}

			// Calculate required date by subtracting leadtime from top part's required date
		$PartRequiredDate = $Requirement['daterequired'];
			if ((int)$LeadTime>0) {

				$CalendarSQL = "SELECT COUNT(*),cal2.calendardate
						  FROM mrpcalendar
							LEFT JOIN mrpcalendar as cal2
							  ON (mrpcalendar.daynumber - '".$LeadTime."') = cal2.daynumber
						  WHERE mrpcalendar.calendardate = '".$PartRequiredDate."'
							AND cal2.manufacturingflag='1'
							GROUP BY cal2.calendardate";
				$ResultDate = DB_query($CalendarSQL);
				$myrowdate = DB_fetch_array($ResultDate);
				if($myrowdate[0]>0){
					$NewDate = $myrowdate[1];
				} else {//No calendar date available, so use $PartRequiredDate
						$ConvertDate = ConvertSQLDate($PartRequiredDate);
						$DateAdd = DateAdd($ConvertDate,'d',($LeadTime * -1));
						$NewDate = FormatDateForSQL($DateAdd);
				}
				// If can't find date based on manufacturing calendar, use $PartRequiredDate
			}  else {
				// Convert $PartRequiredDate from mysql format to system date format, use that to subtract leadtime
				// from it using DateAdd, convert that date back to mysql format
				$ConvertDate = ConvertSQLDate($PartRequiredDate);
				$DateAdd = DateAdd($ConvertDate,'d',($LeadTime * -1));
				$NewDate = FormatDateForSQL($DateAdd);
		}

		$sql = "INSERT INTO mrpplannedorders (id,
												part,
												duedate,
												supplyquantity,
												ordertype,
												orderno,
												mrpdate,
												updateflag)
											VALUES (NULL,
												'" . $Requirement['part'] . "',
												'" . $NewDate . "',
												'" . $PlannedQty  . "',
												'" . $Requirement['mrpdemandtype']  . "',
												'" . $Requirement['orderno']  . "',
												'" . $NewDate . "',
												'0')";


			$result = DB_query($sql);
			// If part has lower level components, create requirements for them
			$sql = "SELECT COUNT(*) FROM bom
					  WHERE parent ='" . $Requirement['part'] . "'
					  GROUP BY parent";
			$result = DB_query($sql);
			$myrow = DB_fetch_row($result);
			if ($myrow[0] > 0) {
				CreateLowerLevelRequirement($Requirement['part'],$NewDate,
				  $PlannedQty,$Requirement['mrpdemandtype'],$Requirement['orderno'],
				  $Requirement['whererequired']);
			}
		} // End of if $PlannedQty > 0
	} // End of foreach $Requirements

   // If there are any supplies not used and updateflag is zero, those supplies are not
	// necessary, so change date

	foreach($Supplies as $supply) {
		if ($supply['supplyquantity'] > 0  && $supply['updateflag'] == 0) {
			$id = $supply['id'];
			$sql = "UPDATE mrpsupplies SET mrpdate ='2050-12-31' WHERE id = '".$id."'
					  AND ordertype <> 'QOH'";
			$result = DB_query($sql);
		}
	}

} // End of LevelNetting -------------------------------------------------------

function CreateLowerLevelRequirement($TopPart,
									$TopDate,
									$TopQuantity,
									$TopMRPDemandType,
									$TopOrderNo,
									$WhereRequired) {
// Creates an mrprequirement based on the net requirement from the part above it in the bom
	$sql = "SELECT bom.component,
				   bom.quantity,
				   levels.leadtime,
				   levels.eoq
			FROM bom
				 LEFT JOIN levels
				   ON bom.component = levels.part
			WHERE bom.parent = '".$TopPart."'
            AND bom.effectiveafter <= '" . date('Y-m-d') . "'
            AND bom.effectiveto > '" . date('Y-m-d') . "'";
	$ResultBOM = DB_query($sql);
	while ($myrow=DB_fetch_array($ResultBOM)) {
		// Calculate required date by subtracting leadtime from top part's required date
		$LeadTime = $myrow['leadtime'];
		$Component = $myrow['component'];
		$ExtendedQuantity = $myrow['quantity'] * $TopQuantity;
// Commented out the following lines 8/15/09 because the eoq should be considered in the
// LevelNetting() function where $ExcessQty is calculated
//		 if ($myrow['eoq'] > $ExtendedQuantity) {
//			 $ExtendedQuantity = $myrow['eoq'];
//		 }
		$sql = "INSERT INTO mrprequirements (part,
											 daterequired,
											 quantity,
											 mrpdemandtype,
											 orderno,
											 directdemand,
											 whererequired)
			   VALUES ('".$Component."',
					  '".$TopDate."',
					  '".$ExtendedQuantity."',
					  '".$TopMRPDemandType."',
					  '".$TopOrderNo."',
					  '0',
					  '".$WhereRequired."')";
		$result = DB_query($sql);
	}  //end of while loop

}  // End of CreateLowerLevelRequirement

include('includes/footer.php');
?>