<?php
/*PDF page header for price list report */
if ($PageNumber>1){
	$pdf->newPage();
}

$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$FormDesign->logo->x+3,$Page_Height-$FormDesign->logo->y,$FormDesign->logo->width,$FormDesign->logo->height);
$pdf->SetTextColor(0,0,255);
$LeftOvers = $pdf->addText($FormDesign->CompanyName->x+3,$Page_Height-$FormDesign->CompanyName->y,$FormDesign->CompanyName->FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addText($FormDesign->GRNNumber2->x+3,$Page_Height-$FormDesign->GRNNumber2->y,$FormDesign->GRNNumber2->FontSize, '凭证类型：'.$Typemame,'right' );
$JournalNo=str_pad($JournalNo, 9, '0', STR_PAD_LEFT);
$LeftOvers = $pdf->addText($FormDesign->GRNNumber->x+3,$Page_Height-$FormDesign->GRNNumber->y,$FormDesign->GRNNumber->FontSize, '凭证编号：'.$JournalNo,'right' );
$LeftOvers = $pdf->addText($FormDesign->OrderNumber->x+3,$Page_Height-$FormDesign->OrderNumber->y,$FormDesign->OrderNumber->FontSize, '记 账 凭 证 ');
$LeftOvers = $pdf->addText($FormDesign->PrintDate->x+3,$Page_Height-$FormDesign->PrintDate->y,$FormDesign->PrintDate->FontSize, _('Printed').'：' . Date($_SESSION['DefaultDateFormat']), 'right');
$pdf->setlineStyle(array('width'=>1.2));
$pdf->SetLineStyle(array('color'=>array(0,0,255)));
/*Draw a rectangle to put the headings in     */
$pdf->Rectangle($FormDesign->HeaderRectangle->x+3, $Page_Height - $FormDesign->HeaderRectangle->y, $FormDesign->HeaderRectangle->width,$FormDesign->HeaderRectangle->height);

/*set up the headings */
$LeftOvers = $pdf->addText($FormDesign->Headings->Column1->x+3,$Page_Height - $FormDesign->Headings->Column1->y, $FormDesign->Headings->Column1->FontSize,  '摘   要');
$LeftOvers = $pdf->addText($FormDesign->Headings->Column2->x+3,$Page_Height - $FormDesign->Headings->Column2->y, $FormDesign->Headings->Column2->FontSize,  '科目代码');
$LeftOvers = $pdf->addText($FormDesign->Headings->Column3->x+3,$Page_Height - $FormDesign->Headings->Column3->y, $FormDesign->Headings->Column3->FontSize,  '科目名称');
$LeftOvers = $pdf->addTextWrap($FormDesign->Headings->Column4->x+3,$Page_Height - $FormDesign->Headings->Column4->y, $FormDesign->Headings->Column4->Length,$FormDesign->Headings->Column4->FontSize, '借方金额            贷方金额', 'right');
$LeftOvers = $pdf->addTextWrap($FormDesign->Headings->Column5->x+3,$Page_Height - $FormDesign->Headings->Column5->y, $FormDesign->Headings->Column5->Length,$FormDesign->Headings->Column5->FontSize,  '千百十万千.百十元角分', 'right');
$LeftOvers = $pdf->addTextWrap($FormDesign->Headings->Column6->x+3,$Page_Height - $FormDesign->Headings->Column6->y, $FormDesign->Headings->Column6->Length,$FormDesign->Headings->Column6->FontSize,  '千百十万千.百十元角分', 'right');



$LeftOvers = $pdf->addText($FormDesign->ReceiptDate->x+3,$Page_Height-$FormDesign->ReceiptDate->y,$FormDesign->ReceiptDate->FontSize, '记账日期: '.ConvertSQLDate($JournalDate));

$tmpX=$FormDesign->SignedFor->x+3;
$tmpY=$Page_Height-$FormDesign->SignedFor->y;
$tmpFz=$FormDesign->SignedFor->FontSize;
$LeftOvers = $pdf->addText($tmpX,$tmpY,$tmpFz, '会计主管：');
$LeftOvers = $pdf->addText($tmpX+220,$tmpY,$tmpFz, '复核：');
$LeftOvers = $pdf->addText($tmpX+405,$tmpY,$tmpFz, '制单：');

if ($JournalNo=='Preview') {
	$realname = 'cncerp';
} else {
	//list($userid,$realname,$stepdate)=getTransBy($TypeID,$JournalNo);//
}
$pdf->addText($tmpX+430,$tmpY,$tmpFz, $realname);

$pdf->addText($tmpX+50,$tmpY,$tmpFz, '');
$pdf->addText($tmpX+250,$tmpY,$tmpFz, '');
$pdf->addText($tmpX+335,$tmpY,$tmpFz, '');
$pdf->addText($tmpX+450,$tmpY,$tmpFz, '');

$LeftOvers = $pdf->addTextWrap($FormDesign->Headings->Column7->x+3,$Page_Height - $FormDesign->Headings->Column7->y, $FormDesign->Headings->Column7->Length,$FormDesign->Headings->Column7->FontSize, '过次页', 'right');
/*Draw a rectangle to put the data in     */
$pdf->setlineStyle(array('width'=>1.2));
$pdf->Rectangle($FormDesign->DataRectangle->x+3, $Page_Height - $FormDesign->DataRectangle->y, $FormDesign->DataRectangle->width,$FormDesign->DataRectangle->height);
$pdf->setlineStyle(array('width'=>0.2));
$pdf->Line($FormDesign->LineAboveFooter->startx, $Page_Height - $FormDesign->LineAboveFooter->starty, $FormDesign->LineAboveFooter->endx,$Page_Height - $FormDesign->LineAboveFooter->endy);

$pdf->setlineStyle(array('width'=>0.2));
$pdf->Line($FormDesign->Column1->startx, $Page_Height - $FormDesign->Column1->starty, $FormDesign->Column1->endx,$Page_Height - $FormDesign->Column1->endy);
$pdf->Line($FormDesign->Column2->startx, $Page_Height - $FormDesign->Column2->starty, $FormDesign->Column2->endx,$Page_Height - $FormDesign->Column2->endy);
$pdf->Line($FormDesign->Column3->startx, $Page_Height - $FormDesign->Column3->starty, $FormDesign->Column3->endx,$Page_Height - $FormDesign->Column3->endy);
$pdf->Line($FormDesign->Column4->startx, $Page_Height - $FormDesign->Column4->starty, $FormDesign->Column4->endx,$Page_Height - $FormDesign->Column4->endy);
$pdf->Line($FormDesign->Column5->startx, $Page_Height - $FormDesign->Column5->starty, $FormDesign->Column5->endx,$Page_Height - $FormDesign->Column5->endy);
$pdf->setlineStyle(array('width'=>1.2));

$pdf->Line($FormDesign->Column6->startx, $Page_Height - $FormDesign->Column6->starty, $FormDesign->Column6->endx,$Page_Height - $FormDesign->Column6->endy);
$pdf->setlineStyle(array('width'=>0.2));

$pdf->Line($FormDesign->Column7->startx, $Page_Height - $FormDesign->Column7->starty, $FormDesign->Column7->endx,$Page_Height - $FormDesign->Column7->endy);
$pdf->Line($FormDesign->Column8->startx, $Page_Height - $FormDesign->Column8->starty, $FormDesign->Column8->endx,$Page_Height - $FormDesign->Column8->endy);
$pdf->setlineStyle(array('width'=>1.2));

$pdf->Line($FormDesign->Column9->startx, $Page_Height - $FormDesign->Column9->starty, $FormDesign->Column9->endx,$Page_Height - $FormDesign->Column9->endy);
$pdf->setlineStyle(array('width'=>0.2));

$pdf->Line($FormDesign->Column10->startx, $Page_Height - $FormDesign->Column10->starty, $FormDesign->Column10->endx,$Page_Height - $FormDesign->Column10->endy);
$pdf->Line($FormDesign->Column11->startx, $Page_Height - $FormDesign->Column11->starty, $FormDesign->Column11->endx,$Page_Height - $FormDesign->Column11->endy);
$pdf->setlineStyle(array('width'=>1.2));
$pdf->SetLineStyle(array('color'=>array(255,50,0)));
$pdf->Line($FormDesign->Column12->startx, $Page_Height - $FormDesign->Column12->starty, $FormDesign->Column12->endx,$Page_Height - $FormDesign->Column12->endy);
$pdf->setlineStyle(array('width'=>0.2));
$pdf->SetLineStyle(array('color'=>array(0,0,255)));
$pdf->Line($FormDesign->Column13->startx, $Page_Height - $FormDesign->Column13->starty, $FormDesign->Column13->endx,$Page_Height - $FormDesign->Column13->endy);
$pdf->Line($FormDesign->Column14->startx, $Page_Height - $FormDesign->Column14->starty, $FormDesign->Column14->endx,$Page_Height - $FormDesign->Column14->endy);
$pdf->Line($FormDesign->Column15->startx, $Page_Height - $FormDesign->Column15->starty, $FormDesign->Column15->endx,$Page_Height - $FormDesign->Column15->endy);
$pdf->Line($FormDesign->Column16->startx, $Page_Height - $FormDesign->Column16->starty, $FormDesign->Column16->endx,$Page_Height - $FormDesign->Column16->endy);
$pdf->setlineStyle(array('width'=>1.2));

$pdf->Line($FormDesign->Column17->startx, $Page_Height - $FormDesign->Column17->starty, $FormDesign->Column17->endx,$Page_Height - $FormDesign->Column17->endy);
$pdf->setlineStyle(array('width'=>0.2));

$pdf->Line($FormDesign->Column18->startx, $Page_Height - $FormDesign->Column18->starty, $FormDesign->Column18->endx,$Page_Height - $FormDesign->Column18->endy);
$pdf->Line($FormDesign->Column19->startx, $Page_Height - $FormDesign->Column19->starty, $FormDesign->Column19->endx,$Page_Height - $FormDesign->Column19->endy);
$pdf->setlineStyle(array('width'=>1.2));

$pdf->Line($FormDesign->Column20->startx, $Page_Height - $FormDesign->Column20->starty, $FormDesign->Column20->endx,$Page_Height - $FormDesign->Column20->endy);
$pdf->setlineStyle(array('width'=>0.2));

$pdf->Line($FormDesign->Column21->startx, $Page_Height - $FormDesign->Column21->starty, $FormDesign->Column21->endx,$Page_Height - $FormDesign->Column21->endy);
$pdf->Line($FormDesign->Column22->startx, $Page_Height - $FormDesign->Column22->starty, $FormDesign->Column22->endx,$Page_Height - $FormDesign->Column22->endy);
$pdf->setlineStyle(array('width'=>1.2));
$pdf->SetLineStyle(array('color'=>array(255,50,0)));
$pdf->Line($FormDesign->Column23->startx, $Page_Height - $FormDesign->Column23->starty, $FormDesign->Column23->endx,$Page_Height - $FormDesign->Column23->endy);
$pdf->setlineStyle(array('width'=>0.2));
$pdf->SetLineStyle(array('color'=>array(0,0,255)));
$pdf->Line($FormDesign->Column24->startx, $Page_Height - $FormDesign->Column24->starty, $FormDesign->Column24->endx,$Page_Height - $FormDesign->Column24->endy);

$pdf->Line($FormDesign->Column25->startx, $Page_Height - $FormDesign->Column25->starty, $FormDesign->Column25->endx,$Page_Height - $FormDesign->Column25->endy);
$pdf->Line($FormDesign->Column26->startx, $Page_Height - $FormDesign->Column26->starty, $FormDesign->Column26->endx,$Page_Height - $FormDesign->Column26->endy);
$pdf->Line($FormDesign->Column27->startx, $Page_Height - $FormDesign->Column27->starty, $FormDesign->Column27->endx,$Page_Height - $FormDesign->Column27->endy);
$pdf->Line($FormDesign->Column28->startx, $Page_Height - $FormDesign->Column28->starty, $FormDesign->Column28->endx,$Page_Height - $FormDesign->Column28->endy);
$pdf->Line($FormDesign->Column29->startx, $Page_Height - $FormDesign->Column29->starty, $FormDesign->Column29->endx,$Page_Height - $FormDesign->Column29->endy);
$pdf->Line($FormDesign->Column30->startx, $Page_Height - $FormDesign->Column30->starty, $FormDesign->Column30->endx,$Page_Height - $FormDesign->Column30->endy);
$pdf->Line($FormDesign->Column31->startx, $Page_Height - $FormDesign->Column31->starty, $FormDesign->Column31->endx,$Page_Height - $FormDesign->Column31->endy);
$pdf->Line($FormDesign->Column32->startx, $Page_Height - $FormDesign->Column32->starty, $FormDesign->Column32->endx,$Page_Height - $FormDesign->Column32->endy);
$pdf->Line($FormDesign->Column34->startx, $Page_Height - $FormDesign->Column34->starty, $FormDesign->Column34->endx,$Page_Height - $FormDesign->Column34->endy);
$pdf->Line($FormDesign->Column35->startx, $Page_Height - $FormDesign->Column35->starty, $FormDesign->Column35->endx,$Page_Height - $FormDesign->Column35->endy);

$LeftOvers = $pdf->addTextWrap($FormDesign->Headings->Column10->x+3,$Page_Height - $FormDesign->Headings->Column10->y, $FormDesign->Headings->Column10->Length,$FormDesign->Headings->Column10->FontSize,  '附', 'right');
$LeftOvers = $pdf->addTextWrap($FormDesign->Headings->Column11->x+3,$Page_Height - $FormDesign->Headings->Column11->y, $FormDesign->Headings->Column11->Length,$FormDesign->Headings->Column11->FontSize,  '件', 'right');
$LeftOvers = $pdf->addTextWrap($FormDesign->Headings->Column12->x+3,$Page_Height - $FormDesign->Headings->Column12->y, $FormDesign->Headings->Column12->Length,$FormDesign->Headings->Column12->FontSize,  '张', 'right');
$PageNumber++;
?>
