<?php
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) {
    ob_end_clean();
}

ob_start();

session_start();
require_once '../connect.php';

$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    $tcpdfPath = dirname(__DIR__) . '/vendor/tecnickcom/tcpdf/tcpdf.php';
    if (!file_exists($tcpdfPath)) {
        $tcpdfPath = dirname(__DIR__) . '/tcpdf/tcpdf.php';
    }
    
    if (!file_exists($tcpdfPath)) {
        ob_end_clean();
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'error' => 'TCPDF library not found. Please run: composer install']));
    }
    
    require_once $tcpdfPath;
}

if (!class_exists('TCPDF')) {
    ob_end_clean();
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'error' => 'TCPDF class not loaded. Please check installation.']));
}

$jsonInput = file_get_contents('php://input');
if (!$jsonInput) {
    ob_end_clean();
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'error' => 'No input data received']));
}

$data = json_decode($jsonInput, true);

if (!$data) {
    ob_end_clean();
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'error' => 'Invalid JSON data received']));
}

$previewMode = isset($data['preview']) && $data['preview'] === true;

class CVPDF extends TCPDF {
    private $data;
    private $goldenRatio = 1.618;
    private $colorScheme;
    private $template;
    private $labels;
    
    public function __construct($data) {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->data = $data;
        $this->colorScheme = $this->getColorScheme($data['color'] ?? 'forest');
        $this->template = $data['template'] ?? 'golden';
        $this->labels = $this->getLabels($this->template);
        
        $this->SetCreator('Silver Web System');
        $this->SetAuthor($data['personal']['fullname'] ?? 'User');
        $this->SetTitle('CV - ' . ($data['personal']['fullname'] ?? 'Resume'));
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(TRUE, 15);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        
        $this->SetFont('helvetica', '', 10);
    }
    
    private function getLabels($template) {
        $labels = [
            'golden' => [
                'summary' => 'Professional Summary',
                'experience' => 'Work Experience',
                'education' => 'Education',
                'skills' => 'Skills',
                'technical' => 'Technical Skills',
                'soft' => 'Soft Skills',
                'languages' => 'Languages',
                'at' => 'at',
                'present' => 'Present'
            ],
            'spanish' => [
                'summary' => 'Resumen Profesional',
                'experience' => 'Experiencia Laboral',
                'education' => 'Educación',
                'skills' => 'Habilidades',
                'technical' => 'Habilidades Técnicas',
                'soft' => 'Habilidades Blandas',
                'languages' => 'Idiomas',
                'at' => 'en',
                'present' => 'Presente'
            ],
            'modern' => [
                'summary' => 'About Me',
                'experience' => 'Professional Experience',
                'education' => 'Academic Background',
                'skills' => 'Core Competencies',
                'technical' => 'Technical Expertise',
                'soft' => 'Interpersonal Skills',
                'languages' => 'Language Proficiency',
                'at' => '@',
                'present' => 'Current'
            ],
            'classic' => [
                'summary' => 'Executive Summary',
                'experience' => 'Employment History',
                'education' => 'Educational Qualifications',
                'skills' => 'Professional Skills',
                'technical' => 'Technical Proficiencies',
                'soft' => 'Personal Attributes',
                'languages' => 'Language Skills',
                'at' => 'with',
                'present' => 'To Present'
            ]
        ];
        
        return $labels[$template] ?? $labels['golden'];
    }
    
    private function getColorScheme($color) {
        $schemes = [
            'forest' => [
                'primary' => [19, 138, 54],
                'secondary' => [24, 255, 109],
                'light' => [228, 242, 233],
                'text' => [0, 0, 0]
            ],
            'spring' => [
                'primary' => [24, 255, 109],
                'secondary' => [19, 138, 54],
                'light' => [228, 242, 233],
                'text' => [0, 0, 0]
            ],
            'olive' => [
                'primary' => [52, 64, 58],
                'secondary' => [180, 208, 191],
                'light' => [228, 242, 233],
                'text' => [0, 0, 0]
            ],
            'gray' => [
                'primary' => [52, 64, 58],
                'secondary' => [180, 208, 191],
                'light' => [240, 240, 240],
                'text' => [0, 0, 0]
            ],
            'darkforest' => [
                'primary' => [19, 60, 35],
                'secondary' => [80, 80, 80],
                'light' => [245, 245, 245],
                'text' => [25, 25, 25]
            ]
        ];
        
        return $schemes[$color] ?? $schemes['forest'];
    }
    
    public function generateCV() {
        switch($this->template) {
            case 'spanish':
                $this->generateSpanishTemplate();
                break;
            case 'modern':
                $this->generateModernTemplate();
                break;
            case 'classic':
                $this->generateClassicTemplate();
                break;
            default:
                $this->generateGoldenTemplate();
                break;
        }
    }
    
