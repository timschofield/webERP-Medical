<?php

$OptionURL=mb_substr(mb_strstr(mb_substr(mb_strstr($result, $OptionName, true),-100,98), 'href', false),6);

$url=$ServerPath.$OptionURL;

include('GetURL.php');

?>