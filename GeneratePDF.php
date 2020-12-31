 
<?php

require_once ( 'GenerateNotesandVocab.php'); 


ob_start();

require 'vendor/autoload.php';
use TCPDF;

// create new PDF document
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Brian Bakkala');
$pdf->SetTitle('Latin Homework #'.$_GET['hw']);

$pdf->SetHeaderData('', '', "AP Latin Homework #".$_GET['hw'],"");

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);



// set margins
// $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
// $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);




// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();


// Set some content to print
$latintext = "<span>".DisplayLines(false, $HWAssignment, $HWLines, $TargetedDictionary)."</span>" ;
$notestext = "<span>".DisplayNotesText($HWStartId, $HWEndId, $HWAssignment, $BookTitle, false)."</span>―<BR><span>" . DisplayVocabText($TargetedDictionary, true) ."</span>";

// Print text using writeHTMLCell()
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
$pdf->writeHTMLCell(111, '', '',  ($pdf->getY()), $latintext, 0, 0, 0, true, 'L', true);
$pdf->SetFont('', '', 8);
$pdf->writeHTMLCell(85, '', '', '', $notestext, 0, 1, 0, true, 'L', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('AP_Latin_HW_'.$_GET['hw'].'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+







?>