    private function generateGoldenTemplate() {
        $this->AddPage();
        $this->generateStandardLayout();
    }
    
    private function generateSpanishTemplate() {
        $this->AddPage();
        $this->generateStandardLayout();
    }
    
    private function generateModernTemplate() {
        $this->AddPage();
        
        $primaryColor = $this->colorScheme['primary'];
        $secondaryColor = $this->colorScheme['secondary'];
        $lightColor = $this->colorScheme['light'];
        
        $this->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $this->Rect(0, 0, 210, 40, 'F');
        
        $this->SetY(10);
        $this->SetFont('helvetica', 'B', 26);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, $this->data['personal']['fullname'] ?? '', 0, 1, 'C');
        
        if (!empty($this->data['personal']['title'])) {
            $this->SetFont('helvetica', '', 14);
            $this->Cell(0, 7, $this->data['personal']['title'], 0, 1, 'C');
        }
        
        $this->SetY(45);
        $this->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        
        if (!empty($this->data['personal']['photo'])) {
            $this->handlePhoto(170, 10, 25, 25);
        }
        
        $this->generateSections();
    }
    
    private function generateClassicTemplate() {
        $this->AddPage();
        
        $this->SetFont('times', 'B', 24);
        $primaryColor = $this->colorScheme['primary'];
        $this->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $this->Cell(0, 10, $this->data['personal']['fullname'] ?? '', 0, 1, 'C');
        
        $this->SetDrawColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY() + 2, 195, $this->GetY() + 2);
        $this->Ln(5);
        
        $this->generateSections();
    }
    
    private function generateStandardLayout() {
        $imageSize = 30;
        $imageMargin = 5;
        
        if (!empty($this->data['personal']['photo'])) {
            $this->handlePhoto(15, 15, $imageSize, $imageSize);
        } else {
            $this->drawPhotoPlaceholder($imageSize);
        }
        
        $this->SetXY(15 + $imageSize + $imageMargin, 15);
        
        $this->SetFont('helvetica', 'B', 24);
        $primaryColor = $this->colorScheme['primary'];
        $this->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $this->Cell(0, 10, $this->data['personal']['fullname'] ?? '', 0, 1, 'L');
        
        $this->SetX(15 + $imageSize + $imageMargin);
        if (!empty($this->data['personal']['title'])) {
            $this->SetFont('helvetica', '', 14);
            $secondaryColor = $this->colorScheme['secondary'];
            $this->SetTextColor($secondaryColor[0], $secondaryColor[1], $secondaryColor[2]);
            $this->Cell(0, 7, $this->data['personal']['title'], 0, 1, 'L');
        }
        
        $this->SetX(15 + $imageSize + $imageMargin);
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $contact = [];
        if (!empty($this->data['personal']['email'])) {
            $contact[] = $this->data['personal']['email'];
        }
        if (!empty($this->data['personal']['phone'])) {
            $contact[] = $this->data['personal']['phone'];
        }
        if (!empty($this->data['personal']['address'])) {
            $contact[] = $this->data['personal']['address'];
        }
        if (!empty($contact)) {
            $this->Cell(0, 5, implode(' | ', $contact), 0, 1, 'L');
        }
        
        $this->SetY(15 + $imageSize + $imageMargin + 5);
        
        $this->generateSections();
    }
    
    private function generateSections() {
        $primaryColor = $this->colorScheme['primary'];
        $textColor = $this->colorScheme['text'];
        
        if (!empty($this->data['personal']['summary'])) {
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $this->Cell(0, 8, $this->labels['summary'], 0, 1);
            
            $this->SetFont('helvetica', '', 10);
            $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
            $this->MultiCell(0, 5, $this->data['personal']['summary'], 0, 'J');
            $this->Ln(5);
        }
        
        $startY = $this->GetY();
        $columnWidth = 115;
        $columnGap = 10;
        
        if (!empty($this->data['experience']) && is_array($this->data['experience'])) {
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $this->Cell($columnWidth, 8, $this->labels['experience'], 0, 1);
            
            foreach ($this->data['experience'] as $exp) {
                if ($this->GetY() > 250) {
                    $this->AddPage();
                    $startY = 15;
                    $this->SetY($startY);
                }
                
                if (!empty($exp['title']) || !empty($exp['company'])) {
                    $this->SetFont('helvetica', 'B', 10);
                    $textColor = ($this->data['color'] === 'darkforest') ? $this->colorScheme['text'] : [52, 64, 58];
                    $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                    $title = $exp['title'] ?? '';
                    if (!empty($exp['company'])) {
                        $title .= ($title ? ' ' . $this->labels['at'] . ' ' : '') . $exp['company'];
                    }
                    $this->MultiCell($columnWidth, 5, $title, 0, 'L');
                }
                
                if (!empty($exp['start']) || !empty($exp['end'])) {
                    $this->SetFont('helvetica', 'I', 9);
                    $dateColor = ($this->data['color'] === 'darkforest') ? $this->colorScheme['secondary'] : [100, 100, 100];
                    $this->SetTextColor($dateColor[0], $dateColor[1], $dateColor[2]);
                    $dates = $exp['start'] ?? '';
                    if (!empty($exp['end'])) {
                        $endText = ($exp['end'] === 'Present' || $exp['end'] === 'Presente') ? $this->labels['present'] : $exp['end'];
                        $dates .= ($dates ? ' - ' : '') . $endText;
                    }
                    $this->Cell($columnWidth, 4, $dates, 0, 1);
                }
                
                if (!empty($exp['description'])) {
                    $this->SetFont('helvetica', '', 9);
                    $textColor = $this->colorScheme['text'];
                    $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                    $this->MultiCell($columnWidth, 4, $exp['description'], 0, 'J');
                }
                $this->Ln(3);
            }
        }
        
        if (!empty($this->data['education']) && is_array($this->data['education'])) {
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $this->Cell($columnWidth, 8, $this->labels['education'], 0, 1);
            
            foreach ($this->data['education'] as $edu) {
                if (!empty($edu['degree'])) {
                    $this->SetFont('helvetica', 'B', 10);
                    $textColor = ($this->data['color'] === 'darkforest') ? $this->colorScheme['text'] : [52, 64, 58];
                    $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                    $this->MultiCell($columnWidth, 5, $edu['degree'], 0, 'L');
                }
                
                if (!empty($edu['institution']) || !empty($edu['start']) || !empty($edu['end'])) {
                    $this->SetFont('helvetica', 'I', 9);
                    $dateColor = ($this->data['color'] === 'darkforest') ? $this->colorScheme['secondary'] : [100, 100, 100];
                    $this->SetTextColor($dateColor[0], $dateColor[1], $dateColor[2]);
                    $details = $edu['institution'] ?? '';
                    if (!empty($edu['start']) || !empty($edu['end'])) {
                        $dates = $edu['start'] ?? '';
                        if (!empty($edu['end'])) {
                            $dates .= ($dates ? ' - ' : '') . $edu['end'];
                        }
                        if ($dates) {
                            $details .= ($details ? ' | ' : '') . $dates;
                        }
                    }
                    $this->MultiCell($columnWidth, 4, $details, 0, 'L');
                }
                
                if (!empty($edu['description'])) {
                    $this->SetFont('helvetica', '', 9);
                    $textColor = $this->colorScheme['text'];
                    $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                    $this->MultiCell($columnWidth, 4, $edu['description'], 0, 'J');
                }
                $this->Ln(3);
            }
        }
        
        $leftColumnEndY = $this->GetY();
        
        $rightColumnX = 15 + $columnWidth + $columnGap;
        $rightColumnWidth = 65;
        
        $this->SetXY($rightColumnX, $startY);
        
        if (!empty($this->data['skills']['technical']) || !empty($this->data['skills']['soft'])) {
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $this->SetX($rightColumnX);
            $this->Cell($rightColumnWidth, 8, $this->labels['skills'], 0, 1);
            
            if (!empty($this->data['skills']['technical'])) {
                $this->SetX($rightColumnX);
                $this->SetFont('helvetica', 'B', 10);
                $textColor = ($this->data['color'] === 'darkforest') ? $this->colorScheme['text'] : [52, 64, 58];
                $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $this->Cell($rightColumnWidth, 5, $this->labels['technical'], 0, 1);
                
                $this->SetFont('helvetica', '', 9);
                $textColor = $this->colorScheme['text'];
                $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                
                $techSkills = $this->data['skills']['technical'];
                
                if (strpos($techSkills, "\n") !== false) {
                    $skillsList = explode("\n", $techSkills);
                } 
                elseif (strpos($techSkills, ",") !== false) {
                    $skillsList = explode(",", $techSkills);
                }
                elseif (strpos($techSkills, ";") !== false) {
                    $skillsList = explode(";", $techSkills);
                }
                else {
                    $skillsList = array($techSkills);
                }
                
                foreach ($skillsList as $skill) {
                    $skill = trim($skill);
                    if (!empty($skill)) {
                        $this->SetX($rightColumnX);
                        $bulletText = '• ' . $skill;
                        $this->MultiCell($rightColumnWidth, 4, $bulletText, 0, 'L');
                    }
                }
                $this->Ln(2);
            }
            
            if (!empty($this->data['skills']['soft'])) {
                $this->SetX($rightColumnX);
                $this->SetFont('helvetica', 'B', 10);
                $textColor = ($this->data['color'] === 'darkforest') ? $this->colorScheme['text'] : [52, 64, 58];
                $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $this->Cell($rightColumnWidth, 5, $this->labels['soft'], 0, 1);
                
                $this->SetFont('helvetica', '', 9);
                $textColor = $this->colorScheme['text'];
                $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                
                $softSkills = $this->data['skills']['soft'];
                
                if (strpos($softSkills, "\n") !== false) {
                    $skillsList = explode("\n", $softSkills);
                }
                elseif (strpos($softSkills, ",") !== false) {
                    $skillsList = explode(",", $softSkills);
                }
                elseif (strpos($softSkills, ";") !== false) {
                    $skillsList = explode(";", $softSkills);
                }
                else {
                    $skillsList = array($softSkills);
                }
                
                foreach ($skillsList as $skill) {
                    $skill = trim($skill);
                    if (!empty($skill)) {
                        $this->SetX($rightColumnX);
                        $bulletText = '• ' . $skill;
                        $this->MultiCell($rightColumnWidth, 4, $bulletText, 0, 'L');
                    }
                }
            }
            $this->Ln(5);
        }
        
        if (!empty($this->data['languages']) && is_array($this->data['languages'])) {
            $this->SetX($rightColumnX);
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $this->Cell($rightColumnWidth, 8, $this->labels['languages'], 0, 1);
            
            $this->SetFont('helvetica', '', 10);
            $textColor = $this->colorScheme['text'];
            $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
            foreach ($this->data['languages'] as $lang) {
                if (!empty($lang['name']) && !empty($lang['level'])) {
                    $this->SetX($rightColumnX);
                    $this->SetFont('helvetica', 'B', 9);
                    $this->Cell($rightColumnWidth * 0.4, 5, $lang['name'], 0, 0);
                    $this->SetFont('helvetica', '', 9);
                    $levelColor = ($this->data['color'] === 'darkforest') ? $this->colorScheme['secondary'] : [100, 100, 100];
                    $this->SetTextColor($levelColor[0], $levelColor[1], $levelColor[2]);
                    $levelText = $this->translateLevel($lang['level']);
                    $this->Cell($rightColumnWidth * 0.6, 5, $levelText, 0, 1);
                    $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                }
            }
        }
        
        $this->SetY(max($leftColumnEndY, $this->GetY()));
    }
    
    private function translateLevel($level) {
        if ($this->template === 'spanish') {
            $translations = [
                'Native' => 'Nativo',
                'Fluent' => 'Fluido',
                'Advanced' => 'Avanzado',
                'Intermediate' => 'Intermedio',
                'Basic' => 'Básico'
            ];
            return $translations[$level] ?? $level;
        }
        return $level;
    }
    
    private function handlePhoto($x, $y, $width, $height) {
        $photoData = $this->data['personal']['photo'];
        
        if (strpos($photoData, 'data:image') === 0) {
            $photoData = explode(',', $photoData);
            if (count($photoData) == 2) {
                $imageData = base64_decode($photoData[1]);
                
                $tempFile = tempnam(sys_get_temp_dir(), 'cv_photo_');
                file_put_contents($tempFile, $imageData);
                
                try {
                    $this->Image($tempFile, $x, $y, $width, $height, '', '', '', true, 300, '', false, false, 0, false, false, false);
                } catch (Exception $e) {
                    $this->drawPhotoPlaceholder($width);
                }
                
                unlink($tempFile);
            } else {
                $this->drawPhotoPlaceholder($width);
            }
        } else {
            $this->drawPhotoPlaceholder($width);
        }
    }
    
    private function drawPhotoPlaceholder($imageSize) {
        $this->SetFillColor(240, 240, 240);
        $primaryColor = $this->colorScheme['primary'];
        $this->SetDrawColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $this->SetLineWidth(0.5);
        $this->Rect(15, 15, $imageSize, $imageSize, 'DF');
        
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(150, 150, 150);
        $this->SetXY(15, 15 + $imageSize/2 - 2);
        $this->Cell($imageSize, 4, 'PHOTO', 0, 0, 'C');
    }
}

try {
    ob_end_clean();
    
    $pdf = new CVPDF($data);
    $pdf->generateCV();
    
    if ($previewMode) {
        $pdfContent = $pdf->Output('', 'S');
        
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'pdfData' => 'data:application/pdf;base64,' . base64_encode($pdfContent)
        ]);
    } else {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="CV_' . time() . '.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        $pdf->Output('CV_' . time() . '.pdf', 'D');
    }
} catch (Exception $e) {
    ob_end_clean();
    
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

exit();
?>

