<?php
include 'session_start.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
<div class="sidebar">
    <img src="../csnlogo-removebg.png" alt="Admin Logo" class="img-fluid">
    <h4 class="text-center">Parent Portal - Admin</h4> <!-- Added Parent Portal header -->
    <h3 class="text-center"><?php echo htmlspecialchars($_SESSION['username']); ?></h3> <!-- Changed to h3 for username -->
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="registered_users.php"><i class="fas fa-users"></i> Registered Users</a>
    <a href="parent_participation.php"><i class="fas fa-user-friends"></i> Parent Participation</a>
    <a href="pending_requests.php"><i class="fas fa-file-invoice"></i> Pending Requests</a>
    <a href="create_announcement.php"><i class="fas fa-bullhorn"></i> Announcement</a>
    <a href="create_event.php"><i class="fas fa-calendar-alt"></i> Create Events</a>
    <a href="generate_request.php"><i class="fas fa-file-alt"></i> Generate Request</a>
    <a href="index.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
</body>
</html>
