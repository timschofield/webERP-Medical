<?php

include ('includes/session.php');
$Title = _('Update of Prices By A Multiple Of Cost');
$ViewTopic= 'Sales';
$BookMark = '';
include ('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . _('Update Price By Cost') . '</p>';

if (isset($_POST['submit']) OR isset($_POST['update'])) {
	if ($_POST['Margin'] == '') {
		header('Location: PricesByCost.php');
	}
	if ($_POST['Comparator'] == 1) {
		$Comparator = '<=';
	} else {
		$Comparator = '>=';
	} /*end of else Comparator */
	if ($_POST['StockCat'] != 'all') {
		$Category = " AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'";
	} else {
		$Category ='';
	}/*end of else StockCat */

	$sql = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					prices.debtorno,
					prices.branchcode,
					(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) as cost,
					prices.price as price,
					prices.debtorno AS customer,
					prices.branchcode AS branch,
					prices.startdate,
					prices.enddate,
					currencies.decimalplaces,
					currencies.rate
				FROM stockmaster INNER JOIN prices
				ON stockmaster.stockid=prices.stockid
				INNER JOIN currencies
				ON prices.currabrev=currencies.currabrev
				WHERE stockmaster.discontinued = 0
				" . $Category . "
				AND   prices.price" . $Comparator . "(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) * '" . filter_number_format($_POST['Margin']) . "'
				AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
				AND prices.currabrev ='" . $_POST['CurrCode'] . "'
				AND (prices.enddate>='" . Date('Y-m-d') . "' OR prices.enddate='0000-00-00')";
	$result = DB_query($sql);
	$numrow = DB_num_rows($result);

	if ($_POST['submit'] == 'Update') {
			//Update Prices
		$PriceCounter =0;
		while ($myrow = DB_fetch_array($result)) {
			/*The logic here goes like this:
			 * 1. If the price at the same start and end date already exists then do nowt!!
			 * 2. If not then check if a price with the start date of today already exists - then we should be updating it
			 * 3. If not either of the above then insert the new price
			*/
			$SQLTestExists = "SELECT price FROM prices
								WHERE stockid = '" . $_POST['StockID_' . $PriceCounter] . "'
								AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
								AND prices.currabrev ='" . $_POST['CurrCode'] . "'
								AND prices.debtorno ='" . $_POST['DebtorNo_' . $PriceCounter] . "'
								AND prices.branchcode ='" . $_POST['BranchCode_' . $PriceCounter] . "'
								AND prices.startdate ='" . $_POST['StartDate_' . $PriceCounter] . "'
								AND prices.enddate ='" . $_POST['EndDate_' . $PriceCounter] . "'
								AND prices.price ='" . filter_number_format($_POST['Price_' . $PriceCounter]) . "'";
			$TestExistsResult = DB_query($SQLTestExists);
			if (DB_num_rows($TestExistsResult)==0){ //the price doesn't currently exist
				//now check to see if a new price has already been created from start date of today

				$SQLTestExists = "SELECT price FROM prices
									WHERE stockid = '" . $_POST['StockID_' . $PriceCounter] . "'
									AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
									AND prices.currabrev ='" . $_POST['CurrCode'] . "'
									AND prices.debtorno ='" . $_POST['DebtorNo_' . $PriceCounter] . "'
									AND prices.branchcode ='" . $_POST['BranchCode_' . $PriceCounter] . "'
									AND prices.startdate ='" . date('Y-m-d') . "'";
				$TestExistsResult = DB_query($SQLTestExists);
				if (DB_num_rows($TestExistsResult)==1){
					 //then we are updating
					$SQLUpdate = "UPDATE prices	SET price = '" . filter_number_format($_POST['Price_' . $PriceCounter]) . "'
									WHERE stockid = '" . $_POST['StockID_' . $PriceCounter] . "'
									AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
									AND prices.currabrev ='" . $_POST['CurrCode'] . "'
									AND prices.debtorno ='" . $_POST['DebtorNo_' . $PriceCounter] . "'
									AND prices.branchcode ='" . $_POST['BranchCode_' . $PriceCounter] . "'
									AND prices.startdate ='" . date('Y-m-d') . "'
									AND prices.enddate ='" . $_POST['EndDate_' . $PriceCounter] . "'";
				$ResultUpdate = DB_query($SQLUpdate);
				} else { //there is not a price already starting today so need to create one
					//update the old price to have an end date of yesterday too
					$SQLUpdate = "UPDATE prices	SET enddate = '" . FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1)) . "'
									WHERE stockid = '" . $_POST['StockID_' . $PriceCounter] . "'
									AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
									AND prices.currabrev ='" . $_POST['CurrCode'] . "'
									AND prices.debtorno ='" . $_POST['DebtorNo_' . $PriceCounter] . "'
									AND prices.branchcode ='" . $_POST['BranchCode_' . $PriceCounter] . "'
									AND prices.startdate ='" . $_POST['StartDate_' . $PriceCounter] . "'
									AND prices.enddate ='" . $_POST['EndDate_' . $PriceCounter] . "'";
					$Result = DB_query($SQLUpdate);
					//we need to add a new price from today
					$SQLInsert = "INSERT INTO prices (	stockid,
														price,
														typeabbrev,
														currabrev,
														debtorno,
														branchcode,
														startdate
													) VALUES (
														'" . $_POST['StockID_' . $PriceCounter] . "',
														'" . filter_number_format($_POST['Price_' . $PriceCounter]) . "',
														'" . $_POST['SalesType'] . "',
														'" . $_POST['CurrCode'] . "',
														'" . $_POST['DebtorNo_' . $PriceCounter] . "',
														'" . $_POST['BranchCode_' . $PriceCounter] . "',
														'" . date('Y-m-d') . "'
													)";
					$ResultInsert = DB_query($SQLInsert);
				}
			}
			$PriceCounter++;
		}//end while loop
		DB_free_result($result); //clear the old result
		$result = DB_query($sql); //re-run the query with the updated prices
		$numrow = DB_num_rows($result); // get the new number - should be the same!!
	}

	$sqlcat = "SELECT categorydescription
				FROM stockcategory
				WHERE categoryid='" . $_POST['StockCat'] . "'";
	$ResultCat = DB_query($sqlcat);
	$CategoryRow = DB_fetch_array($ResultCat);

	$sqltype = "SELECT sales_type
				FROM salestypes
				WHERE typeabbrev='" . $_POST['SalesType'] . "'";
	$ResultType = DB_query($sqltype);
	$SalesTypeRow = DB_fetch_array($ResultType);

	if (isset($CategoryRow['categorgdescription'])) {
		$CategoryText = $CategoryRow['categorgdescription'] . ' ' . _('category');
	} else {
		$CategoryText = _('all Categories');
	} /*end of else Category */

	echo '<div class="page_help_text">' . _('Items in') . ' ' . $CategoryText . ' ' . _('With Prices') . ' ' . $Comparator . '' . $_POST['Margin'] . ' ' . _('times') . ' ' . _('Cost in Price List') . ' ' . $SalesTypeRow['sales_type'] . '</div><br /><br />';

	if ($numrow > 0) { //the number of prices returned from the main prices query is
		echo '<form action="' .htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'" method="post" id="update">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo'<input type="hidden" value="' . $_POST['StockCat'] . '" name="StockCat" />
			<input type="hidden" value="' . $_POST['Margin'] . '" name="Margin" />
			<input type="hidden" value="' . $_POST['CurrCode'] . '" name="CurrCode" />
			<input type="hidden" value="' . $_POST['Comparator'] . '" name="Comparator" />
			<input type="hidden" value="' . $_POST['SalesType'] . '" name="SalesType" />';

		echo '<table class="selection">
			<thead>
				<tr>
					<th class="ascending">' . _('Code') . '</th>
					<th class="ascending">' . _('Description') . '</th>
					<th class="ascending">' . _('Customer') . '</th>
					<th class="ascending">' . _('Branch') . '</th>
					<th class="ascending">' . _('Start Date') . '</th>
					<th class="ascending">' . _('End Date') . '</th>
					<th class="ascending">' . _('Cost') . '</th>
					<th class="ascending">' . _('GP %') . '</th>
					<th class="ascending">' . _('Price Proposed') . '</th>
					<th class="ascending">' . _('List Price') . '</th>
				<tr>
			</thead>
			<tbody>';

		$PriceCounter =0;
		while ($myrow = DB_fetch_array($result)) {

			//get cost
			if ($myrow['cost'] == '') {
				$Cost = 0;
			} else {
				$Cost = $myrow['cost'];
			} /*end of else Cost */

			//variables for update
			echo '<input type="hidden" value="' . $myrow['stockid'] . '" name="StockID_' . $PriceCounter .'" />
				<input type="hidden" value="' . $myrow['debtorno'] . '" name="DebtorNo_' . $PriceCounter .'" />
				<input type="hidden" value="' . $myrow['branchcode'] . '" name="BranchCode_' . $PriceCounter .'" />
				<input type="hidden" value="' . $myrow['startdate'] . '" name="StartDate_' . $PriceCounter .'" />
				<input type="hidden" value="' . $myrow['enddate'] . '" name="EndDate_' . $PriceCounter .'" />';
			//variable for current margin
			if ($myrow['price'] != 0){
				$CurrentGP = (($myrow['price']/$myrow['rate'])-$Cost)*100 / ($myrow['price']/$myrow['rate']);
			} else {
				$CurrentGP = 0;
			}
			//variable for proposed
			$ProposedPrice = $Cost * filter_number_format($_POST['Margin']);
			if ($myrow['enddate']=='0000-00-00'){
				$EndDateDisplay = _('No End Date');
			} else {
				$EndDateDisplay = ConvertSQLDate($myrow['enddate']);
			}
			echo '<tr class="striped_row">
					<td>' . $myrow['stockid'] . '</td>
					<td>' . $myrow['description'] . '</td>
					<td>' . $myrow['customer'] . '</td>
					<td>' . $myrow['branch'] . '</td>
					<td>' . ConvertSQLDate($myrow['startdate']) . '</td>
					<td>' . $EndDateDisplay . '</td>
					<td class="number">' . locale_number_format($Cost, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CurrentGP, 1) . '%</td>
					<td class="number">' . locale_number_format($ProposedPrice, $myrow['decimalplaces']) . '</td>
					<td><input type="text" class="number" name="Price_' . $PriceCounter . '" maxlength="14" size="10" value="' . locale_number_format($myrow['price'],$myrow['decimalplaces']) . '" /></td>
				</tr> ';
			$PriceCounter++;
		} //end of looping

		echo '</tbody>
			<tfoot>
				<tr>
			<td class="number" colspan="4"><input type="submit" name="submit" value="' . _('Update') . '" onclick="return confirm(\'' . _('If the prices above do not have a commencement date as today, this will create new prices with commencement date of today at the entered figures and update the existing prices with historical start dates to have an end date of yesterday. Are You Sure?') . '\');" /></td>
			<td class="text" colspan="3"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '"><input type="submit" value="' . _('Back') . '" /></a></td>
			 </tr>
			</tfoot>
			</table>
			</form>';
	} else {
		prnMsg(_('There were no prices meeting the criteria specified to review'),'info');
		echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Back') . '<a/></div>';
	}
} else { /*The option to submit was not hit so display form */
	echo '<div class="page_help_text">' . _('Prices can be displayed based on their relation to cost') . '</div><br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Bulk Price Update'), '</legend>';

	$SQL = "SELECT categoryid, categorydescription
			  FROM stockcategory
			  ORDER BY categorydescription";
	$result1 = DB_query($SQL);
	echo '<field>
			<label for="StockCat">' . _('Category') . ':</label>
			<select name="StockCat">';
	echo '<option value="all">' . _('All Categories') . '</option>';
	while ($myrow1 = DB_fetch_array($result1)) {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	}
	echo '</select>
		</field>';
	echo '<field>
			<label for="Margin">' . _('Price') . '</label>
			<select name="Comparator">
				<option value="1">' . _('Less than or equal to') . '</option>
				<option value="2">' . _('Greater than or equal to') . '</option>';
	if ($_SESSION['WeightedAverageCosting']==1) {
		echo '</select>' . ' '. _('Average Cost') . ' x ';
	} else {
		echo '</select>' . ' '. _('Standard Cost') . ' x ';
	}
	if (!isset($_POST['Margin'])){
		$_POST['Margin']=1;
	}
	echo '<input type="text" class="number" name="Margin" maxlength="8" size="8" value="' .$_POST['Margin'] . '" />
		</field>';
	$result = DB_query("SELECT typeabbrev, sales_type FROM salestypes");
	echo '<field>
			<label for="SalesType">' . _('Sales Type') . '/' . _('Price List') . ':</label>
			<select name="SalesType">';
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['SalesType'] == $myrow['typeabbrev']) {
			echo '<option selected="selected" value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
		} else {
			echo '<option value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result, 0);
	$result = DB_query("SELECT currency, currabrev FROM currencies");
	echo '</select>
		</field>';

	echo '<field>
			<label for="CurrCode">' . _('Currency') . ':</label>
			<select name="CurrCode">';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['CurrCode']) and $_POST['CurrCode'] == $myrow['currabrev']) {
			echo '<option selected="selected" value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
		} else {
			echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result, 0);
	echo '</select>
		</field>';
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Submit') . '" /></div>
		</div>
	</form>';
} /*end of else not submit */
include ('includes/footer.php');
?>
