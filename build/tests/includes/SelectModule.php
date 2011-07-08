<?php

$ModuleID=substr(strstr($result, $ModuleName, true),-4,2);
$url=$RootPath.'index.php?Application='.$ModuleID;

include('GetURL.php');

?>