<?php

InsertRecord('securitytokens',array('tokenid'),array('0'),array('tokenid, tokenname'), array('0', 'Main Index Page'), $db);

$result=DB_query("SELECT * FROM securitygroups WHERE tokenid='0'", $db);

if (DB_num_rows($result)==0) {
	DB_query("INSERT INTO securitygroups (SELECT secroleid, '0' FROM securityroles)" , $db);
}

UpdateField('pagesecurity', 'security', 0, "script='Index.php'", $db);

UpdateDBNo(80, $db);

?>