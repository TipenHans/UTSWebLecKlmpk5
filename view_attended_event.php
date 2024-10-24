<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_GET['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT events.event_id, events.event_name, events.banner, events.start_date, events.start_time, events.status, events.location
    FROM participants
    JOIN events ON participants.event_id = events.event_id
    WHERE participants.user_id = :user_id
");

$stmt->execute(['user_id' => $user_id]);
$event_history = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Event Detail</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="header">
        <h2>User Event Detail</h2>
        <div class="user-profile">
            <a href="view_profile.php">
                <img src="uploads/<?php echo $user['profile_picture']; ?>" alt="Profile Picture" width="50" height="50">    
                <?php echo $user['full_name']; ?>
            </a>
        </div>
    </div>

    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header">
                <h2 class="text-center m-3">Events Attended by User</h2>
            </div>
            <div class="card-body">
                <?php if (empty($event_history)) { ?>
                    <p class="text-center">This user hasn't registered for any events.</p>
                <?php } else { ?>
                    <table id="eventsTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID Event</th>
                                <th>Event Name</th>
                                <th>Event Date</th>
                                <th>Start Time</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($event_history as $event) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['event_id']); ?></td>
                                    <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                                    <td><?php echo htmlspecialchars($event['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($event['start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>

                <div class="back-arrow mt-4">
                    <a href="view_user.php?user_id=<?php echo $user_id; ?>" class="btn btn-secondary">Back to User</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#eventsTable').DataTable();
        });
    </script>
</body>
</html>
