<?php

/* Ensure the correct conversion factor is stored in the purchase
 * order line record
 */

$sql="SELECT podetailitem,
			itemcode,
			purchorders.supplierno
		FROM purchorderdetails
		LEFT JOIN purchorders
			ON purchorders.orderno=purchorderdetails.orderno
		WHERE conversionfactor=0";

$result=DB_query($sql, $db);

while ($myrow=DB_fetch_array($result)) {
	$ConversionFactorSQL="SELECT conversionfactor
							FROM purchdata
							WHERE supplierno='".$myrow['supplierno'] . "'
								AND stockid='".$myrow['itemcode']."'";
	$ConversionFactorResult=DB_query($ConversionFactorSQL, $db);
	if (DB_num_rows($ConversionFactorResult)>0) {
		$ConversionFactorRow=DB_fetch_array($ConversionFactorResult);
		$ConversionFactor=$ConversionFactorRow['conversionfactor'];
	} else {
		$ConversionFactor=1;
	}
	$UpdateSQL="UPDATE purchorderdetails
				SET conversionfactor='".$ConversionFactor."'
				WHERE podetailitem='".$myrow['podetailitem']."'";
	$UpdateResult=DB_query($UpdateSQL, $db);
}
OutputResult( _('Purchase order details have been correctly updated'), 'success');

UpdateDBNo(57, $db);

?>