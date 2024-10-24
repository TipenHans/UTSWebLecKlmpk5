<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';
require 'fpdf186/fpdf.php'; 
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :admin_id");
$stmt->execute(['admin_id' => $admin_id]);
$admin = $stmt->fetch();

$event_id = $_GET['event_id'];

$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
$stmt->execute(['event_id' => $event_id]);
$event = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT participants.ticket_code, users.id as user_id, users.full_name, participants.register_date, users.email
    FROM participants 
    JOIN users ON participants.user_id = users.id
    WHERE participants.event_id = :event_id
");
$stmt->execute(['event_id' => $event_id]);
$participants = $stmt->fetchAll();

if (isset($_GET['download']) && $_GET['download'] == 'csv') {
    $filename = "participants_list.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Kode Tiket', 'Nama Lengkap', 'Tanggal Daftar', 'E-Mail']);
    foreach ($participants as $participant) {
        fputcsv($output, [$participant['ticket_code'], $participant['full_name'], $participant['register_date'], $participant['email']]);
    }
    fclose($output);
    exit;
}

if (isset($_GET['download']) && $_GET['download'] == 'pdf') {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Participants List for Event: ' . $event['event_name'], 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 10, 'Kode Tiket', 1);
    $pdf->Cell(60, 10, 'Nama Lengkap', 1);
    $pdf->Cell(50, 10, 'Tanggal Daftar', 1);
    $pdf->Cell(40, 10, 'E-Mail', 1);
    $pdf->Ln();
    foreach ($participants as $participant) {
        $pdf->Cell(40, 10, $participant['ticket_code'], 1);
        $pdf->Cell(60, 10, $participant['full_name'], 1);
        $pdf->Cell(50, 10, $participant['register_date'], 1);
        $pdf->Cell(40, 10, $participant['email'], 1);
        $pdf->Ln();
    }
    $pdf->Output('D', 'participants_list.pdf');
    exit;
}

if (isset($_GET['download']) && $_GET['download'] == 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Kode Tiket');
    $sheet->setCellValue('B1', 'Nama Lengkap');
    $sheet->setCellValue('C1', 'Tanggal Daftar');
    $sheet->setCellValue('D1', 'E-Mail');

    $row = 2;
    foreach ($participants as $participant) {
        $sheet->setCellValue('A' . $row, $participant['ticket_code']);
        $sheet->setCellValue('B' . $row, $participant['full_name']);
        $sheet->setCellValue('C' . $row, $participant['register_date']);
        $sheet->setCellValue('D' . $row, $participant['email']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'participants_list.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $writer->save('php://output');
    exit;
}
?>
