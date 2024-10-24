<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$event_id = $_GET['event_id'];

$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :admin_id");
$stmt->execute(['admin_id' => $admin_id]);
$admin = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
$stmt->execute(['event_id' => $event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $event_name = htmlspecialchars($_POST['event_name']);
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $location = htmlspecialchars($_POST['location']);
    $description = htmlspecialchars($_POST['description']);
    $max_participants = (int)$_POST['max_participants'];
    $status = $_POST['status'];

    if ($max_participants < 1) {
        $_SESSION['error'] = 'Max participants must be at least 1';
        header('Location: edit_event.php?event_id=' . $event_id);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_name = :event_name AND event_id != :event_id");
    $stmt->execute(['event_name' => $event_name, 'event_id' => $event_id]);
    $existing_event = $stmt->fetch();

    if ($existing_event) {
        $_SESSION['error'] = 'An event with this name already exists. Please choose a different name.';
        header('Location: edit_event.php?event_id=' . $event_id);
        exit;
    }

    if (!empty($_FILES['banner']['name'])) {
        $banner = $_FILES['banner']['name'];
        move_uploaded_file($_FILES['banner']['tmp_name'], "uploads/$banner");
    } else {
        $banner = $event['banner'];
    }

    $stmt = $pdo->prepare("UPDATE events SET event_name = :event_name, start_date = :start_date, start_time = :start_time, 
                           location = :location, description = :description, max_participants = :max_participants, 
                           banner = :banner, status = :status WHERE event_id = :event_id");
    $stmt->execute([
        'event_name' => $event_name,
        'start_date' => $start_date,
        'start_time' => $start_time,
        'location' => $location,
        'description' => $description,
        'max_participants' => $max_participants,
        'banner' => $banner,
        'status' => $status,
        'event_id' => $event_id
    ]);

    $_SESSION['event_updated'] = true;

    header('Location: event_details_admin.php?event_id=' . $event_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
    <div class="d-flex justify-content-center">
        <div class="card col-6 mt-3">
            <div class="card-header">
                <h2><?php echo $event['event_name']?></h2>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-around mb-3">
                    <img src="uploads/<?php echo $event['banner']; ?>" alt="Event Banner" class="img-fluid rounded" width="400">
                </div>
            
                <form class="form-edit mt-4" action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="event_name" class="form-label">Nama Event:</label>
                        <input type="text" id="event_name" name="event_name" class="form-control" value="<?php echo $event['event_name']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Tanggal:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $event['start_date']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Waktu:</label>
                        <input type="time" id="start_time" name="start_time" class="form-control" value="<?php echo $event['start_time']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Lokasi:</label>
                        <input type="text" id="location" name="location" class="form-control" value="<?php echo $event['location']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Event Status:</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="open" <?php if ($event['status'] === 'open') echo 'selected'; ?>>Open</option>
                            <option value="closed" <?php if ($event['status'] === 'closed') echo 'selected'; ?>>Closed</option>
                            <option value="cancelled" <?php if ($event['status'] === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi:</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo $event['description']; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="max_participants" class="form-label">Jumlah Max Participant (minimal 1):</label>
                        <input type="number" id="max_participants" name="max_participants" class="form-control" value="<?php echo $event['max_participants']; ?>" required min="1">
                    </div>

                    <div class="mb-3">
                        <label for="banner" class="form-label">Update Event Banner</label>
                        <input type="file" id="banner" name="banner" class="form-control" accept="image/*">
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="update_event" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-danger" onclick="window.location.href='event_details_admin.php?event_id=<?php echo $event_id; ?>';">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script>
        document.querySelector('.upload-label').addEventListener('click', function() {
            document.getElementById('banner').click();
        });

        <?php if (isset($_SESSION['event_updated']) && $_SESSION['event_updated'] === true): ?>
            Swal.fire({
                icon: 'success',
                title: 'Event Updated',
                text: 'The event has been successfully updated!',
                confirmButtonText: 'OK'
            }).then(function() {
                window.location.href = 'event_details_admin.php?event_id=<?php echo $event_id; ?>';
            });
            <?php unset($_SESSION['event_updated']);?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $_SESSION['error']; ?>',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['error']);?>
        <?php endif; ?>
    </script>
</body>
</html>
