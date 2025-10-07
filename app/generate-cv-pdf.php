<?php
session_start();
require_once 'connect.php';
require_once 'tcpdf/tcpdf.php';

$data = json_decode(file_get_contents('php://input'), true);

class CVPDF extends TCPDF {
    private $data;
    private $goldenRatio = 1.618;
    
    public function __construct($data) {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->data = $data;
        
        $this->SetCreator('Silver Web System');
        $this->SetAuthor($data['personal']['fullname']);
        $this->SetTitle('CV - ' . $data['personal']['fullname']);
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(TRUE, 15);
    }
    
    public function Header() {
        // Header with golden ratio proportions
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(19, 138, 54); // Forest green
        $this->Cell(0, 10, $this->data['personal']['fullname'], 0, 1, 'L');
        
        $this->SetFont('helvetica', '', 14);
        $this->SetTextColor(52, 64, 58); // Black olive
        $this->Cell(0, 7, $this->data['personal']['title'], 0, 1, 'L');
        
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $contact = $this->data['personal']['email'] . ' | ' . 
                  $this->data['personal']['phone'] . ' | ' . 
                  $this->data['personal']['address'];
        $this->Cell(0, 5, $contact, 0, 1, 'L');
        
        $this->Ln(5);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Create PDF
$pdf = new CVPDF($data);
$pdf->AddPage();

// Add sections...
// Professional Summary
if (!empty($data['personal']['summary'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(19, 138, 54);
    $pdf->Cell(0, 8, 'Professional Summary', 0, 1);
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 5, $data['personal']['summary'], 0, 'L');
    $pdf->Ln(5);
}

// Work Experience
if (!empty($data['experience'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(19, 138, 54);
    $pdf->Cell(0, 8, 'Work Experience', 0, 1);
    
    foreach ($data['experience'] as $exp) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(52, 64, 58);
        $pdf->Cell(0, 5, $exp['title'] . ' at ' . $exp['company'], 0, 1);
        
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 4, $exp['start'] . ' - ' . $exp['end'], 0, 1);
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 4, $exp['description'], 0, 'L');
        $pdf->Ln(3);
    }
}

// Output PDF
$pdf->Output('CV_' . time() . '.pdf', 'D');
?>

