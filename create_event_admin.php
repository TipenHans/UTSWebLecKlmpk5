<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :admin_id");
$stmt->execute(['admin_id' => $admin_id]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = $_POST['event_name'];
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $max_participants = $_POST['max_participants'];
    $status = $_POST['status'];

    if ($max_participants < 1) {
        $_SESSION['error'] = 'Invalid Max Participants';
        header('Location: create_event_admin.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_name = :event_name");
    $stmt->execute(['event_name' => $event_name]);
    $existing_event = $stmt->fetch();

    if ($existing_event) {
        $_SESSION['error'] = 'Event Name Already Exists';
        header('Location: create_event_admin.php');
        exit;
    }

    if (!empty($_FILES['banner']['name'])) {
        $banner = $_FILES['banner']['name'];
        $banner_tmp = $_FILES['banner']['tmp_name'];
        $upload_dir = "uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        move_uploaded_file($banner_tmp, $upload_dir . $banner);

        $stmt = $pdo->prepare("INSERT INTO events (event_name, start_date, start_time, location, description, max_participants, banner, status) 
                               VALUES (:event_name, :start_date, :start_time, :location, :description, :max_participants, :banner, :status)");
        $stmt->execute([
            'event_name' => $event_name,
            'start_date' => $start_date,
            'start_time' => $start_time,
            'location' => $location,
            'description' => $description,
            'max_participants' => $max_participants,
            'banner' => $banner,
            'status' => $status
        ]);

        $_SESSION['event_created'] = true;

        header('Location: admin_dashboard.php');
        exit;
    } else {
        $_SESSION['error'] = 'No Banner Uploaded';
        header('Location: create_event_admin.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script>
        function loadFile(event) {
            const output = document.getElementById('bannerPreview');
            output.src = URL.createObjectURL(event.target.files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src);
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <h2>Event Management</h2>
        <div class="user-profile">
            <img src="uploads/<?php echo $admin['profile_picture']; ?>" alt="Profile Picture" width="50" height="50">
            <a href="view_profile.php"><?php echo $admin['full_name']; ?></a>
        </div>
    </div>
    <div class="container card mt-5 shadow p-4 col-5">
        <form action="create_event_admin.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="event_name" class="form-label">Nama Event:</label>
                <input type="text" id="event_name" name="event_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Tanggal:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="start_time" class="form-label">Jam:</label>
                <input type="time" id="start_time" name="start_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Lokasi:</label>
                <input type="text" id="location" name="location" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi:</label>
                <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="max_participants" class="form-label">Jumlah Partisipan:</label>
                <input type="number" id="max_participants" name="max_participants" class="form-control" min=1 required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status:</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="banner" class="form-label">Upload Event Banner</label>
                <input type="file" id="banner" name="banner" class="form-control" accept="image/*" onchange="loadFile(event)" required>
                <img id="bannerPreview" class="img-fluid mt-3" src="#" alt="Event Banner Preview" style="display:none;">
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" name="add_event" class="btn btn-primary">Create</button>
                <button type="button" onclick="window.location.href='admin_dashboard.php';" class="btn btn-secondary">Back</button>
            </div>
        </form>
    </div>
</body>
</html>
