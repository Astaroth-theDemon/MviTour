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
    <title>Choose Destination Type - MViTour</title>
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

        .choice-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #007bff;
            text-align: center;
        }

        .choice-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 30px;
        }

        .choice-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .choice-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #eee;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .choice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
        }

        .choice-card i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 20px;
        }

        .choice-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }

        .choice-card p {
            color: #666;
            font-size: 1rem;
            line-height: 1.6;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
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

        .header-btn i {
            font-size: 1rem;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #666;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb a:hover {
            color: #0056b3;
        }

        .breadcrumb i {
            font-size: 0.8rem;
            color: #999;
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
        <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <span>Choose Destination Type</span>
        </div>

        <div class="choice-section">
            <h1>Choose Destination Type</h1>
            <div class="choice-grid">
                <a href="add_tourist_spot.php" class="choice-card">
                    <i class="fas fa-landmark"></i>
                    <h3>Tourist Spot</h3>
                    <p>Add historical sites, natural attractions, parks, and other tourist destinations</p>
                </a>
                <a href="add_business.php" class="choice-card">
                    <i class="fas fa-store"></i>
                    <h3>Business</h3>
                    <p>Add hotels, restaurants, resorts, and other local businesses</p>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Confirmation for navigation
        function confirmAction(message, url) {
            if (confirm(message)) {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>
