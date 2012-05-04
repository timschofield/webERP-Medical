<?php

/* Include the new script for printing quotations in Portrait
 */

InsertRecord('pagesecurity', array('script', 'security'), array('KCMCRegister.php',4), array('script', 'security'), array('KCMCRegister.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCPatientDeposit.php',4), array('script', 'security'), array('KCMCPatientDeposit.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCRadiology.php',4), array('script', 'security'), array('KCMCRadiology.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCLabTests.php',4), array('script', 'security'), array('KCMCLabTests.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCPharmacy.php',4), array('script', 'security'), array('KCMCPharmacy.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCOtherMedicalServices.php',4), array('script', 'security'), array('KCMCOtherMedicalServices.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCInPatientBilling.php',4), array('script', 'security'), array('KCMCInPatientBilling.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCInsuranceInvoice.php',4), array('script', 'security'), array('KCMCInsuranceInvoice.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCRadiologyReport.php',4), array('script', 'security'), array('KCMCRadiologyReport.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCLaboratoryReport.php',4), array('script', 'security'), array('KCMCLaboratoryReport.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCInsuranceCompanyDetails.php',4), array('script', 'security'), array('KCMCInsuranceCompanyDetails.php',4), $db);
InsertRecord('pagesecurity', array('script', 'security'), array('KCMCFunctionalUnitPL.php',4), array('script', 'security'), array('KCMCFunctionalUnitPL.php',4), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>