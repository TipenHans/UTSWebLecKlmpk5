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

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'event_name_asc';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

switch ($sort) {
    case 'event_name_desc':
        $order = 'ORDER BY event_name DESC';
        break;
    case 'participants_most':
        $order = 'ORDER BY current_participants DESC';
        break;
    case 'participants_least':
        $order = 'ORDER BY current_participants ASC';
        break;
    case 'start_date_closest':
        $order = 'ORDER BY start_date ASC';
        break;
    case 'start_date_furthest':
        $order = 'ORDER BY start_date DESC';
        break;
    default:
        $order = 'ORDER BY event_name ASC';
        break;
}
$status_condition = '';
if ($status_filter !== 'all') {
    $status_condition = "WHERE status = '$status_filter'";
}

$stmt = $pdo->query("SELECT * FROM events $status_condition $order");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="header">
        <h2>Event Management</h2>
        <div class="user-profile">
            <div class="me-3">
                <a href="view_user.php"><button class="btn-view">User</button></a>
            </div>
            <a href="view_profile.php">
                <img src="uploads/<?php echo $admin['profile_picture']; ?>" alt="Profile Picture" width="50" height="50">    
                <?php echo $admin['full_name']; ?>
            </a>
        </div>
    </div>
    <div class="d-flex justify-content-center">
        <div class="card m-4 col-10">        <div class="search-sort-container  card-header">
                <input type="text" id="searchEvent" onkeyup="searchEvents()" placeholder="Search event by name...">
                <div class="search-sort-right">
                    <label for="statusFilter">Status:</label>
                    <select id="statusFilter" onchange="filterByStatus(this.value)">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <label for="sort">Sort by:</label>
                    <select id="sort" onchange="sortEvents(this.value)">
                        <option value="event_name_asc" <?php echo $sort === 'event_name_asc' ? 'selected' : ''; ?>>A-Z</option>
                        <option value="event_name_desc" <?php echo $sort === 'event_name_desc' ? 'selected' : ''; ?>>Z-A</option>
                        <option value="participants_most" <?php echo $sort === 'participants_most' ? 'selected' : ''; ?>>Most Participants</option>
                        <option value="participants_least" <?php echo $sort === 'participants_least' ? 'selected' : ''; ?>>Least Participants</option>
                        <option value="start_date_closest" <?php echo $sort === 'start_date_closest' ? 'selected' : ''; ?>>Starting Soonest</option>
                        <option value="start_date_furthest" <?php echo $sort === 'start_date_furthest' ? 'selected' : ''; ?>>Starting Latest</option>
                    </select>
                </div>
            </div>
            <div class="event-container card-body">
                <?php if (empty($events)) { ?>
                    <p>No events available.</p>
                <?php } else { ?>
                    <?php foreach ($events as $event) { ?>
                        <div class="event-card" data-event-name="<?php echo strtolower($event['event_name']); ?>">
                            <img src="uploads/<?php echo $event['banner']; ?>" alt="Event Banner" id="event-img">
                            <h3><?php echo $event['event_name']; ?></h3>
                            <p>(<?php echo $event['current_participants']; ?> participants)</p>
                            <a href="event_details_admin.php?event_id=<?php echo $event['event_id']; ?>" class="btn-view">View Details</a>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
            <div class="add-event">
                <a href="create_event_admin.php">
                    <img src="asset/add.png" alt="Add Event" id="addButton">
                </a>
                <p id="addTooltip">Add New Event?</p>
            </div>
        </div>
    </div>
    <script>
        function filterByStatus(status) {
            const sort = document.getElementById('sort').value;
            window.location.href = '?status=' + status + '&sort=' + sort;
        }
        function sortEvents(sortType) {
            const status = document.getElementById('statusFilter').value;
            window.location.href = '?sort=' + sortType + '&status=' + status;
        }
        document.getElementById('addButton').addEventListener('mouseover', function () {
            document.getElementById('addTooltip').style.visibility = 'visible';
        });
        document.getElementById('addButton').addEventListener('mouseout', function () {
            document.getElementById('addTooltip').style.visibility = 'hidden';
        });
        document.getElementById('addButton1').addEventListener('mouseover', function () {
            document.getElementById('addTooltip1').style.visibility = 'visible';
        });
        document.getElementById('addButton1').addEventListener('mouseout', function () {
            document.getElementById('addTooltip1').style.visibility = 'hidden';
        });

        function searchEvents() {
            const searchInput = document.getElementById('searchEvent').value.toLowerCase();
            const events = document.querySelectorAll('.event-card');

            events.forEach(event => {
                const eventName = event.getAttribute('data-event-name');
                if (eventName.includes(searchInput)) {
                    event.style.display = 'block';
                } else {
                    event.style.display = 'none';
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>
