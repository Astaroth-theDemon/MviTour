<?php
session_start();
include '../db.php';

$isLoggedIn = isset($_SESSION['user_id']);

    // Fetch all active destinations (both tourist spots and businesses)
    $sql_tourist_spots = "SELECT 
        'tourist' as type,
        tourist_spot_id as id,
        name,
        category,
        barangay,
        location,
        destination_thumbnail,
        entrance_fee as price
        FROM Tourist_Spots 
        WHERE status = 'active'";

    $sql_businesses = "SELECT 
        'business' as type,
        business_id as id,
        name,
        category,
        barangay,
        location,
        destination_thumbnail,
        budget as price
        FROM Businesses 
        WHERE status = 'active'";

    $sql = "$sql_tourist_spots UNION ALL $sql_businesses";
    $result = $conn->query($sql);
    $destinations = $result->fetch_all(MYSQLI_ASSOC);

    // Get unique categories for filter
    $categories = array_unique(array_column($destinations, 'category'));
    $locations = array_unique(array_column($destinations, 'location'));

    function formatPrice($price, $type) {
        if ($type === 'tourist') {
            return '₱' . number_format($price) . ' entrance fee';
        } else {
            return 'Starting at ₱' . number_format($price);
        }
    }

    $tourist_categories = array_unique(array_filter(array_map(function($dest) {
        return $dest['type'] === 'tourist' ? $dest['category'] : null;
    }, $destinations)));
    
    $business_categories = array_unique(array_filter(array_map(function($dest) {
        return $dest['type'] === 'business' ? $dest['category'] : null;
    }, $destinations)));

    function getCategoryIcon($category, $type) {
        if ($type === 'tourist') {
            switch ($category) {
                case 'Religious Site':
                    return 'fa-church';
                case 'Museum':
                    return 'fa-landmark';
                case 'Historical Road':
                    return 'fa-road';
                case 'Structures and Buildings':
                    return 'fa-building';
                case 'Beaches':
                    return 'fa-umbrella-beach';
                case 'Parks':
                    return 'fa-tree';
                case 'Falls':
                    return 'fa-water';
                case 'Nature Trail':
                    return 'fa-mountain';
                case 'Camping Ground':
                    return 'fa-campground';
                case 'Recreational Activities':
                    return 'fa-hiking';
                default:
                    return 'fa-monument';
            }
        } else {
            // Business categories
            switch ($category) {
                case 'Hotel':
                    return 'fa-hotel';
                case 'Restaurant':
                    return 'fa-utensils';
                case 'Resort':
                    return 'fa-umbrella-beach';
                case 'Inn':
                    return 'fa-bed';
                case 'Transient House':
                    return 'fa-house';
                case 'Apartelle':
                    return 'fa-building';
                default:
                    return 'fa-store';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations - MViTour</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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

        .destinations-container {
            max-width: 1400px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            text-decoration: none;
            color: inherit;
        }

        .destination-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .destination-card .explore-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .destination-card .explore-content {
            padding: 20px;
        }

        .explore-category {
            background: linear-gradient(45deg, #007bff, #00bfff);   
        }

        .destination-card .explore-category {
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

        .destination-card .explore-category i {
            font-size: 0.9rem;
        }

        .destination-card .explore-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .destination-card .explore-location {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }

        .destination-card .explore-price {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #28a745;
            font-size: 0.9rem;
            margin-top: 10px;
            font-weight: 500;
            padding-top: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .destination-card .explore-price i {
            font-size: 0.9rem;
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

    <div class="destinations-container">
        <div class="filter-section">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search for destinations, categories, or locations...">
            </div>
            <div class="filter-controls">
                <div class="filter-group">
                    <label>Type</label>
                    <select id="typeFilter">
                        <option value="">All Types</option>
                        <option value="tourist">Tourist Spot</option>
                        <option value="business">Business</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Category</label>
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach($tourist_categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" data-type="tourist">
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                        <?php foreach($business_categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" data-type="business">
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Location</label>
                    <select id="locationFilter">
                        <option value="">All Locations</option>
                        <?php foreach($locations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location); ?>">
                                <?php echo htmlspecialchars($location); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Sort By</label>
                    <select id="sortBy">
                        <option value="name">Name</option>
                        <option value="price-low">Price (Low to High)</option>
                        <option value="price-high">Price (High to Low)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="destinations-grid">
            <?php foreach($destinations as $destination): ?>
                <a href="destination_details.php?id=<?php echo htmlspecialchars($destination['id']); ?>&type=<?php echo htmlspecialchars($destination['type']); ?>" class="destination-card" data-type="<?php echo htmlspecialchars($destination['type']); ?>">
                    <img src="../uploads/<?php echo htmlspecialchars($destination['destination_thumbnail']); ?>" 
                        alt="<?php echo htmlspecialchars($destination['name']); ?>"
                        class="explore-image">
                    <div class="explore-content">
                        <div class="explore-category">
                            <i class="fas <?php echo getCategoryIcon($destination['category'], $destination['type']); ?>"></i>
                            <?php echo htmlspecialchars($destination['category']); ?>
                        </div>
                        <h3 class="explore-title"><?php echo htmlspecialchars($destination['name']); ?></h3>
                        <div class="explore-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($destination['barangay'] . ', ' . $destination['location']); ?></span>
                        </div>
                        <div class="explore-price">
                            <i class="fas fa-tag"></i>
                            <span><?php echo formatPrice($destination['price'], $destination['type']); ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const typeFilter = document.getElementById('typeFilter')
        const categoryFilter = document.getElementById('categoryFilter');
        const locationFilter = document.getElementById('locationFilter');
        const sortBySelect = document.getElementById('sortBy');
        const destinationsGrid = document.querySelector('.destinations-grid');

        // Function to extract price number from text
        function extractPrice(priceText) {
            // Remove all non-numeric characters except decimal point
            const price = priceText.replace(/[^\d.]/g, '');
            return parseFloat(price);
        }

        // Store all destination cards for filtering
        let destinationCards = Array.from(document.querySelectorAll('.destination-card'));

        // Add this function to update category options based on type
        function updateCategoryOptions() {
            const selectedType = typeFilter.value;
            const categoryOptions = Array.from(categoryFilter.options);
            
            categoryOptions.forEach(option => {
                if (option.value === '') {
                    // Always show "All Categories" option
                    option.style.display = '';
                    return;
                }
                
                const optionType = option.getAttribute('data-type');
                if (!selectedType || selectedType === optionType) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                    if (option.selected) {
                        categoryFilter.value = ''; // Reset category selection if hidden
                    }
                }
            });
        }
        
        // Function to filter and sort destinations
        function updateDestinations() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedType = typeFilter.value.toLowerCase();
            const selectedCategory = categoryFilter.value.toLowerCase();
            const selectedLocation = locationFilter.value.toLowerCase();
            
            let filteredDestinations = destinationCards.filter(card => {
                const name = card.querySelector('.explore-title').textContent.toLowerCase();
                const category = card.querySelector('.explore-category').textContent.toLowerCase();
                const location = card.querySelector('.explore-location span').textContent.toLowerCase();
                const type = card.getAttribute('data-type').toLowerCase();
                
                const matchesSearch = name.includes(searchTerm) || 
                                    category.includes(searchTerm) || 
                                    location.includes(searchTerm);
                const matchesType = !selectedType || type === selectedType;
                const matchesCategory = !selectedCategory || category.includes(selectedCategory);
                const matchesLocation = !selectedLocation || location.includes(selectedLocation);
                
                return matchesSearch && matchesType && matchesCategory && matchesLocation;
            });

            // Sort destinations
            const sortBy = sortBySelect.value;
            filteredDestinations.sort((a, b) => {
                switch(sortBy) {
                    case 'name':
                        return a.querySelector('.explore-title').textContent
                            .localeCompare(b.querySelector('.explore-title').textContent);
                    
                    case 'price-low':
                        const priceA_low = extractPrice(a.querySelector('.explore-price span').textContent);
                        const priceB_low = extractPrice(b.querySelector('.explore-price span').textContent);
                        return priceA_low - priceB_low;
                    
                    case 'price-high':
                        const priceA_high = extractPrice(a.querySelector('.explore-price span').textContent);
                        const priceB_high = extractPrice(b.querySelector('.explore-price span').textContent);
                        return priceB_high - priceA_high;
                    
                    default:
                        return 0;
                }
            });

            // Clear and update grid
            destinationsGrid.innerHTML = '';
            filteredDestinations.forEach(card => {
                destinationsGrid.appendChild(card);
            });
        }

        // Add type filter event listener
        typeFilter.addEventListener('change', () => {
            updateCategoryOptions();
            updateDestinations();
        });

        updateCategoryOptions();

        // Add event listeners
        searchInput.addEventListener('input', updateDestinations);
        categoryFilter.addEventListener('change', updateDestinations);
        locationFilter.addEventListener('change', updateDestinations);
        sortBySelect.addEventListener('change', updateDestinations);

        // Initial sort
        updateDestinations();

        // Profile Dropdown functionality
        const profileBtn = document.querySelector('.profile-btn');
        const dropdownContent = document.querySelector('.dropdown-content');

        if (profileBtn && dropdownContent) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownContent.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (!profileBtn.contains(e.target)) {
                    dropdownContent.classList.remove('show');
                }
            });
        }
    });
    </script>
</body>
</html>