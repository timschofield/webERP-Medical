<?php

DropConstraint('recurringsalesorders', 'recurrsalesorderdetails_ibfk_1', $db);
AddConstraint('recurrsalesorderdetails', 'recurrsalesorderdetails_ibfk_1', 'recurrorderno', 'recurringsalesorders', 'recurrorderno', $db);

UpdateDBNo(59, $db);

?>