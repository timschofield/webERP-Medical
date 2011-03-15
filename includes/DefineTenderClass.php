<?php
/* $Id$ */
/* Definition of the tender class to hold all the information for a supplier tender
*/


Class Tender {

	var $TenderId;
	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $CurrCode;
	var $ExRate;
	var $Initiator;
	var $DeliveryDate;
	var $RequisitionNo;
	var $DelAdd1;
	var $DelAdd2;
	var $DelAdd3;
	var $DelAdd4;
	var $DelAdd5;
	var $DelAdd6;
	var $Telephone;
	var $Comments;
	var $Location;
	var $OrderNo; /*Only used for modification of existing orders otherwise only established when order committed */
	var $LinesOnTender;
	var $SuppliersOnTender;
	var $GLLink; /*Is the GL link to stock activated only checked when order initiated or reading in for modification */
	var $Version;
	var $Revised;
	var $contact;
	var $Suppliers;

	function tender(){
	/*Constructor function initialises a new purchase tender object */
		$this->LineItems = array();
		$this->Suppliers = array();
		$this->LinesOnTender=0;
		$this->SuppliersOnTender=0;
	}

	function save($db) {
		/* Does record exist for this tender
		 */
		if ($this->TenderId=='') {
			$this->TenderId = GetNextTransNo(37, $db);
			$HeaderSQL="INSERT INTO tenders (
									tenderid,
									location,
									address1,
									address2,
									address3,
									address4,
									address5,
									address6,
									telephone)
								VALUES (
									'" . $this->TenderId  . "',
									'" . $this->Location  . "',
									'" . $this->DelAdd1  . "',
									'" . $this->DelAdd2  . "',
									'" . $this->DelAdd3  . "',
									'" . $this->DelAdd4  . "',
									'" . $this->DelAdd5  . "',
									'" . $this->DelAdd6  . "',
									'" . $this->Telephone  . "')";
			foreach ($this->Suppliers as $Supplier) {
				$SuppliersSQL[]="INSERT INTO tendersuppliers (
									tenderid,
									supplierid,
									email)
								VALUES (
									'" . $this->TenderId . "',
									'" . $Supplier->SupplierCode . "',
									'" . $Supplier->EmailAddress . "')";
			}
			foreach ($this->LineItems as $LineItem) {
				$ItemsSQL[]="INSERT INTO tenderitems (
									tenderid,
									stockid,
									quantity,
									units)
								VALUES (
									'" . $this->TenderId . "',
									'" . $LineItem->StockID . "',
									'" . $LineItem->Quantity . "',
									'" . $LineItem->Units . "')";
			}
		} else {
			$HeaderSQL="UPDATE tenders
								SET location='" . $this->Location  . "',
									address1='" . $this->DelAdd1  . "',
									address2='" . $this->DelAdd2  . "',
									address3='" . $this->DelAdd3  . "',
									address4='" . $this->DelAdd4  . "',
									address5='" . $this->DelAdd5  . "',
									address6='" . $this->DelAdd6  . "',
									telephone='" . $this->Telephone  . "'
								WHERE tenderid = '" . $this->TenderId  . "'";
			foreach ($this->Suppliers as $Supplier) {
				$sql="SELECT count(tenderid)
					FROM tendersuppliers
					WHERE  tenderid='" . $this->TenderId . "'
						AND supplierid='" . $Supplier->SupplierCode . "'";
				$result=DB_query($sql, $db);
				$myrow=DB_fetch_row($result);
				if ($myrow[0] > 0) {
					$SuppliersSQL[]="UPDATE tendersuppliers
									SET email='" . $Supplier->EmailAddress . "'
									WHERE tenderid='" . $this->TenderId . "'
										AND supplierid='" . $Supplier->SupplierCode . "'";
				} else {
					$SuppliersSQL[]="INSERT INTO tendersuppliers (
									tenderid,
									supplierid,
									email)
								VALUES (
									'" . $this->TenderId . "',
									'" . $Supplier->SupplierCode . "',
									'" . $Supplier->EmailAddress . "')";
				}
			}
			foreach ($this->LineItems as $LineItem) {
				$sql="SELECT count(tenderid)
					FROM tenderitems
					WHERE  tenderid='" . $this->TenderId . "'
							AND stockid='" . $LineItem->StockID . "'";
				$result=DB_query($sql, $db);
				$myrow=DB_fetch_row($result);
				if ($myrow[0] > 0) {
					$ItemsSQL[]="UPDATE tenderitems
									SET quantity='" . $LineItem->Quantity . "',
										units='" . $LineItem->Units . "'
									WHERE tenderid='" . $this->TenderId . "'
										AND stockid='" . $LineItem->StockID . "'";
				} else {
					$ItemsSQL[]="INSERT INTO tenderitems (
									tenderid,
									stockid,
									quantity,
									units)
								VALUES (
									'" . $this->TenderId . "',
									'" . $LineItem->StockID . "',
									'" . $LineItem->Quantity . "',
									'" . $LineItem->Units . "')";
				}
			}
		}
		DB_Txn_Begin($db);
		$result=DB_query($HeaderSQL, $db, '', '', True);
		foreach ($SuppliersSQL as $sql) {
			$result=DB_query($sql, $db, '', '', True);
		}
		foreach ($ItemsSQL as $sql) {
			$result=DB_query($sql, $db, '', '', True);
		}
		DB_Txn_Commit($db);
	}

	function add_item_to_tender(
				$LineNo,
				$StockID,
				$Qty,
				$ItemDescr,
				$UOM,
				$DecimalPlaces,
				$ExpiryDate){

		if (isset($Qty) and $Qty!=0){

			$this->LineItems[$LineNo] = new LineDetails($LineNo,
				$StockID,
				$Qty,
				$ItemDescr,
				$UOM,
				$DecimalPlaces,
				$ExpiryDate);
			$this->LinesOnTender++;
			Return 1;
		}
		Return 0;
	}

	function add_supplier_to_tender(
				$SupplierCode,
				$SupplierName,
				$Emailaddress){

		if (isset($SupplierCode)){

			$this->Suppliers[$SupplierCode] = new Supplier($SupplierCode, $SupplierName,
				$Emailaddress);
			$this->SuppliersOnTender++;
			Return 1;
		}
		Return 0;
	}

	function update_tender_item($LineNo,
				$Qty,
				$Price,
				$ExpiryDate){

			$this->LineItems[$LineNo]->Quantity = $Qty;
			$this->LineItems[$LineNo]->Price = $Price;
			$this->LineItems[$LineNo]->ExpiryDate = $ExpiryDate;
	}

	function remove_item_from_tender(&$LineNo){
		unset($this->LineItems[$LineNo]);
		$this->LinesOnTender--;
	}

	function remove_supplier_from_tender(&$SupplierCode){
		unset($this->Suppliers[$SupplierCode]);
		$this->SuppliersOnTender--;
	}

	function Tender_Value() {
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
				$UOM,
				$DecimalPlaces,
				$ExpiryDate)
	{

	/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNo = $LineNo;
		$this->StockID =$StockItem;
		$this->ItemDescription = $ItemDescr;
		$this->Quantity = $Qty;
		$this->Units = $UOM;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->ExpiryDate = $ExpiryDate;
		$this->Deleted = False;
	}
}

Class Supplier {

	var $SupplierCode;
	var $SupplierName;
	var $EmailAddress;
	var $Responded;

	function Supplier (
				$SupplierCode,
				$SupplierName,
				$EmailAddress) {
		$this->SupplierCode = $SupplierCode;
		$this->SupplierName = $SupplierName;
		$this->EmailAddress = $EmailAddress;
		$this->Responded = 0;
	}

}

?>