<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_adminacc.php"); // Redirect to login page if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MViTour</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            min-height: 100vh;
        }

        /* Fixed Header */
        .header {
            background-color: #007bff;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: white;
        }

        .logo img {
            height: 40px;
        }

        .logo span {
            font-size: 1.8rem;
            font-weight: 500;
            font-family: 'Montserrat', sans-serif;
        }

        /* Content Container */
        .main-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 20px;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #007bff;
        }

        .welcome-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 15px;
        }

        .welcome-section p {
            color: #666;
            font-size: 1.1rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
            text-align: center;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
        }

        .action-card i {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 15px;
        }

        .action-card h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
        }

        .action-card p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: 2px solid white;
            border-radius: 30px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background-color: white;
            color: #dc3545;
            border-color: #dc3545;
        }

        .logout-btn i {
            font-size: 1rem;
        }

        .admin-info {
            color: white;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-info i {
            font-size: 1.2rem;
        }

    </style>
</head>
<body>
    <!-- Fixed Header -->
    <header class="header">
        <a class="logo">
            <img src="../assets/mvitour_logo.png" alt="MViTour Logo">
            <span>MViTour</span>
        </a>
        <div class="header-actions">
            <div class="admin-info">
                <i class="fas fa-user-shield"></i>
                <span>Administrator</span>
            </div>
            <a href="logout_admin.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <div class="welcome-section">
            <h1>Welcome to Admin Dashboard</h1>
            <p>Manage and monitor your tourism platform with ease</p>
        </div>

        <div class="actions-grid">
            <a href="choose_destination.php" class="action-card">
                <i class="fas fa-plus-circle"></i>
                <h3>Add Destination</h3>
                <p>Add new tourist spots or businesses to the platform</p>
            </a>

            <a href="manage_destinations.php" class="action-card">
                <i class="fas fa-map-marked-alt"></i>
                <h3>Manage Destinations</h3>
                <p>Edit, update, or archive existing destinations</p>
            </a>

            <a href="manage_adminaccs.php" class="action-card">
                <i class="fas fa-users-cog"></i>
                <h3>Manage Admins</h3>
                <p>Control admin accounts and permissions</p>
            </a>

            <a href="manage_featured_attractions.php" class="action-card">
                <i class="fas fa-star"></i>
                <h3>Featured Attractions</h3>
                <p>Manage featured attractions and highlights of Ilocos Sur</p>
            </a>
        </div>
    </div>
</body>
</html>