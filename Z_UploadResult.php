<?php

//$PageSecurity=15;

include('includes/session.php');

$Title=_('File Upload Result');

$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php'); ;
include('includes/header.php');


prnMsg( _('The file') . ' ' . $HTTP_POST_FILES['userfile']['name'] . ' ' . _('was uploaded to the server in the /tmp directory and has been renamed temp'),'info');

move_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'], '/tmp/temp');

include('includes/footer.php');

?>