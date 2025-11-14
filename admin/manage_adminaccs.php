<?php
include '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_adminacc.php");
    exit();
}

// Handle account deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if ($delete_id == $_SESSION['admin_id']) {
        $message = "You cannot delete your own account!";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("DELETE FROM Admins WHERE admin_id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = "Admin account deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Error deleting admin account.";
            $messageType = "error";
        }
        $stmt->close();
    }
}

// Fetch all admin accounts
$query = "SELECT admin_id, username, email FROM admins";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Accounts - MViTour</title>
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

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-btn {
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
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: white;
            color: #dc3545;
            border-color: #dc3545;
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 20px;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
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
            color: #6c757d;
            font-size: 0.8rem;
        }

        /* Content Section */
        .content-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #007bff;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: #333;
        }

        .add-admin-btn {
            padding: 12px 25px;
            background-color: #28a745;
            color: white;
            border: 2px solid #28a745;
            border-radius: 30px;
            font-size: 0.9rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .add-admin-btn:hover {
            background-color: white;
            color: #28a745;
        }

        /* Table Styles */
        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
        }

        .admin-table th,
        .admin-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .admin-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .admin-table tr:hover {
            background-color: #f8f9fa;
        }

        .admin-table th:last-child,
        .admin-table td:last-child {
            width: 100px; /* Set fixed width for actions column */
            text-align: center; /* Center align the actions */
        }

        /* Action Buttons */
        .action-btn {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: 2px solid #dc3545;
        }

        .delete-btn:hover {
            background-color: white;
            color: #dc3545;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #adb5bd;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <p id="modalMessage"></p>
            <div class="modal-buttons">
                <button class="action-btn delete-btn" onclick="handleModalNo()">No</button>
                <button class="add-admin-btn" onclick="handleModalYes()">Yes</button>
            </div>
        </div>
    </div>

    <!-- Header -->
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
            <a href="logout_admin.php" class="header-btn logout-btn">
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
            <span>Manage Admin Accounts</span>
        </div>

        <!-- Content Section -->
        <div class="content-section">
            <!-- Messages -->
            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Section Header -->
            <div class="section-header">
                <h1 class="section-title">Admin Accounts</h1>
                <a href="create_adminacc.php" class="add-admin-btn">
                    <i class="fas fa-plus"></i>
                    Add Admin
                </a>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <!-- Admin Table -->
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <?php if ($row['admin_id'] != $_SESSION['admin_id']): ?>
                                        <a href="#" class="action-btn delete-btn" 
                                           onclick="confirmDelete(<?php echo $row['admin_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <p>No admin accounts found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Modal handling
        const modal = document.getElementById('confirmModal');
        let modalCallback = null;

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

        // Delete confirmation
        function confirmDelete(adminId) {
            showConfirmModal('Are you sure you want to delete this admin account?', (confirmed) => {
                if (confirmed) {
                    window.location.href = `?delete_id=${adminId}`;
                }
            });
        }

        // Navigation confirmation
        document.querySelector('.logout-btn').addEventListener('click', function(e) {
            e.preventDefault();
            showConfirmModal("Are you sure you want to log out?", (confirmed) => {
                if (confirmed) {
                    window.location.href = this.getAttribute('href');
                }
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }

        // Handle ESC key press
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'block') {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>