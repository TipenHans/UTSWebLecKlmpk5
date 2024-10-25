<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$event_id = $_GET['event_id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
$stmt->execute(['event_id' => $event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "<div class='alert alert-danger'>Event not found.</div>";
    exit;
}

$event_datetime = $event['start_date'] . ' ' . $event['start_time'];
$current_time = new DateTime();
$event_time = new DateTime($event_datetime);

if ($current_time >= $event_time && $event['status'] !== 'closed') {
    $stmt = $pdo->prepare("UPDATE events SET status = 'closed' WHERE event_id = :event_id");
    if ($stmt->execute(['event_id' => $event_id])) {
        echo "Event status updated to closed.<br>";
    } else {
        echo "Failed to update event status.<br>";
    }

    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
    $stmt->execute(['event_id' => $event_id]);
    $event = $stmt->fetch();
}

$stmt = $pdo->prepare("SELECT * FROM participants WHERE user_id = :user_id AND event_id = :event_id");
$stmt->execute(['user_id' => $user_id, 'event_id' => $event_id]);
$already_registered = $stmt->fetch();

if (isset($_POST['register']) && !$already_registered && $event['status'] === 'open' && $event['current_participants'] < $event['max_participants']) {
    $ticket_code = 'TKT-' . strtoupper(substr(md5(time() . rand()), 0, 8));

    $stmt = $pdo->prepare("INSERT INTO participants (user_id, event_id, ticket_code, register_date) VALUES (:user_id, :event_id, :ticket_code, NOW())");
    $stmt->execute([
        'user_id' => $user_id,
        'event_id' => $event_id,
        'ticket_code' => $ticket_code
    ]);

    $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants + 1 WHERE event_id = :event_id");
    $stmt->execute(['event_id' => $event_id]);

    $_SESSION['success_message'] = "Registered successfully!";
    
    header('Location: event_details_user.php?event_id=' . $event_id);
    exit;
}

if (isset($_POST['cancel_registration']) && $event['status'] !== 'closed') {
    $stmt = $pdo->prepare("DELETE FROM participants WHERE user_id = :user_id AND event_id = :event_id");
    $stmt->execute(['user_id' => $user_id, 'event_id' => $event_id]);

    $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants - 1 WHERE event_id = :event_id");
    $stmt->execute(['event_id' => $event_id]);

    $_SESSION['success_message'] = "Canceled registration successfully!";
    
    header('Location: event_details_user.php?event_id=' . $event_id);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <script>
    var countDownDate = new Date("<?php echo $event_datetime; ?>").getTime();

    var countdownfunction = setInterval(function() {
        var now = new Date().getTime();
        var distance = countDownDate - now;

        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("countdown").innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

        if (distance < 0) {
            clearInterval(countdownfunction);
            document.getElementById("countdown").innerHTML = "The event has started!";
            var registerButton = document.getElementById("registerButton");
            if (registerButton) {
                registerButton.disabled = true;
                registerButton.innerText = "Registration Closed";
            }
            var cancelButton = document.getElementById("cancelButton");
            if (cancelButton) {
                cancelButton.disabled = true;
                cancelButton.innerText = "Cancellation Closed";
            }
        }
    }, 1000);
    </script>
</head>
<body>
    <div class="container">
        <div class="header d-flex justify-content-between align-items-center mb-2">
            <h2>Event Details</h2>
            <img src="uploads/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png'; ?>" alt="Profile" class="rounded-circle" width="50" height="50">
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: '<?php echo $_SESSION['success_message']; ?>',
                confirmButtonText: 'OK'
            });
        </script>
        <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $event['event_name']; ?></h3>
                        <p class="card-text"><strong>Location:</strong> <?php echo $event['location']; ?></p>
                        <p class="card-text"><strong>Date:</strong> <?php echo $event['start_date']; ?></p>
                        <p class="card-text"><strong>Time:</strong> <?php echo $event['start_time']; ?></p>
                        <p class="card-text"><strong>Description:</strong> <?php echo nl2br($event['description']); ?></p>
                        <p class="card-text"><strong>Participants:</strong> <?php echo $event['current_participants'] . '/' . $event['max_participants']; ?></p>
                        <p class="card-text"><strong>Status:</strong> <span class="badge <?php echo $event['status'] === 'open' ? 'bg-success' : 'bg-danger'; ?>"><?php echo ucfirst($event['status']); ?></span></p>
                        
                        <div class="action-buttons text-center">
                            <?php if ($already_registered) { ?>
                                <p class="text-warning">You are already registered for this event.</p>
                            <?php } elseif ($event['status'] === 'open' && $event['current_participants'] < $event['max_participants']) { ?>
                                <form method="POST" action="">
                                    <button type="submit" name="register" class="btn btn-primary" id="registerButton">Register</button>
                                </form>
                            <?php } elseif ($event['status'] === 'closed') { ?>
                                <p class="text-danger">The event is closed. Registration is not available.</p>
                            <?php } else { ?>
                                <p class="text-danger">Registration is closed or the event is full.</p>
                            <?php } ?>
                            
                            <div class="countdown-label mt-3">Countdown:</div>
                            <div class="countdown">
                                <p id="countdown" class="h5"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <img src="uploads/<?php echo $event['banner']; ?>" alt="Event Banner" class="img-fluid">
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12 text-start">
                <a href="user_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <?php if ($already_registered) { ?>
                    <button type="button" class="btn btn-danger" id="cancelButton">Cancel Registration</button>
                </div>

                <script>
                    document.getElementById('cancelButton').addEventListener('click', function() {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, cancel it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '';
                                var input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'cancel_registration';
                                form.appendChild(input);
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });
                    });
                </script>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
