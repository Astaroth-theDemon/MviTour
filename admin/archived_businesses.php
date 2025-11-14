<?php
include '../db.php';

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_adminacc.php");
    exit();
}

// Fetch all archived businesses
$sql = "SELECT * FROM Businesses WHERE status = 'archived'";
$result = $conn->query($sql);

// Handle success messages
if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Function to get category icon
function getCategoryIcon($category) {
    switch ($category) {
        case 'Hotel': return 'fa-hotel';
        case 'Restaurant': return 'fa-utensils';
        case 'Resort': return 'fa-umbrella-beach';
        case 'Inn': return 'fa-bed';
        case 'Transient House': return 'fa-house';
        case 'Apartelle': return 'fa-building';
        default: return 'fa-store';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Businesses - MViTour</title>
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

        /* Header Styles */
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

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 20px;
        }

        /* Businesses Grid */
        .businesses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .business-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .business-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .business-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .business-content {
            padding: 20px;
        }

        .business-category {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background-color: #007bff;
            color: white;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }

        .business-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .business-location {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .business-price {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #28a745;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        /* Action Buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .edit-btn, .unarchive-btn {
            width: 100%;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
        }

        .edit-btn {
            background-color: #007bff;
            color: white;
        }

        .edit-btn:hover {
            background-color: white;
            color: #007bff;
            border-color: #007bff;
        }

        .unarchive-btn {
            background-color: #28a745;
            color: white;
        }

        .unarchive-btn:hover {
            background-color: white;
            color: #28a745;
            border-color: #28a745;
        }

        /* Header Actions */
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
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: white;
            color: #dc3545;
        }

        /* Breadcrumb */
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

        /* Success Message */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            width: 400px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

    </style>
</head>
<body>
    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <p id="modalMessage"></p>
            <div class="modal-buttons">
                <button class="btn btn-danger" onclick="handleModalNo()">No</button>
                <button class="btn btn-primary" onclick="handleModalYes()">Yes</button>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="logo">
            <img src="../assets/mvitour_logo.png" alt="MViTour Logo">
            <span>MViTour</span>
        </div>
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
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="manage_destinations.php">Manage Destinations</a>
            <i class="fas fa-chevron-right"></i>
            <a href="manage_business.php">Businesses</a>
            <i class="fas fa-chevron-right"></i>
            <span>Archived Businesses</span>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Businesses Grid -->
        <div class="businesses-grid">
            <?php 
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) { 
            ?>
                <div class="business-card">
                    <img src="../uploads/<?php echo htmlspecialchars($row['destination_thumbnail']); ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         class="business-image">
                    <div class="business-content">
                        <div class="business-category">
                            <i class="fas <?php echo getCategoryIcon($row['category']); ?>"></i>
                            <?php echo htmlspecialchars($row['category']); ?>
                        </div>
                        <h3 class="business-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <div class="business-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($row['barangay'] . ', ' . $row['location']); ?></span>
                        </div>
                        <div class="business-price">
                            <i class="fas fa-tag"></i>
                            <span>Starting at ₱<?php echo number_format($row['budget'], 2); ?></span>
                        </div>
                        <div class="action-buttons">
                            <a href="edit_business.php?id=<?php echo $row['business_id']; ?>" class="edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="unarchive-btn" onclick="confirmUnarchive(<?php echo $row['business_id']; ?>)">
                                <i class="fas fa-box-open"></i> Unarchive
                            </button>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<p>No archived businesses found.</p>";
            } 
            ?>
        </div>
    </div>

    <script>
        // Modal handling
        let modalCallback = null;
        const modal = document.getElementById('confirmModal');

        function showConfirmModal(message, callback) {
            document.getElementById('modalMessage').textContent = message;
            modal.style.display = 'block';
            modalCallback = callback;
        }

        function handleModalYes() {
            modal.style.display = 'none';
            if (modalCallback) modalCallback(true);
        }

        function handleModalNo() {
            modal.style.display = 'none';
            if (modalCallback) modalCallback(false);
        }

        // Unarchive confirmation
        function confirmUnarchive(id) {
            showConfirmModal("Are you sure you want to unarchive this business?", (confirmed) => {
                if (confirmed) {
                    window.location.href = `unarchive_business.php?id=${id}`;
                }
            });
        }
    </script>
</body>
</html>