<?php

$CompanyDir = $path_to_root . '/companies/' . $_POST['company_name'];
$Result = mkdir($CompanyDir . '/FormDesigns');
if ($Result) {
	copy ($path_to_root . '/companies/weberpdemo/FormDesigns/GoodsReceived.xml', $CompanyDir . '/FormDesigns/GoodsReceived.xml');
	copy ($path_to_root . '/companies/weberpdemo/FormDesigns/PickingList.xml', $CompanyDir . '/FormDesigns/PickingList.xml');
	copy ($path_to_root . '/companies/weberpdemo/FormDesigns/PurchaseOrder.xml', $CompanyDir . '/FormDesigns/PurchaseOrder.xml');
	copy ($path_to_root . '/companies/weberpdemo/FormDesigns/SalesInvoice.xml', $CompanyDir . '/FormDesigns/SalesInvoice.xml');
	OutputResult( _('The contents of the Form Design folder has been copied to the company folder'), 'info');
} else {
	OutputResult( _('The contents of the Form Design folder could not be copied to the company folder'), 'info');
}
UpdateDBNo(62, $db);

?>