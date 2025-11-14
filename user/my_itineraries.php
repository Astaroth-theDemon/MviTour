<?php
session_start();
include '../db.php';

$isLoggedIn = isset($_SESSION['user_id']);

if (!isset($_SESSION['user_id'])) {
    header("Location: login_useracc.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch saved itineraries
$stmt = $conn->prepare("SELECT * FROM SavedItineraries WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$itineraries = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Itineraries - MViTour</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Montserrat', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            overflow-x: hidden;
        }

        /* Header styles */
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header a {
            text-decoration: none;
            color: white;
        }
        
        .header a:hover {
            text-decoration: underline;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 400;
            display: flex;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .nav-link:hover {
            text-decoration: none;
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 1px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .profile-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 10px;
            z-index: 1000;
            margin-top: 10px;
            overflow: hidden;
        }

        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .dropdown-content a:hover {
            background-color: #f8f9fa;
            color: #007bff;
            transform: translateX(5px);
        }

        .dropdown-content {
            display: none;
        }

        .dropdown-content.show {
            display: block;
        }

        /* Arrow pointer for dropdown */
        .dropdown-content::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 20px;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid white;
        }

        .container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 30px;
        }

        .page-header {
            text-align: center;
            margin: 100px 20px 40px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 2px solid #007bff;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: #333;
            font-family: 'Playfair Display', serif;
            margin-bottom: 15px;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .itineraries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
            gap: 25px;
            padding: 20px;
            margin: 0 auto;
            max-width: 1400px;
        }

        .itinerary-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .itinerary-card:hover {
            transform: translateY(-5px);
            border-color: #007bff;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .itinerary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #007bff, #00bfff);
        }

        .itinerary-card h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 15px;
            font-family: 'Playfair Display', serif;
        }

        .card-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
        }

        .detail-item i {
            color: #007bff;
            font-size: 1.1rem;
        }

        .date {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
            padding: 5px 15px;
            background: #f8f9fa;
            border-radius: 20px;
        }

        .view-btn {
            flex: 1;
            justify-content: center;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Montserrat', Arial, sans-serif;
        }

        .view-btn {
            background: #007bff;
            color: white;
            border: 2px solid #007bff;
        }

        .view-btn:hover {
            background: white;
            color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .no-itineraries {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 20px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .no-itineraries i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 20px;
        }

        .no-itineraries h2 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .no-itineraries p {
            color: #666;
        }

        .create-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 25px;
            background: #28a745;
            color: white;
            border: 2px solid #28a745;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .create-btn:hover {
            background: white;
            color: #28a745;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: space-between;
        }

        .delete-btn {
            flex: 1;
            justify-content: center;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Montserrat', Arial, sans-serif;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: 2px solid #dc3545;
        }

        .delete-btn:hover {
            background: white;
            color: #dc3545;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }

        .modal-buttons button {
            padding: 10px 20px;
            border-radius: 25px;
            border: 2px solid;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .confirm-delete {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .confirm-delete:hover {
            background: white;
            color: #dc3545;
        }

        .cancel-delete {
            background: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        .cancel-delete:hover {
            background: white;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <a href="homepage.php" class="logo">
            <img src="../assets/mvitour_logo.png" alt="MViTour Logo">
            MViTour
        </a>
        <div class="header-nav">
            <a href="destination_listing.php" class="nav-link">
                <i class="fas fa-list-ul"></i> Destinations
            </a>
            <?php if ($isLoggedIn): ?>
                <div class="profile-dropdown">
                    <button type="button" class="profile-btn" aria-label="Profile menu">
                        <i class="fas fa-user"></i>
                    </button>
                    <div class="dropdown-content" aria-label="Profile options">
                        <a href="my_itineraries.php">
                            <i class="fas fa-map-marked-alt"></i> My Itineraries
                        </a>
                        <a href="edit_preferences.php">
                            <i class="fas fa-cog"></i> Edit Preferences
                        </a>
                        <a href="logout_useracc.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login_useracc.php" class="nav-link">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </header>

    <div class="page-header">
        <h1>My Saved Itineraries</h1>
        <p>View and manage your personalized travel plans</p>
    </div>

    <?php if (empty($itineraries)): ?>
        <div class="no-itineraries">
            <i class="fas fa-route"></i>
            <h2>No Saved Itineraries Yet</h2>
            <p>Start planning your next adventure!</p>
            <a href="get_itinerary.php" class="create-btn">
                <i class="fas fa-plus"></i> Create New Itinerary
            </a>
        </div>
    <?php else: ?>

        <div class="itineraries-grid">
            <?php foreach ($itineraries as $itinerary): ?>
                <?php $data = json_decode($itinerary['itinerary_data'], true); ?>
                <div class="itinerary-card" data-id="<?php echo $itinerary['itinerary_id']; ?>">
                    <div class="date">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('F j, Y', strtotime($itinerary['created_at'])); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($data['destination']); ?> Trip</h3>
                    <div class="card-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo htmlspecialchars($data['duration']); ?> days</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span><?php echo htmlspecialchars($data['people']); ?> travelers</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo date('M j', strtotime($data['start_date'])); ?> - <?php echo date('M j, Y', strtotime($data['end_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-wallet"></i>
                            <span>₱<?php echo number_format($data['budget']); ?></span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="view_itinerary.php?id=<?php echo $itinerary['itinerary_id']; ?>" class="view-btn">
                            <i class="fas fa-eye"></i> View Itinerary
                        </a>
                        <button class="delete-btn" onclick="deleteItinerary(<?php echo $itinerary['itinerary_id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Delete Itinerary</h2>
            <p>Are you sure you want to delete this itinerary? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="confirm-delete" onclick="confirmDelete()">Delete</button>
                <button class="cancel-delete" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileBtn = document.querySelector('.profile-btn');
            const dropdownContent = document.querySelector('.dropdown-content');

            if (profileBtn && dropdownContent) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownContent.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileBtn.contains(e.target)) {
                        dropdownContent.classList.remove('show');
                    }
                });
            }
        });

        let currentDeleteId = null;
        const modal = document.getElementById('deleteModal');

        function deleteItinerary(itineraryId) {
            currentDeleteId = itineraryId;
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
            currentDeleteId = null;
        }

        function confirmDelete() {
            if (!currentDeleteId) return;

            fetch('delete_itinerary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `itinerary_id=${currentDeleteId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[data-id="${currentDeleteId}"]`);
                    if (card) {
                        card.remove();
                    }
                    closeModal();
                    location.reload(); // Reload to update the page
                } else {
                    alert('Error deleting itinerary');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting itinerary');
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

</body>
</html>