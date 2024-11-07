<?php
//$PageSecurity = 15;
include('includes/session.php');
$Title = _('Upgrade webERP to version 3.10.5');
include('includes/header.php');


prnMsg(_('This script will perform any modifications to the database since v 3.10 required to allow the additional functionality in version 3.10 scripts'),'info');

if (!isset($_POST['DoUpgrade'])) {
    echo '<br /><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<div class="centre"><input type="submit" name=DoUpgrade value="' . _('Perform Upgrade') . '" /></div>';
    echo '</form>';
}

if ($_POST['DoUpgrade'] == _('Perform Upgrade')){
    echo '<table><tr><td>' . _('Inserting default Debtor type') . '</td>';
    $sql='SELECT count(typeid)
            FROM debtortype
            WHERE typeid=1';
    $result=DB_query($sql);
    $myrow=DB_fetch_array($result);
    if ($myrow[0]==0) {
        $sql='INSERT INTO `debtortype` ( `typeid` , `typename` ) VALUES (1, "Default")';
        $result=DB_query($sql);
        if (DB_error_no()==0) {
            echo '<td>' . _('Success') . '</td></tr>';
        } else {
            echo '<td>' . _('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . _('Success') . '</td></tr>';
    }
    echo '<tr><td>' . _('Inserting default Factor company') . '</td>';
    $sql="SELECT count(id)
            FROM factorcompanies
            WHERE coyname='None'";
    $result=DB_query($sql);
    $myrow=DB_fetch_array($result);
    if ($myrow[0]==0) {
        $sql='INSERT INTO `factorcompanies` ( `id` , `coyname` ) VALUES (null, "None")';
        $result=DB_query($sql);
        if (DB_error_no()==0) {
            echo '<td>' . _('Success') . '</td></tr>';
        } else {
            echo '<td>' . _('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . _('Success') . '</td></tr>';
    }
    echo '<tr><td>' . _('Adding quotedate to salesorders table') . '</td>';
    $sql='DESCRIBE `salesorders` `quotedate`';
    $result=DB_query($sql);
    if (DB_num_rows($result)==0) {
        $sql='ALTER TABLE `salesorders` ADD `quotedate` date NOT NULL default "0000-00-00"';
        $result=DB_query($sql);
        if (DB_error_no()==0) {
            echo '<td>' . _('Success') . '</td></tr>';
        } else {
            echo '<td>' . _('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . _('Success') . '</td></tr>';
    }
    echo '<tr><td>' . _('Adding confirmeddate to salesorders table') . '</td>';
    $sql='DESCRIBE `salesorders` `confirmeddate`';
    $result=DB_query($sql);
    if (DB_num_rows($result)==0) {
        $sql="ALTER TABLE `salesorders` ADD `confirmeddate` date NOT NULL default '0000-00-00'";
        $result=DB_query($sql);
        if (DB_error_no()==0) {
            echo '<td>' . _('Success') . '</td></tr>';
        } else {
            echo '<td>' . _('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . _('Success') . '</td></tr>';
    }
    echo '</table>';
}

include('includes/footer.php');
?>
