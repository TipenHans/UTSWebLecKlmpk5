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

$event_id = $_GET['event_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
$stmt->execute(['event_id' => $event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found.";
    exit;
}

if (isset($_POST['delete_event'])) {
    $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
    $stmt->execute(['event_id' => $event_id]);
    
    echo json_encode(['status' => 'success', 'message' => 'Event Deleted Successfully']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details (Admin)</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>
<body>
    <div class="header">
        <h2>Event Management</h2>
        <div class="user-profile">
            <img src="uploads/<?php echo $admin['profile_picture']; ?>" alt="Profile Picture" width="50" height="50">
            <a href="view_profile.php"><?php echo $admin['full_name']; ?></a>
        </div>
    </div>

    <div class="container mt-5">
        <div class="d-flex justify-content-center">
            <div class="card col-5 shadow-lg">
                <div class="card-header">
                    <h2>Event Details</h2>
                </div>
                <div class="card-body">
                    <div class="event-banner mb-3">
                        <div class="d-flex justify-content-around">
                            <img src="uploads/<?php echo $event['banner']; ?>" alt="Event Banner" id="event-img">
                            <div class="card">
                                <div class="card-body">
                                    <p class="card-title">Time Remaining Until Event Starts:</p>
                                    <div id="countdown" class="card-text fw-bold fs-4"></div>
                                    <div class="">
                                        Event Starts On: <?php echo date("F j, Y, g:i a", strtotime($event['start_date'] . ' ' . $event['start_time'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table class="table table-bordered mt-3">
                            <tbody>
                                <tr>
                                    <th>Event Name</th>
                                    <td><?php echo $event['event_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td><?php echo $event['start_date']; ?></td>
                                </tr>
                                <tr>
                                    <th>Location</th>
                                    <td><?php echo $event['location']; ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td><?php echo ucfirst($event['status']); ?></td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td><?php echo nl2br($event['description']); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Participants</th>
                                    <td><?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end">
                            <a href="participants.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-primary">View Participants</a>
                        </div>
                        <div class="mt-4 d-flex justify-content-between">
                            <div>
                                <a href="edit_event.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-warning">Edit</a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $event['event_id']; ?>)">Delete</button>
                            </div>
                            <a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
            </div>  
        </div>  
    </div> 

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmDelete(eventId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete the event and all associated participants permanently!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f44336',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('delete_event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `event_id=${eventId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = 'admin_dashboard.php'; // Redirect to admin dashboard
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    });
                }
            });
        }

        var countDownDate = new Date("<?php echo $event['start_date'] . ' ' . $event['start_time']; ?>").getTime();

        var countdownFunction = setInterval(function() {
            var now = new Date().getTime();
            var distance = countDownDate - now;

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("countdown").innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

            if (distance < 0) {
                clearInterval(countdownFunction);
                document.getElementById("countdown").innerHTML = "Event has started!";
            }
        }, 1000);
    </script>
</body>
</html>

