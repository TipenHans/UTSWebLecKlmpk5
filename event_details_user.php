<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$event_id = $_GET['event_id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
$stmt->execute(['event_id' => $event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found.";
    exit;
}

$event_datetime = $event['start_date'] . ' ' . $event['start_time'];

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

if (isset($_POST['cancel_registration'])) {
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
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container">
    <h2>Event Details</h2>

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

    <div class="event-banner">
        <img src="uploads/<?php echo $event['banner']; ?>" alt="Event Banner">
    </div>

    <div class="event-info">
        <div class="event-details">
            <h3><?php echo $event['event_name']; ?></h3>
            <p>Location: <?php echo $event['location']; ?></p>
            <p>Date: <?php echo $event['start_date']; ?></p>
            <p>Time: <?php echo $event['start_time']; ?></p>
            <p>Status: <strong><?php echo ucfirst($event['status']); ?></strong></p>
        </div>
        <div class="event-participants">
            <p>Participants</p>
            <p><?php echo $event['current_participants'] . '/' . $event['max_participants']; ?></p>
        </div>
    </div>

    <div class="actions">
        <?php if ($already_registered) { ?>
            <p>You are already registered for this event.</p>
            <button type="button" class="btn cancel-btn" id="cancelButton">Cancel Registration</button>
        <?php } elseif ($event['status'] === 'open' && $event['current_participants'] < $event['max_participants']) { ?>
            <form method="POST" action="">
                <button type="submit" name="register" class="btn apply-btn">Apply</button>
            </form>
        <?php } else { ?>
            <p>Registration is closed or the event is full.</p>
        <?php } ?>
    </div>

    <div class="countdown-container">
        <h3>Countdown to Event Start</h3>
        <div class="countdown" id="countdown"></div>
        <div class="start-time">Event starts on: <?php echo $event['start_date'] . ' at ' . $event['start_time']; ?></div>
    </div>

    <a href="user_dashboard.php" class="btn back-btn">Back to Dashboard</a>
</div>

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
    }
}, 1000);

document.getElementById('cancelButton')?.addEventListener('click', function() {
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

</body>
</html>
