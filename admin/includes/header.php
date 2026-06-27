<?php
// Ensure this is only included and not accessed directly
if (!defined('ADMIN_DASHBOARD')) {
    define('ADMIN_DASHBOARD', true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineTime Admin Dashboard</title>
    <!-- Use CDN Bootstrap for rapid UI dev in Admin panel -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0a0a0f;
            --sidebar-bg: #14141c;
            --card-bg: #1e1e28;
            --accent-primary: #e50914; /* Netflix-style red */
            --accent-hover: #ff1e27;
            --text-primary: #f0f0f5;
            --text-secondary: #9a9ab0;
            --border-color: #2a2a35;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            overflow-x: hidden;
            margin: 0;
            display: flex;
        }

        /* Sidebar UI */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-color);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        .sidebar-header h3 {
            color: var(--accent-primary);
            font-weight: 800;
            letter-spacing: 1px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-menu {
            padding: 20px 0;
            list-style: none;
            margin: 0;
            flex-grow: 1;
        }

        .nav-item {
            padding: 0 16px;
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            gap: 15px;
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(229, 9, 20, 0.1);
            color: var(--accent-primary);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            width: 4px;
            height: 30px;
            background-color: var(--accent-primary);
            border-radius: 0 4px 4px 0;
        }

        .user-panel {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Main Content UI */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            height: 70px;
            background-color: rgba(20, 20, 28, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .content-body {
            padding: 30px;
            flex-grow: 1;
        }

        /* Global UI Elements */
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            color: var(--text-primary);
        }

        .card-header {
            background-color: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid var(--border-color);
            padding: 20px;
            font-weight: 600;
        }

        .table {
            color: var(--text-primary);
        }

        .table th {
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom-color: var(--border-color);
        }

        .table td {
            border-bottom-color: var(--border-color);
            vertical-align: middle;
        }

        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(255, 255, 255, 0.02);
            color: var(--text-primary);
        }

        .table-hover > tbody > tr:hover > * {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
        }

        .btn-primary {
            background-color: var(--accent-primary);
            border-color: var(--accent-primary);
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
        }

        /* Modal dark theme styling */
        .modal-content {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
        }
        .modal-header {
            border-bottom: 1px solid var(--border-color);
        }
        .modal-footer {
            border-top: 1px solid var(--border-color);
        }
        .form-control, .form-select {
            background-color: rgba(0,0,0,0.2);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        .form-control:focus, .form-select:focus {
            background-color: rgba(0,0,0,0.3);
            border-color: var(--accent-primary);
            color: var(--text-primary);
            box-shadow: none;
        }
    </style>
</head>
<body>

    <!-- Sidebar Start -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-video"></i> CineTime</h3>
        </div>
        
        <ul class="nav-menu">
            <?php 
                $current_page = basename($_SERVER['PHP_SELF']); 
            ?>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="movies.php" class="nav-link <?= ($current_page == 'movies.php') ? 'active' : '' ?>">
                    <i class="fas fa-film"></i> Movies
                </a>
            </li>
            <li class="nav-item">
                <a href="tmdb_import.php" class="nav-link <?= ($current_page == 'tmdb_import.php') ? 'active' : '' ?>">
                    <i class="fas fa-cloud-download-alt"></i> Import TMDB
                </a>
            </li>
            <li class="nav-item">
                <a href="theaters.php" class="nav-link <?= ($current_page == 'theaters.php' || $current_page == 'seat_maps.php') ? 'active' : '' ?>">
                    <i class="fas fa-building"></i> Theaters & Maps
                </a>
            </li>
            <li class="nav-item">
                <a href="shows.php" class="nav-link <?= ($current_page == 'shows.php') ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i> Shows
                </a>
            </li>
            <li class="nav-item">
                <a href="bookings.php" class="nav-link <?= ($current_page == 'bookings.php') ? 'active' : '' ?>">
                    <i class="fas fa-ticket-alt"></i> Bookings
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link <?= ($current_page == 'users.php') ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a href="payments.php" class="nav-link <?= ($current_page == 'payments.php') ? 'active' : '' ?>">
                    <i class="fas fa-money-check-alt"></i> Payments
                </a>
            </li>
        </ul>

        <div class="user-panel">
            <i class="fas fa-user-circle fa-2x text-secondary"></i>
            <div>
                <div style="font-weight: 600; font-size: 0.9rem;">Admin Panel</div>
                <a href="logout.php" class="text-danger text-decoration-none" style="font-size: 0.8rem;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
    <!-- Sidebar End -->

    <!-- Main Content Start -->
    <div class="main-content">
        <div class="top-navbar">
            <h1 class="page-title">
                <?php
                switch($current_page) {
                    case 'dashboard.php': echo 'Dashboard Overview'; break;
                    case 'movies.php': echo 'Manage Movies'; break;
                    case 'shows.php': echo 'Manage Shows'; break;
                    case 'bookings.php': echo 'Booking Records'; break;
                    case 'users.php': echo 'Registered Users'; break;
                    case 'payments.php': echo 'Payment Transactions'; break;
                    default: echo 'CineTime Admin';
                }
                ?>
            </h1>
            <div>
                <span class="badge bg-danger">LIVE</span>
            </div>
        </div>
        
        <div class="content-body">
