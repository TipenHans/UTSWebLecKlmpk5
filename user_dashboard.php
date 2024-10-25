<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

$stmt = $pdo->query("SELECT * FROM events ORDER BY start_date ASC");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.9/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="mySidebar" aria-labelledby="offcanvasSidebarLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasSidebarLabel">Menu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="text-center mb-4">
                    <img src="uploads/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png'; ?>" alt="Profile" class="rounded-circle" width="80" height="80">
                    <h6 class="mt-2"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                </div>
                <div class="nav flex-column">
                    <a href="view_profile.php" class="nav-link text-white">Profile</a>
                    <a href="registered_events.php" class="nav-link text-white">My Events</a>
                    <a href="user_event_history_list.php" class="nav-link text-white">View Event History</a>
                    <a href="javascript:void(0);" id="logoutBtn" class="nav-link text-danger">Logout</a>
                </div>
            </div>
        </div>
        <div class="content-container container" id="main">
            <div class="header d-flex justify-content-between align-items-center mb-4">
                <img src="asset/menu.png" alt="Menu" class="img-fluid" id="menuButton" onclick="toggleSidebar()">
                <h2>Available Events</h2>
                <a href="view_profile.php"><img src="uploads/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png'; ?>" alt="Profile" class="profile-icon img-thumbnail rounded-circle" width="60" height="60"></a>
            </div>
            <div class="filter-section">
                <input type="text" id="searchEvent" class="form-control" placeholder="Search event by name..." onkeyup="applySearchFilterSort()">
                <select id="statusFilter" class="form-control" onchange="applySearchFilterSort()">
                    <option value="all">All Events</option>
                    <option value="available">Available Events</option>
                    <option value="full">Full Events</option>
                    <option value="closed">Closed Events</option>
                </select>
                <select id="sortOptions" class="form-control" onchange="applySearchFilterSort()">
                    <option value="#" selected disabled>Sort By:</option>
                    <option value="name_asc">Name A-Z</option>
                    <option value="name_desc">Name Z-A</option>
                    <option value="date_asc">Starting Soonest</option>
                    <option value="date_desc">Starting Latest</option>
                </select>
            </div>
            <div class="event-grid mb-5" id="eventGrid">
                <?php if (empty($events)) { ?>
                    <p>No events available.</p>
                <?php } else { ?>
                    <?php foreach ($events as $event) { 
                        $isFull = $event['current_participants'] >= $event['max_participants'];
                    ?>
                        <div class="event-card" data-name="<?php echo strtolower($event['event_name']); ?>" data-status="<?php echo $isFull ? 'full' : ($event['status'] === 'closed' ? 'closed' : 'available'); ?>" data-date="<?php echo $event['start_date']; ?>">
                            <img src="uploads/<?php echo $event['banner']; ?>" alt="Event Image" id="event-img">
                            <h4><?php echo $event['event_name']; ?></h4>
                            <p><?php echo $event['start_date']; ?></p>
                            <?php if ($isFull) { ?>
                                <p class="text-danger">Event Full</p>
                            <?php } elseif ($event['status'] == 'closed') { ?>
                                <p class="text-warning">Event Closed</p>
                            <?php } ?>
                            <a href="event_details_user.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-info">Details</a>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <script>
    function toggleSidebar() {
        var mySidebar = new bootstrap.Offcanvas(document.getElementById('mySidebar'));
        mySidebar.toggle();
    }

    function applySearchFilterSort() {
        let search = document.getElementById('searchEvent').value.toLowerCase();
        let status = document.getElementById('statusFilter').value;
        let sort = document.getElementById('sortOptions').value;

        let events = document.querySelectorAll('.event-card');

        events.forEach(event => {
            let eventName = event.getAttribute('data-name');
            let eventStatus = event.getAttribute('data-status');
            let matchesSearch = eventName.includes(search);
            let matchesStatus = (status === 'all') || (status === eventStatus);

            if (matchesSearch && matchesStatus) {
                event.style.display = 'block';
            } else {
                event.style.display = 'none';
            }
        });

        let sortedEvents = Array.from(events).sort((a, b) => {
            if (sort.includes('name')) {
                let nameA = a.getAttribute('data-name');
                let nameB = b.getAttribute('data-name');
                return sort === 'name_asc' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
            } else {
                let dateA = new Date(a.getAttribute('data-date'));
                let dateB = new Date(b.getAttribute('data-date'));
                return sort === 'date_asc' ? dateA - dateB : dateB - dateA;
            }
        });

        document.getElementById('eventGrid').innerHTML = '';
        sortedEvents.forEach(event => {
            document.getElementById('eventGrid').appendChild(event);
        });
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        document.getElementById('sortOptions').value = 'name_asc';
        applySearchFilterSort();
    });

    document.getElementById('logoutBtn').addEventListener('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, logout!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    });
    </script>
</body>
</html>
