<?php

/* $Id$*/

// Script to do some Sales Integrity checks
// No SQL updates or Inserts - so safe to run


include ('includes/session.inc');
$title = _('Sales Integrity');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/maintenance.png" title="' . _('Sales Integrity Check') . '" alt="" />' . ' ' . _('Sales Integrity Check') . '</p>';

echo '<table class="selection">';

$SQL = "SELECT id,
				transno,
				order_,
				trandate
			FROM debtortrans
			WHERE type = 10";
$Result = DB_query($SQL,$db);

echo '<tr><td>'._('Check every Invoice has a Sales Order').'</td></tr>';
while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = "SELECT orderno,
					orddate
				FROM salesorders
				WHERE orderno = '" . $myrow['order_'] . "'";
	$Result2 = DB_query($SQL2,$db);
	echo '<tr>';
	if ( DB_num_rows($Result2) == 0) {
		echo '<td>'._('Invoice '). ' '. $myrow['transno'] . ' : </td>';
		echo '<td><font class="bad">' . _('No Sales Order') . '</font></td>';
	} else {
		echo '<td></td><td><font class="good">' . _('Sales Order Exists') . '</font></td>';
	}
	echo '</tr>';
}

DB_data_seek($Result, 0);
echo '<tr><td>' ._('Check every Invoice has a Tax Entry').'</td></tr>';
while ($myrow = DB_fetch_array($Result)) {
	$SQL3 = "SELECT debtortransid
				FROM debtortranstaxes
				WHERE debtortransid = '" . $myrow['id'] . "'";
	$Result3 = DB_query($SQL3,$db);

	if ( DB_num_rows($Result3) == 0) {
		echo '<td>'. _('Invoice '). ' ' . $myrow['transno'] . ' : </td>';
		echo '<td><font color=red>' . _('has no Tax Entry') . '</font></td>';
	} else {
		echo '<td></td><td><font class="good">' . _('Tax Entry Exists') . '</font></td>';
	}
	echo '</tr>';
}
DB_data_seek($Result, 0);
echo '<br /><br />'._('Check every Invoice has a GL Entry').'<br />';
while ($myrow = DB_fetch_array($Result)) {
	$SQL4 = "SELECT typeno
				FROM gltrans
				WHERE type = 10
				AND typeno = '" . $myrow['transno'] . "'";
	$Result4 = DB_query($SQL4,$db);

	if ( DB_num_rows($Result4) == 0) {
		echo '<br />' . _('Invoice') . ' ' . $myrow['transno'] . ' : ';
		echo '<font color=red>' . _('has no GL Entry') . '</font>';
	}
}


echo '<br /><br />'._('Check for orphan GL Entries').'<br />';
$SQL = "SELECT DISTINCT typeno,
					counterindex
				FROM gltrans
				WHERE type = 10";
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = "SELECT id,
					transno,
					trandate
				FROM debtortrans
				WHERE type = 10
				AND transno = '" . $myrow['typeno'] . "'";
	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
			echo '<br />'._('GL Entry ') . $myrow['counterindex'] . " : ";
			echo ', <font color=red>'._('Invoice ') . $myrow['typeno'] . _(' could not be found').'</font>';
	}
}

echo '<br /><br />'._('Check Receipt totals').'<br />';
$SQL = "SELECT typeno,
				amount
		FROM gltrans
		WHERE type = 12
		AND account = '" . $_SESSION['CompanyRecord']['debtorsact'] . "'";

$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = "SELECT SUM((ovamount+ovgst)/rate) AS gross
			FROM debtortrans
			WHERE type = 12
			AND transno = '" . $myrow['typeno'] . "'";

	$Result2 = DB_query($SQL2,$db);
	$myrow2 = DB_fetch_array($Result2);

	if ( $myrow2['gross'] + $myrow['amount'] == 0 ) {
			echo '<br />'._('Receipt') . ' ' . $myrow['typeno'] . " : ";
			echo '<font color=red>' . $myrow['amount']. ' ' . _('in GL but found'). ' ' . $myrow2['gross'] . ' ' . _('in debtorstrans').'</font>';
	}
}

