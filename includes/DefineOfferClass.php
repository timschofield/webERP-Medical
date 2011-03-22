<?php
/* $Id$ */
/* Definition of the Offer class to hold all the information for a supplier offer
*/


Class Offer {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $TenderID;
	var $CurrCode;
	var $Location;
	var $SupplierID;
	var $LinesOnOffer;
	var $version;

	function Offer(){
	/*Constructor function initialises a new purchase offer object */
		$this->LineItems = array();
		$this->total=0;
		$this->LinesOnOffer=0;
	}

	function add_to_offer(
				$LineNo,
				$StockID,
				$Qty,
				$ItemDescr,
				$Price,
				$UOM,
				$DecimalPlaces,
				$ExpiryDate){

		if (isset($Qty) and $Qty!=0){

			$this->LineItems[$LineNo] = new LineDetails($LineNo,
				$StockID,
				$Qty,
				$ItemDescr,
				$Price,
				$UOM,
				$DecimalPlaces,
				$ExpiryDate);
			$this->LinesOnOffer++;
			Return 1;
		}
		Return 0;
	}

	function Save($db) {
		$MailText='';
		foreach ($this->LineItems as $LineItems) {
			if ($LineItems->Deleted==False) {
				$sql="INSERT INTO offers (
						supplierid,
						tenderid,
						stockid,
						quantity,
						uom,
						price,
						expirydate,
						currcode)
					VALUES (
						'".$this->SupplierID."',
						'".$this->TenderID."',
						'".$LineItems->StockID."',
						'".$LineItems->Quantity."',
						'".$LineItems->Units."',
						'".$LineItems->Price."',
						'".FormatDateForSQL($LineItems->ExpiryDate)."',
						'".$this->CurrCode."'
					)";
				$ErrMsg =  _('The suppliers offer could not be inserted into the database because');
				$DbgMsg = _('The SQL statement used to insert the suppliers offer record and failed was');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				if (DB_error_no($db)==0) {
					prnMsg( _('The offer for').' '.$LineItems->StockID.' '._('has been inserted into the database'), 'success');
					$MailText .= $LineItems->Quantity.$LineItems->Units.' '._('of').' '.$LineItems->StockID.' '._('at a price of').
						' '.$this->CurrCode.number_format($LineItems->Price,2)."\n";
				} else {
					prnMsg( _('The offer for').' '.$LineItems->StockID.' '._('could not be inserted into the database'), 'error');
					include('includes/footer.inc');
					exit;
				}
			}
		}
	}

	function update_offer_item($LineNo,
				$Qty,
				$Price,
				$ExpiryDate){

			$this->LineItems[$LineNo]->Quantity = $Qty;
			$this->LineItems[$LineNo]->Price = $Price;
			$this->LineItems[$LineNo]->ExpiryDate = $ExpiryDate;
	}

	function remove_from_offer(&$LineNo){
		 $this->LineItems[$LineNo]->Deleted = True;
	}


	function Offer_Value() {
		$TotalValue=0;
		foreach ($this->LineItems as $OrderedItems) {
			$TotalValue += ($OrderedItems->Price)*($OrderedItems->Quantity);
		}
		return $TotalValue;
	}
} /* end of class defintion */

Class LineDetails {
/* PurchOrderDetails */
	var $LineNo;
	var $StockID;
	var $ItemDescription;
	var $Quantity;
	var $Price;
	var $Units;
	var $DecimalPlaces;
	var $Deleted;
	var $ExpiryDate;

	function LineDetails (
				$LineNo,
				$StockItem,
				$Qty,
				$ItemDescr,
				$Prc,
				$UOM,
				$DecimalPlaces,
				$ExpiryDate)
	{

	/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNo = $LineNo;
		$this->StockID =$StockItem;
		$this->ItemDescription = $ItemDescr;
		$this->Quantity = $Qty;
		$this->Price = $Prc;
		$this->Units = $UOM;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->ExpiryDate = $ExpiryDate;
		$this->Deleted = False;
	}
}

?>