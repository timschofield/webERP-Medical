<?php
/* $Id$*/
include('includes/session.inc');
$title = _('Upgrade webERP to version 3.10.5');
include('includes/header.inc');


prnMsg(_('This script will perform any modifications to the database since v 3.10 required to allow the additional functionality in version 3.10 scripts'),'info');

if (!isset($_POST['DoUpgrade'])) {
    echo '<br /><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<div class="centre"><button type="submit" name="DoUpgrade">' . _('Perform Upgrade') . '</button></div>';
    echo '</form>';
}

if (isset($_POST['DoUpgrade'])){
    echo '<table><tr><td>' . _('Inserting default Debtor type') . '</td>';
    $sql="SELECT count(typeid)
            FROM debtortype
            WHERE typeid=1";
    $result=DB_query($sql, $db);
    $myrow=DB_fetch_array($result);
    if ($myrow[0]==0) {
        $sql="INSERT INTO `debtortype` ( `typeid` , `typename` ) VALUES (1, 'Default')";
        $result=DB_query($sql, $db);
        if (DB_error_no($db)==0) {
            echo '<td>' . _('Success') . '</td>';
        } else {
            echo '<td>' . _('Failed') . '</td>';
        }
    } else {
        echo '<td>' . _('Success') . '</td>';
    }
    echo '</tr><tr><td>' . _('Inserting default Factor company') . '</td>';
    $sql="SELECT count(id)
            FROM factorcompanies
            WHERE coyname='None'";
    $result=DB_query($sql, $db);
    $myrow=DB_fetch_array($result);
    if ($myrow[0]==0) {
        $sql="INSERT INTO `factorcompanies` ( `id` , `coyname` ) VALUES (null, 'None')";
        $result=DB_query($sql, $db);
        if (DB_error_no($db)==0) {
            echo '<td>' . _('Success') . '</td>';
        } else {
            echo '<td>' . _('Failed') . '</td>';
        }
    } else {
        echo '<td>' . _('Success') . '</td>';
    }
    echo '</tr><tr><td>' . _('Adding quotedate to salesorders table') . '</td>';
    $sql="DESCRIBE `salesorders` `quotedate`";
    $result=DB_query($sql, $db);
    if (DB_num_rows($result)==0) {
        $sql="ALTER TABLE `salesorders` ADD `quotedate` date NOT NULL default '0000-00-00'";
        $result=DB_query($sql, $db);
        if (DB_error_no($db)==0) {
            echo '<td>' . _('Success') . '</td>';
        } else {
            echo '<td>' . _('Failed') . '</td>';
        }
    } else {
        echo '<td>' . _('Success') . '</td>';
    }
    echo '</tr><tr><td>' . _('Adding confirmeddate to salesorders table') . '</td>';
    $sql="DESCRIBE `salesorders` `confirmeddate`";
    $result=DB_query($sql, $db);
    if (DB_num_rows($result)==0) {
        $sql="ALTER TABLE `salesorders` ADD `confirmeddate` date NOT NULL default '0000-00-00'";
        $result=DB_query($sql, $db);
        if (DB_error_no($db)==0) {
            echo '<td>' . _('Success') . '</td>';
        } else {
            echo '<td>' . _('Failed') . '</td>';
        }
    } else {
        echo '<td>' . _('Success') . '</td>';
    }
    echo '</tr></table>';
}

include('includes/footer.inc');
?>