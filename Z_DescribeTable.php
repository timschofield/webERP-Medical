<?php
/* $Id$*/

include('includes/session.inc');
$title = _('Database table details');
include('includes/header.inc');

$sql='DESCRIBE '.$_GET['table'];
$result=DB_query($sql, $db);

echo '<table class="selection"><tr>';
echo '<th>'._('Field name').'</th>';
echo '<th>'._('Field type').'</th>';
echo '<th>'._('Can field be null').'</th>';
echo '<th>'._('Default').'</th>';
while ($myrow=DB_fetch_array($result)) {
	echo '<tr><td>' .$myrow['Field'] .'</td><td>';
	echo $myrow['Type'] .'</td><td>';
	echo $myrow['Null'] .'</td><td>';
	echo $myrow['Default'] .'</td></tr>';
}
echo '</table>';
include('includes/footer.inc');


?>