<?php
include ('includes/phpqrcode/phpqrcode.php');
include ('includes/barcodepack/class.code128.php');

echo ' <canvas id="PatientLabel" width="282" height="148" style="border:1px solid #000000;background-color:#ffffff;"></canvas>';

$SQL = "SELECT pid,
				hospital_file_nr,
				name_first,
				name_last,
				phone_1_nr,
				date_birth,
				blood_group,
				sex,
				phone_1_nr
			FROM care_person
			WHERE pid='" . $PID . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

$PatientName = $MyRow['name_first'] . ' ' . $MyRow['name_last'];
$PhoneNo = $MyRow['phone_1_nr'];

$Hash = sys_get_temp_dir() . '/' . sha1($_SESSION['UserID']);

if ($MyRow['sex'] == 'm') {
	$Gender = _('Male');
} elseif ($MyRow['sex'] == 'f') {
	$Gender = _('Female');
}
$PIDString = str_pad($PID, 8, "0", STR_PAD_LEFT);

$PatientString = $PIDString . "\n" . $PatientName . "\n" . ConvertSQLDate($MyRow['date_birth']) . "\n" . $MyRow['blood_group'] . "\n" . $Gender . "\n" . $MyRow['phone_1_nr'];

QRcode::png($PatientString, 'companies/' . $_SESSION['DatabaseName'] . '/qrcodes_dir/' . $PID . '.png', QR_ECLEVEL_L, 3);

$BarcodeImage = new code128($PID);
ob_start();
imagepng($BarcodeImage->draw(false));
$Image_String = ob_get_contents();
ob_end_clean();
file_put_contents('companies/' . $_SESSION['DatabaseName'] . '/barcodes_dir/' . $PIDString . '.png', $Image_String);

echo '
<script>
var c = document.getElementById("PatientLabel");
var ctx = c.getContext("2d");
ctx.font = "14px Arial";
ctx.fillText("', _('Patient ID'), ' - ', $PIDString, '", 10, 20);
ctx.fillText("', mb_strtoupper($MyRow['name_last']), ',  ', $MyRow['name_first'], '", 10, 40);
ctx.fillText("', _('Date of Birth'), ' - ', ConvertSQLDate($MyRow['date_birth']), '", 10, 60);
ctx.fillText("', _('Blood Group'), ' - ', $MyRow['blood_group'], '", 10, 80);
ctx.fillText("', _('Gender'), ' - ', $Gender, '", 10, 100);
ctx.fillText("', _('Telephone'), ' - ', $MyRow['phone_1_nr'], '", 10, 120);
var QRCode = new Image();
QRCode.src = "companies/' . $_SESSION['DatabaseName'] . '/qrcodes_dir/' . $PID . '.png";

QRCode.onload = function() {
    ctx.drawImage(QRCode, 205, 0, 75, 75);
}

var BarCode = new Image();
BarCode.src = "companies/' . $_SESSION['DatabaseName'] . '/barcodes_dir/' . $PIDString . '.png";

BarCode.onload = function() {
    ctx.drawImage(BarCode, 205, 80, 70, 50);
}

</script>';

?>