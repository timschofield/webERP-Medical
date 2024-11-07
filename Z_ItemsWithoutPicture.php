<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('List of Items without picture');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php'); ;
include ('includes/header.php');

$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			stockcategory.categorydescription
		FROM stockmaster, stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockmaster.discontinued = 0
			AND stockcategory.stocktype != 'D'
		ORDER BY stockcategory.categorydescription, stockmaster.stockid";
$result = DB_query($SQL);
$PrintHeader = TRUE;

if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text"><strong>' . _('Current Items without picture in webERP') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$i = 1;
	$SupportedImgExt = array('png','jpg','jpeg');
	while ($myrow = DB_fetch_array($result)) {
        $glob = (glob($_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
		$imagefile = reset($glob);
		if(!file_exists($imagefile) ) {
			if($PrintHeader){
				$TableHeader = '<tr>
								<th>' . '#' . '</th>
								<th>' . _('Category') . '</th>
								<th>' . _('Item Code') . '</th>
								<th>' . _('Description') . '</th>
								</tr>';
				echo $TableHeader;
				$PrintHeader = FALSE;
			}

			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '" target="_blank">' . $myrow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					$i,
					$myrow['categorydescription'],
					$CodeLink,
					$myrow['description']
					);
			$i++;
		}
	}
	echo '</table>
			</div>
			</form>';
}

include ('includes/footer.php');

?>