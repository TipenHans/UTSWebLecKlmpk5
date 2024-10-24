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
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants List</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="header">
        <h2>Event Management</h2>
        <div class="user-profile">
            
            <a href="view_profile.php">
                <img src="uploads/<?php echo $admin['profile_picture']; ?>" alt="Profile Picture" width="50" height="50">    
                <?php echo $admin['full_name']; ?>
            </a>
        </div>
    </div>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header">
                <h2 class="text-center m-3">Participants List for Event</h2>
            </div>
        
            <div class="card-body">
                <?php if (empty($participants)) { ?>
                    <p class="text-center">No participants registered for this event.</p>
                <?php } else { ?>
                    <table class="table table-bordered">
                        <tr>
                            <td>
                            <div class="download-btn d-flex justify-content-center">
                                <a href="?event_id=<?php echo $event_id; ?>&download=csv" class="btn btn-secondary m-2">Download CSV</a>
                                <a href="?event_id=<?php echo $event_id; ?>&download=pdf" class="btn btn-secondary m-2">Download PDF</a>
                                <a href="?event_id=<?php echo $event_id; ?>&download=excel" class="btn btn-secondary m-2">Download Excel</a>
                            </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <table id="jobsTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kode Tiket</th>
                                        <th>Nama Lengkap</th>
                                        <th>Tanggal Daftar</th>
                                        <th>E-Mail</th>
                                        <th>Action</th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participants as $participant) { ?>
                                        <tr>
                                            <td><?php echo $participant['ticket_code']; ?></td>
                                            <td><a href="participant_details.php?user_id=<?php echo $participant['user_id']; ?>&event_id=<?php echo $event_id; ?>"><?php echo $participant['full_name']; ?></a></td>
                                            <td><?php echo $participant['register_date']; ?></td>
                                            <td><?php echo $participant['email']; ?></td>
                                            <td>
                                                <button class="btn btn-danger delete-btn" data-user-id="<?php echo $participant['user_id']; ?>" data-event-id="<?php echo $event_id; ?>">Remove</button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            </td>
                        </tr>
                    </table>
                <?php } ?>
                <div class="back-arrow mt-4">
                    <a href="event_details_admin.php?event_id=<?php echo $event_id; ?>" class="btn btn-secondary">Back to Event</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        $('#jobsTable').DataTable({
            "paging": true,
            "searching": true,
            "info": true,
            "lengthMenu": [5, 10, 25, 50],
            "pageLength": 10
        });

    $('.delete-btn').on('click', function() {
        const userId = $(this).data('user-id');
        const eventId = $(this).data('event-id');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to remove this participant from the event?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_participant.php',
                    type: 'POST',
                    data: { 
                        user_id: userId, 
                        event_id: eventId 
                    },
                    success: function(response) {
                        if (response === 'success') {
                            Swal.fire(
                                'Deleted!',
                                'The participant has been removed from the event.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'There was a problem removing the participant.',
                                'error'
                            );
                        }
                    }
                });
            }
        });
    });
    });
    </script>
</body>
</html>
