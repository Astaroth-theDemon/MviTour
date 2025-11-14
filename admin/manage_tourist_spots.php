<?php
include '../db.php';

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_adminacc.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch all active tourist spots from the database
$sql = "SELECT * FROM Tourist_Spots WHERE status = 'active'";
$result = $conn->query($sql);

// Handle success messages
if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Function to get category icon
function getCategoryIcon($category) {
    switch ($category) {
        case 'Religious Site': return 'fa-church';
        case 'Museum': return 'fa-landmark';
        case 'Historical Road': return 'fa-road';
        case 'Structures and Buildings': return 'fa-building';
        case 'Beaches': return 'fa-umbrella-beach';
        case 'Parks': return 'fa-tree';
        case 'Falls': return 'fa-water';
        case 'Nature Trail': return 'fa-mountain';
        case 'Camping Ground': return 'fa-campground';
        case 'Recreational Activities': return 'fa-hiking';
        default: return 'fa-monument';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tourist Spots - MViTour</title>
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

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #007bff;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #007bff;
            border-radius: 30px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
        }

        .search-bar input:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
        }

        .filter-controls {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
        }

        /* Destinations Grid */
        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .destination-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .destination-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .destination-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .destination-content {
            padding: 20px;
        }

        .destination-category {
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

        .destination-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .destination-location {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .destination-price {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #28a745;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .destination-price i {
            font-size: 0.9rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;  /* Makes buttons equal width */
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .edit-btn, .archive-btn {
            width: 100%;  /* Makes buttons take full width of their grid cell */
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

        .archive-btn {
            background-color: #dc3545;
            color: white;
        }

        .archive-btn:hover {
            background-color: white;
            color: #dc3545;
            border-color: #dc3545;
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

        /* View Archived Button */
        .top-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .view-archived-btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            background-color: #6c757d;
            color: white;
            border: 2px solid #6c757d;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .view-archived-btn:hover {
            background-color: white;
            color: #6c757d;
        }

        .view-archived-btn i {
            margin-right: 8px;
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
            <span>Tourist Spots</span>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- View Archived Button -->
        <div class="top-actions">
            <a href="archived_tourist_spots.php" class="view-archived-btn">
                <i class="fas fa-archive"></i>
                View Archived Tourist Spots
            </a>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search for tourist spots, categories, or locations...">
            </div>
            <div class="filter-controls">
                <div class="filter-group">
                    <label>Category</label>
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="Religious Site">Religious Site</option>
                        <option value="Nature Trail">Nature Trail</option>
                        <option value="Recreational Activities">Recreational Activities</option>
                        <option value="Historical Road">Historical Road</option>
                        <option value="Falls">Falls</option>
                        <option value="Museum">Museum</option>
                        <option value="Camping Ground">Camping Ground</option>
                        <option value="Parks">Parks</option>
                        <option value="Beach">Beach</option>
                        <option value="Structures and Buildings">Structures and Buildings</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Location</label>
                    <select id="locationFilter">
                        <option value="">All Locations</option>
                        <option value="Vigan">Vigan</option>
                        <option value="Bantay">Bantay</option>
                        <option value="Santa Catalina">Santa Catalina</option>
                        <option value="San Vicente">San Vicente</option>
                        <option value="Caoayan">Caoayan</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Sort By</label>
                    <select id="sortBy">
                        <option value="name">Name</option>
                        <option value="entrance-fee-low">Entrance Fee (Low to High)</option>
                        <option value="entrance-fee-high">Entrance Fee (High to Low)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Destinations Grid -->
        <div class="destinations-grid">
            <?php 
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) { 
            ?>
                <div class="destination-card" 
                     data-name="<?php echo htmlspecialchars($row['name']); ?>"
                     data-category="<?php echo htmlspecialchars($row['category']); ?>"
                     data-location="<?php echo htmlspecialchars($row['location']); ?>"
                     data-fee="<?php echo htmlspecialchars($row['entrance_fee']); ?>">
                    <img src="../uploads/<?php echo htmlspecialchars($row['destination_thumbnail']); ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         class="destination-image">
                    <div class="destination-content">
                        <div class="destination-category">
                            <i class="fas <?php echo getCategoryIcon($row['category']); ?>"></i>
                            <?php echo htmlspecialchars($row['category']); ?>
                        </div>
                        <h3 class="destination-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <div class="destination-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($row['barangay'] . ', ' . $row['location']); ?></span>
                        </div>
                        <div class="destination-price">
                            <i class="fas fa-tag"></i>
                            <span>₱<?php echo number_format($row['entrance_fee'], 2); ?> entrance fee</span>
                        </div>
                        <div class="action-buttons">
                            <a href="edit_tourist_spot.php?id=<?php echo $row['tourist_spot_id']; ?>" class="edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="archive-btn" onclick="confirmArchive(<?php echo $row['tourist_spot_id']; ?>)">
                                <i class="fas fa-archive"></i> Archive
                            </button>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<p>No tourist spots found.</p>";
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

        // Archive confirmation
        function confirmArchive(id) {
            showConfirmModal("Are you sure you want to archive this tourist spot?", (confirmed) => {
                if (confirmed) {
                    window.location.href = `archive_tourist_spot.php?id=${id}`;
                }
            });
        }

        // Search and Filter functionality
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const locationFilter = document.getElementById('locationFilter');
        const sortBySelect = document.getElementById('sortBy');
        const destinationsGrid = document.querySelector('.destinations-grid');
        let destinationCards = Array.from(document.querySelectorAll('.destination-card'));

        function updateDestinations() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value.toLowerCase();
            const selectedLocation = locationFilter.value.toLowerCase();
            const sortBy = sortBySelect.value;

            // Filter destinations
            let filteredDestinations = destinationCards.filter(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const category = card.getAttribute('data-category').toLowerCase();
                const location = card.getAttribute('data-location').toLowerCase();

                const matchesSearch = name.includes(searchTerm) ||
                                    category.includes(searchTerm) ||
                                    location.includes(searchTerm);
                const matchesCategory = !selectedCategory || category === selectedCategory;
                const matchesLocation = !selectedLocation || location === selectedLocation;

                return matchesSearch && matchesCategory && matchesLocation;
            });

            // Sort destinations
            filteredDestinations.sort((a, b) => {
                switch(sortBy) {
                    case 'name':
                        return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                    case 'entrance-fee-low':
                        return parseFloat(a.getAttribute('data-fee')) - parseFloat(b.getAttribute('data-fee'));
                    case 'entrance-fee-high':
                        return parseFloat(b.getAttribute('data-fee')) - parseFloat(a.getAttribute('data-fee'));
                    default:
                        return 0;
                }
            });

            // Update display
            destinationsGrid.innerHTML = '';
            if (filteredDestinations.length > 0) {
                filteredDestinations.forEach(card => destinationsGrid.appendChild(card));
            } else {
                destinationsGrid.innerHTML = '<p>No tourist spots found matching your criteria.</p>';
            }
        }

        // Event listeners
        searchInput.addEventListener('input', updateDestinations);
        categoryFilter.addEventListener('change', updateDestinations);
        locationFilter.addEventListener('change', updateDestinations);
        sortBySelect.addEventListener('change', updateDestinations);

        // Initialize sorting
        updateDestinations();
    </script>
</body>
</html>
