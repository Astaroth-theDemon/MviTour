<?php
session_start();
include '../db.php';

$isLoggedIn = isset($_SESSION['user_id']);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_useracc.php");
    exit();
}

function getCategoryIcon($category, $type) {
    if ($type === 'tourist') {
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
    } else {
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
}

function getRecommendedDestinations($user_id, $conn, $limit = null) {
    error_log("Getting recommendations for user_id: " . $user_id);
    
    // Get user preferences
    $stmt = $conn->prepare("
        SELECT preference_type, preference_value 
        FROM UserPreferences 
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $preferences = [
        'activity' => [],
        'category' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $preferences[$row['preference_type']][] = $row['preference_value'];
    }
    
    // If no preferences at all, return empty array
    if (empty($preferences['category']) && empty($preferences['activity'])) {
        return [];
    }
    
    try {
        // Build query for tourist spots based on both categories and activities
        $conditions = [];
        $params = [];
        $types = '';

        // Check if Restaurant is in the preferences
        $includeRestaurants = in_array('Restaurant', $preferences['category']);
        
        // Remove 'Restaurant' from category preferences for tourist spots query
        if ($includeRestaurants) {
            $preferences['category'] = array_filter($preferences['category'], function($category) {
                return $category !== 'Restaurant';
            });
        }

        // Add category conditions if there are category preferences
        if (!empty($preferences['category'])) {
            $categoryPlaceholders = str_repeat('?,', count($preferences['category']) - 1) . '?';
            $conditions[] = "t.category IN ($categoryPlaceholders)";
            $params = array_merge($params, $preferences['category']);
            $types .= str_repeat('s', count($preferences['category']));
        }

        // Add activity-based conditions if there are activity preferences
        if (!empty($preferences['activity'])) {
            $activityCategoryMap = [
                'Cultural' => ['Religious Site', 'Museum'],
                'Historical' => ['Historical Road', 'Structures and Buildings'],
                'Nature' => ['Parks', 'Nature Trail', 'Beaches'],
                'Adventure' => ['Camping Ground', 'Recreational Activities'],
                'Educational' => ['Museum'],
                'Relaxation' => ['Parks', 'Beaches']
            ];

            $relevantCategories = [];
            foreach ($preferences['activity'] as $activity) {
                if (isset($activityCategoryMap[$activity])) {
                    $relevantCategories = array_merge($relevantCategories, $activityCategoryMap[$activity]);
                }
            }
            
            $relevantCategories = array_unique($relevantCategories);
            
            if (!empty($relevantCategories)) {
                $activityPlaceholders = str_repeat('?,', count($relevantCategories) - 1) . '?';
                $conditions[] = "t.category IN ($activityPlaceholders)";
                $params = array_merge($params, $relevantCategories);
                $types .= str_repeat('s', count($relevantCategories));
            }
        }

        // Build tourist spots query
        $whereClause = "";
        if (!empty($conditions)) {
            $whereClause = "WHERE t.status = 'active' AND (" . implode(' OR ', $conditions) . ")";
        } else {
            $whereClause = "WHERE t.status = 'active'";
        }

        $touristQuery = "SELECT 
            tourist_spot_id as id,
            'tourist' as type,
            name,
            category,
            barangay,
            location,
            destination_thumbnail
        FROM Tourist_Spots t
        $whereClause";

        $restaurantQuery = "SELECT 
            business_id as id,
            'business' as type,
            name,
            category,
            barangay,
            location,
            destination_thumbnail
        FROM Businesses
        WHERE status = 'active' AND category = 'Restaurant'";

        // Combine queries based on preferences
        if ($includeRestaurants) {
            $sql = "($touristQuery) UNION ALL ($restaurantQuery)";
        } else {
            $sql = $touristQuery;
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error in getRecommendedDestinations: " . $e->getMessage());
        return [];
    }
}

// Get all recommended destinations (no limit)
$recommendedDestinations = getRecommendedDestinations($_SESSION['user_id'], $conn);
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended Destinations - MViTour</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Include your existing styles -->
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

        /* Explore Sections Styling */
        .explore-section {
            padding: 80px 20px;
            background-color: white;
            position: relative;
            z-index: 1;
        }

        .explore-section:nth-child(even) {
            background-color: #f8f9fa;
        }

        .explore-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .explore-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .explore-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .explore-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .explore-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .explore-content {
            padding: 20px;
        }

        .explore-category {
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

        .explore-category i {
            font-size: 0.9rem;
        }

        .explore-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .explore-location {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }

        .explore-link {
            text-decoration: none;
            color: inherit;
        }

        .explore-link:hover {
            color: inherit;
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

        .page-header {
            text-align: center;
            padding: 100px 20px 40px;
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
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

        .recommendations-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .filters {
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid #007bff;
            border-radius: 20px;
            background: white;
            color: #333;
            font-family: 'Montserrat', sans-serif;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .recommendations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .no-recommendations {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 25px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .recommendations-info {
            margin: 20px 0;
            padding: 10px;
            background: #e9ecef;
            border-radius: 8px;
            text-align: center;
            color: #666;
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
                    <button class="profile-btn">
                        <i class="fas fa-user"></i>
                    </button>
                    <div class="dropdown-content">
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
        <h1>Your Recommended Destinations</h1>
        <p>Discover places that match your interests and preferences</p>
    </div>

    <div class="recommendations-container">
        <a href="homepage.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <?php if (!empty($recommendedDestinations)): ?>
            <div class="recommendations-info">
                <p>Found <?php echo count($recommendedDestinations); ?> destinations matching your preferences</p>
            </div>
            <div class="recommendations-grid">
                <?php foreach($recommendedDestinations as $destination): ?>
                    <a href="destination_details.php?id=<?php echo htmlspecialchars($destination['id']); ?>&type=<?php echo htmlspecialchars($destination['type']); ?>" class="explore-link">
                        <div class="explore-card">
                            <img src="../uploads/<?php echo htmlspecialchars($destination['destination_thumbnail']); ?>" 
                                alt="<?php echo htmlspecialchars($destination['name']); ?>" 
                                class="explore-image">
                            <div class="explore-content">
                                <span class="explore-category">
                                    <i class="fas <?php echo getCategoryIcon($destination['category'], $destination['type']); ?>"></i>
                                    <?php echo htmlspecialchars($destination['category']); ?>
                                </span>
                                <h3 class="explore-title"><?php echo htmlspecialchars($destination['name']); ?></h3>
                                <div class="explore-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($destination['barangay'] . ', ' . $destination['location']); ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-recommendations">
                <i class="fas fa-info-circle" style="font-size: 2rem; color: #007bff; margin-bottom: 15px;"></i>
                <h3>No Recommendations Found</h3>
                <p>Try updating your preferences to get personalized recommendations.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
         document.addEventListener('DOMContentLoaded', function() {
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