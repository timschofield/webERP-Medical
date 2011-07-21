<?php

$ModuleID=mb_substr(mb_strstr($result, $ModuleName, true),-4,2);
$url=$RootPath.'index.php?Application='.$ModuleID;

include('GetURL.php');

?>