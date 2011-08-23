<?php
/* $Id$*/

if (isset($_SESSION['timezone']) and mb_strlen($_SESSION['timezone']) > 0 ) {
    $ltz = $_SESSION['timezone'];
} else {
    $ltz = date_default_timezone_get();
}

$row = 1;
$handle = fopen('timezone.csv', 'r');
while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
    $num = count($data);
    $row++;
    for ($c=0; $c < $num; $c++) {
		$timezone=$data[$c];
		$c++;
		if ($timezone==$ltz) {
			echo '<option selected="True" value="'.$timezone.'">'.$timezone.'</option>';
		} else {
			echo '<option value="'.$timezone.'">'.$timezone.'</option>';
		}
    }
}
?>