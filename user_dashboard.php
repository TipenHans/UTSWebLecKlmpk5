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

    $stmt = $pdo->query("SELECT * FROM events WHERE status = 'open' ORDER BY start_date ASC");
    $events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
        <div class="content-container container mt-2" id="main">
            <div class="header d-flex justify-content-between align-items-center mb-4">
                <img src="asset/menu.png" alt="Menu" class="img-fluid" id="menuButton" onclick="toggleSidebar()">
                <h2>Available Events</h2>
                <a href="view_profile.php"><img src="uploads/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png'; ?>" alt="Profile" class="profile-icon img-thumbnail"></a>
            </div>
            <div class="filter-section">
                <input type="text" id="searchEvent" class="form-control" placeholder="Search event by name..." onkeyup="applySearchFilterSort()">
                <select id="statusFilter" class="form-control" onchange="applySearchFilterSort()">
                    <option value="all">All Events</option>
                    <option value="available">Available Events</option>
                    <option value="full">Full Events</option>
                </select>
                <select id="sortOptions" class="form-control" onchange="applySearchFilterSort()">
                    <option value="#" selected disabled>Sort By:</option>
                    <option value="name_asc">Name A-Z</option>
                    <option value="name_desc">Name Z-A</option>
                    <option value="date_asc">Starting Soonest</option>
                    <option value="date_desc">Starting Latest</option>
                </select>
            </div>
            <div class="event-grid mb-5">
                <?php if (empty($events)) { ?>
                    <p>No events available.</p>
                <?php } else { ?>
                    <?php foreach ($events as $event) { ?>
                        <div class="event-card" data-name="<?php echo strtolower($event['event_name']); ?>" data-status="<?php echo $event['status']; ?>" data-date="<?php echo $event['start_date']; ?>">
                            <img src="uploads/<?php echo $event['banner']; ?>" alt="Event Image" id="event-img">
                            <h4><?php echo $event['event_name']; ?></h4>
                            <p><?php echo $event['start_date']; ?></p>
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
</body>
</html>
