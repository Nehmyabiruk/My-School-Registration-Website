<?php
session_start();
require_once 'db_connect.php';
require_once 'tcpdf/tcpdf.php'; // Ensure TCPDF is installed in the project directory

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['username'], $_POST['student_id'], $_POST['semester'], $_POST['grades'])) {
    $_SESSION['message'] = "Invalid request for PDF download.";
    $_SESSION['message_type'] = 'error';
    header("Location: view_grades.php");
    exit();
}

$username = $_POST['username'];
$student_id = $_POST['student_id'];
$semester = $_POST['semester'];
$grades = $_POST['grades'];

// Validate input
if (empty($grades) || !is_array($grades)) {
    $_SESSION['message'] = "No grades available for PDF generation.";
    $_SESSION['message_type'] = 'error';
    header("Location: view_grades.php");
    exit();
}

// Initialize TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Student Registration System');
$pdf->SetTitle('Grade Report');
$pdf->SetSubject('Student Grades for ' . $semester);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add title
$pdf->SetFillColor(30, 64, 138); // Dark blue for nightlife theme
$pdf->SetTextColor(255, 255, 255); // White text
$pdf->Cell(0, 10, 'Grade Report', 0, 1, 'C', 1);
$pdf->Ln(5);

// Reset text color for content
$pdf->SetTextColor(0, 0, 0);

// Add student info
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(50, 10, 'Student Username:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $username, 0, 1);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(50, 10, 'Student ID:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $student_id, 0, 1);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(50, 10, 'Semester:', 0, 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $semester, 0, 1);
$pdf->Ln(10);

// Grades table
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(200, 200, 200); // Light gray for header
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(80, 10, 'Course', 1, 0, 'C', 1);
$pdf->Cell(40, 10, 'Grade', 1, 0, 'C', 1);
$pdf->Cell(40, 10, 'Credit Hours', 1, 1, 'C', 1);

$pdf->SetFont('helvetica', '', 12);
foreach ($grades as $grade) {
    $pdf->Cell(80, 10, htmlspecialchars($grade['course_name']), 1, 0);
    $pdf->Cell(40, 10, htmlspecialchars($grade['grade']), 1, 0, 'C');
    $pdf->Cell(40, 10, htmlspecialchars($grade['credit_hour']), 1, 1, 'C');
}

// Output PDF for download
$filename = "grades_{$username}_{$semester}.pdf";
$pdf->Output($filename, 'D'); // 'D' forces download
exit();
?>