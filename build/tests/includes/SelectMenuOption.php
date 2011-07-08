<?php

$OptionURL=substr(strstr(substr(strstr($result, $OptionName, true),-100,98), 'href', false),6);

$url=$ServerPath.$OptionURL;

include('GetURL.php');

?>