echo '<br /><br />'._('Check for orphan Receipts').'<br />';
$SQL = "SELECT transno
			FROM debtortrans
			WHERE type = 12";
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = "SELECT amount
				FROM gltrans
				WHERE type = 12
				AND typeno = '" . $myrow['transno'] . "'";
	$Result2 = DB_query($SQL2,$db);
	$myrow2 = DB_fetch_array($Result2);

	if ( !$myrow2['amount'] ) {
		echo '<br />'._('Receipt') . ' ' . $myrow['transno'] . " : ";
		echo '<font color=red>' . $myrow['transno'] . ' ' ._('not found in GL').'</font>';
	}
}


echo '<br /><br />'._('Check for orphan Sales Orders').'<br />';
$SQL = "SELECT orderno,
				orddate
			FROM salesorders";
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = "SELECT transno,
					order_,
					trandate
				FROM debtortrans
				WHERE type = 10
				AND order_ = '" . $myrow['orderno'] . "'";

	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
		echo '<br />'._('Sales Order') . ' ' . $myrow['orderno'] . ' : ';
		echo '<font color=red>'._('Has no Invoice').'</font>';
	}
}

echo '<br /><br />'._('Check for orphan Order Items').'<br />';
echo '<br /><br />'._('Check Order Item Amounts').'<br />';
$SQL = "SELECT orderno
			FROM salesorderdetails";
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = "SELECT orderno,
					orddate
				FROM salesorders
				WHERE orderno = '" . $myrow['orderno'] . "'";
	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
			echo '<br />'._('Order Item') . ' ' . $myrow['orderno'] . ' : ';
			echo ', <font color=red>'._('Has no Sales Order').'</font>';
	}

	$sumsql = "SELECT SUM( qtyinvoiced * unitprice ) AS InvoiceTotal
				FROM salesorderdetails
				WHERE orderno = '" . $myrow['orderno'] . "'";
	$sumresult = DB_query($sumsql,$db);

	if ($sumrow = DB_fetch_array($sumresult)) {
		$invSQL = "SELECT transno,
							type,
							trandate,
							settled,
							rate,
							ovamount,
							ovgst
				 	FROM debtortrans WHERE order_ = '" . $myrow['orderno'] . "'";
		$invResult = DB_query($invSQL,$db);

		while( $invrow = DB_fetch_array($invResult) ) {
			// Ignore credit notes
			if ( $invrow['type'] != 11 ) {
					// Do an integrity check on sales order items
					if ( $sumrow['InvoiceTotal'] != $invrow['ovamount'] ) {
						echo '<br /><font color=red>' . _('Debtors trans') . ' ' . $invrow['ovamount'] . ' ' . _('differ from salesorderdetails') . ' ' . $sumrow['InvoiceTotal'] . '</font>';
					}
			}
		}
	}
}


echo '<br /><br />'._('Check for orphan Stock Moves').'<br />';
$SQL = "SELECT stkmoveno, transno FROM stockmoves";
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = "SELECT transno,
					order_,
					trandate
				FROM debtortrans
				WHERE type BETWEEN 10 AND 11
				AND transno = '" . $myrow['transno'] . "'";

	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
			echo '<br />'._('Stock Move') . ' ' . $myrow['stkmoveno'] . ' : ';
			echo ', <font color=red>'._('has no Invoice').'</font>';
	}
}


echo '<br /><br />'._('Check for orphan Tax Entries').'<br />';
$SQL = "SELECT debtortransid FROM debtortranstaxes";
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = "SELECT id,
					transno,
					trandate
				FROM debtortrans
				WHERE type BETWEEN 10 AND 11
				AND id = '" . $myrow['debtortransid'] . "'";
	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
			echo '<br />'._('Tax Entry') . ' ' . $myrow['debtortransid'] . ' : ';
			echo ', <font color=red>'._('has no Invoice').'</font>';
	}
}

echo '<br /><br />'._('Sales Integrity Check completed.').'<br /><br />';

prnMsg(_('Sales Integrity Check completed.'),'info');

include('includes/footer.inc');
?>