<?php
    session_start();
    include '../db.php';

    $isLoggedIn = isset($_SESSION['user_id']);

    // Fetch tourist spots from the database
    $sql_tourist_spots = "SELECT name, destination_thumbnail, category, barangay, location, tourist_spot_id FROM Tourist_Spots WHERE status = 'active'";
    $result_tourist_spots = $conn->query($sql_tourist_spots);

    // Fetch businesses from the database
    $sql_businesses = "SELECT name, destination_thumbnail, category, barangay, location, business_id FROM Businesses WHERE status = 'active'";
    $result_businesses = $conn->query($sql_businesses);

    // Fetch featured attractions
    $sql_attractions = "SELECT * FROM featured_attractions WHERE status = 'active' ORDER BY created_at DESC";
    $result_attractions = $conn->query($sql_attractions);
    $attractions = [];
    if ($result_attractions->num_rows > 0) {
        while ($row = $result_attractions->fetch_assoc()) {
            $attractions[] = $row;
        }
    }

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

    function getFeaturedAttractionIcon($category) {
        switch ($category) {
            case 'Heritage Sites':
                return 'fa-landmark';
            case 'Natural Wonders':
                return 'fa-mountain';
            case 'Cultural Spots':
                return 'fa-masks-theater';
            case 'Local Delicacies/Food Spots':
                return 'fa-utensils';
            case 'Traditional Crafts':
                return 'fa-hands-holding';
            case 'Festivals & Events':
                return 'fa-calendar-day';
            default:
                return 'fa-star';
        }
    }

    function getRecommendedDestinations($user_id, $conn) {
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
        
        error_log("User preferences: " . print_r($preferences, true));
        
        // If no preferences at all, return empty array
        if (empty($preferences['category']) && empty($preferences['activity'])) {
            error_log("No preferences found");
            return [];
        }
        
        try {
            // Build query for tourist spots based on both categories and activities
            $conditions = [];
            $params = [];
            $types = '';
    
            // Add category conditions if there are category preferences
            if (!empty($preferences['category'])) {
                $categoryPlaceholders = str_repeat('?,', count($preferences['category']) - 1) . '?';
                $conditions[] = "t.category IN ($categoryPlaceholders)";
                $params = array_merge($params, $preferences['category']);
                $types .= str_repeat('s', count($preferences['category']));
            }
    
            // Add activity-based conditions if there are activity preferences
            if (!empty($preferences['activity'])) {
                // Map activities to relevant categories
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
    
            // Combine conditions with OR
            $whereClause = "WHERE t.status = 'active' AND (" . implode(' OR ', $conditions) . ")";
            
            $sql = "SELECT DISTINCT 
                        t.tourist_spot_id as id,
                        'tourist' as type,
                        t.name,
                        t.category,
                        t.barangay,
                        t.location,
                        t.destination_thumbnail
                    FROM Tourist_Spots t
                    $whereClause
                    LIMIT 6";  // Increased limit to show more recommendations
            
            error_log("SQL Query: " . $sql);
            error_log("Parameters: " . implode(", ", $params));
                    
            $stmt = $conn->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            error_log("Found " . count($results) . " recommendations");
            
            return $results;
        } catch (Exception $e) {
            error_log("Error in getRecommendedDestinations: " . $e->getMessage());
            return [];
        }
    }

    $historyContent = [
        [
            "title" => "The Early Days",
            "period" => "Pre-Colonial Era",
            "content" => "Before the Spanish colonization, the region now known as Ilocos Sur was home to indigenous peoples who established a thriving civilization. The area was known for its gold mines and trading relations with Chinese merchants, who frequented its coastal communities. The region's rich soil and strategic location along the South China Sea made it an important trading hub, with local inhabitants engaging in commerce with various Asian traders long before European contact.",
            "image" => "../assets/Pre-Colonial_Period.jpg"
        ],
        [
            "title" => "Spanish Colonial Period",
            "period" => "1572-1898",
            "content" => "The Spanish arrived in the Ilocos region in 1572, led by Juan de Salcedo. Vigan was established as a trading post and eventually became one of the most important colonial cities in the Philippines. The city's architecture reflects the fusion of Filipino, Chinese, and Spanish influences, particularly evident in its well-preserved heritage buildings and cobblestone streets. During this period, Vigan emerged as a major cultural and economic center, with the establishment of significant religious and administrative buildings.",
            "image" => "../assets/Spanish_Colonial_Period.jpg"
        ],
        [
            "title" => "Revolutionary Period",
            "period" => "1898-1901",
            "content" => "During the Philippine Revolution, Ilocos Sur played a significant role in the fight for independence. The province was a center of revolutionary activities, with many of its sons and daughters joining the cause for Philippine independence. This period marked significant social and political changes in the region, as local leaders and citizens actively participated in the nationwide movement for freedom from Spanish colonial rule.",
            "image" => "../assets/Revolutionary_Period.jpg"
        ],
        [
            "title" => "Modern Era",
            "period" => "Present Day",
            "content" => "Today, Ilocos Sur stands as a testament to its rich history, with Vigan being recognized as a UNESCO World Heritage Site. The province continues to preserve its cultural heritage while embracing modern development, making it a unique destination that bridges the past and present. The careful preservation of historical sites, combined with sustainable development initiatives, has made Ilocos Sur a model for cultural conservation in the Philippines.",
            "image" => "../assets/Modern_Era.jpg"
        ]
    ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MViTour - Your Travel Companion</title>
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
            gap: 20px;
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

        /* Hero Section */
        .hero {
            height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .slide.active {
            opacity: 1;
        }

        .slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
        }

        .municipality-name {
            position: absolute;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 2.5rem;
            font-family: 'Playfair Display', serif;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            opacity: 0;
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }

        .slide.active .municipality-name {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            padding: 0 20px;
        }

        .hero h1 {
            font-size: 3rem;
            font-family: 'Playfair Display', serif;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* Slideshow Navigation */
        .slide-nav {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 3;
        }

        .slide-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slide-dot.active {
            background: white;
            transform: scale(1.2);
        }

        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid #007bff;
        }

        .cta-button:hover {
            background-color: transparent;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Features Section */
        .features {
            padding: 80px 20px;
            background-color: white;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .feature-card i {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }

        .feature-card p {
            color: #666;
            font-size: 1rem;
        }

        /* Section Titles */
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: #333;
            font-family: 'Playfair Display', serif;
            margin-bottom: 15px;
        }

        .section-title p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
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
            background: linear-gradient(45deg, #007bff, #00bfff);
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

        .view-all-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin-top: 40px;
            transition: all 0.3s ease;
            border: 2px solid #007bff;
        }

        .view-all-btn:hover {
            background-color: white;
            color: #007bff;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: #333;
            font-family: 'Playfair Display', serif;
            margin-bottom: 15px;
        }

        .section-header p {
            color: #666;
            font-size: 1.1rem; 
            max-width: 600px;
            margin: 0 auto;
        }

        /* Search Section Styling */
        .header-search {
            flex: 1;
            max-width: 600px;
            position: relative;
        }

        .search-box {
            position: relative;
            width: 100%;
            background: white;
            border-radius: 30px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #007bff;
            font-size: 1.1rem;
        }

        #searchInput {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 2px solid transparent;
            border-radius: 25px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: transparent;
            font-family: 'Montserrat', Arial, sans-serif;
        }

        #searchInput:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
        }

        .search-results {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }

        .search-result-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
            transition: all 0.3s ease;
            text-decoration: none !important;
        }

        .search-result-item:hover {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            transform: translateX(5px);
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background-color: #f8f9fa;
        }

        .search-result-item:hover .result-name,
        .search-result-item:hover .result-location {
            text-decoration: none;
        }

        .result-img {
            width: 90px;
            height: 90px;
            border-radius: 15px;
            object-fit: cover;
            margin-right: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .result-info {
            flex: 1;
        }

        .result-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 8px;
            text-decoration: none;
        }

        .result-category {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            color: #007bff;
            margin-bottom: 8px;
            padding: 4px 12px;
            background: rgba(0, 123, 255, 0.1);
            border-radius: 15px;
        }

        .result-category i {
            font-size: 1rem;
        }

        .result-location {
            font-size: 0.9rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        /* Custom Scrollbar */
        .search-results::-webkit-scrollbar {
            width: 8px;
            height: 0px;
        }

        .search-results::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .search-results::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #007bff, #00bfff);
            border-radius: 4px;
        }

        .search-results::-webkit-scrollbar-horizontal {
            display: none;      /* Hide horizontal scrollbar */
        }

        /* No Results Message */
        .no-results {
            padding: 30px;
            text-align: center;
            color: #666;
            font-style: italic;
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

        .login-prompt {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1001;
            text-align: center;
            display: none;
        }

        .login-prompt.active {
            display: block;
        }

        .login-prompt p {
            color: #666;
            margin-bottom: 15px;
            font-size: 1.1rem;
            text-shadow: none;
            font-weight: 400;
        }

        .login-prompt-btn {
            padding: 8px 20px;
            background: #007bff;
            color: white;
            border: 2px solid #007bff;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .login-prompt-btn:hover {
            background-color: white;
            color: #007bff;
        }

        .history-section {
            background-color: white;
            padding: 80px 0 5px;
        }

        .history-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .history-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .history-header h2 {
            font-size: 2.5rem;
            color: #333;
            font-family: 'Playfair Display', serif;
            margin-bottom: 15px;
        }

        .history-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .timeline-nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 40px;
        }

        .timeline-btn {
            padding: 12px 24px;
            border-radius: 25px;
            border: none;
            background-color: #f8f9fa;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #007bff;
            font-family: 'Montserrat', Arial, sans-serif;  /* Added font-family */
            font-size: 1rem;  /* Added font size */
            font-weight: 500;  /* Added font weight */
        }

        .timeline-btn.active {
            background: linear-gradient(45deg, #007bff, #00bfff);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,123,255,0.2);
            border: 2px solid white;
        }

        .timeline-btn.active:hover {
            color: white;
        }

        .timeline-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            color: #007bff;
            background-color: white;
        }

        .history-content {
            position: relative;
            min-height: 600px;
        }

        .history-item {
            position: absolute;
            width: 100%;
            opacity: 0;
            visibility: hidden;
            transition: all 0.5s ease;
        }

        .history-item.active {
            opacity: 1;
            visibility: visible;
        }

        .history-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        @media (min-width: 992px) {
            .history-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .history-image {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            aspect-ratio: 4/3;
        }

        .history-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);
        }

        .history-text {
            padding: 20px;
        }

        .history-text h3 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 15px;
        }

        .period-badge {
            display: inline-block;
            padding: 8px 16px;
            background-color: #e3f2fd;
            color: #007bff;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .history-text p {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.8;
            text-align: justify;  /* Added this property */
            text-indent: 50px;
        }

        #destination-map {
            width: 100%;
            height: 500px;
            border-radius: 15px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .map-controls {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .location-btn {
            padding: 12px 24px;
            border-radius: 25px;
            border: 2px solid #007bff;
            background-color: white;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
        }

        .location-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            color: #007bff;
            background-color: white;
        }

        .location-btn.active {
            background: linear-gradient(45deg, #007bff, #00bfff);
            color: white;
            border: 2px solid white;
        }

        .map-legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-item img {
            width: 24px;
            height: 24px;
        }

        .attractions-section {
            padding: 80px 0;
            background-color: #f8f9fa;
            overflow: hidden;
        }

        .attractions-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .attractions-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .attractions-header h2 {
            font-size: 2.5rem;
            color: #333;
            font-family: 'Playfair Display', serif;
            margin-bottom: 15px;
        }

        .attractions-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .attractions-slider {
            position: relative;
            padding: 0 60px;
        }

        .attractions-track {
            display: flex;
            transition: transform 0.5s ease;
            gap: 30px;
        }

        .attraction-card-link {
            text-decoration: none;
            color: inherit;
            min-width: calc(33.333% - 20px);
            transition: all 0.3s ease;
        }

        .attraction-card-link:hover {
            text-decoration: none;
            color: inherit;
        }

        .attraction-card-link:hover .attraction-card {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .attraction-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .attraction-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .attraction-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .attraction-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        }

        .attraction-content {
            padding: 25px;
        }

        .attraction-content h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 15px;
            font-family: 'Montserrat', Arial, sans-serif;
        }

        .attraction-category {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            color: white;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #007bff, #00bfff);
            box-shadow: 0 4px 15px rgba(0,123,255,0.2);
        }

        .attraction-location {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            margin-bottom: 15px;
        }

        .slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #007bff;
        }

        .slider-nav:hover {
            background: #007bff;
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .slider-nav.prev {
            left: 0;
        }

        .slider-nav.next {
            right: 0;
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

        <div class="header-search">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search for destinations, categories, or locations...">
            </div>
            <div id="searchResults" class="search-results"></div>
        </div>

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

    <!-- Hero Section -->
    <section class="hero">
        <!-- Slides -->
        <div class="slide active" style="background-image: url('../assets/IS_capitol.jpg')">
            <div class="municipality-name">Ilocos Sur Provincial Capitol</div>
        </div>
        <div class="slide" style="background-image: url('../assets/vigan.jpg')">
            <div class="municipality-name">Vigan City</div>
        </div>
        <div class="slide" style="background-image: url('../assets/bantay.jpg')">
            <div class="municipality-name">Bantay</div>
        </div>
        <div class="slide" style="background-image: url('../assets/santa_catalina.jpg')">
            <div class="municipality-name">Santa Catalina</div>
        </div>
        <div class="slide" style="background-image: url('../assets/san_vicente.jpg')">
            <div class="municipality-name">San Vicente</div>
        </div>
        <div class="slide" style="background-image: url('../assets/caoayan.jpg')">
            <div class="municipality-name">Caoayan</div>
        </div>

        <!-- Hero Content -->
        <div class="hero-content">
            <h1>Your Next Great Adventure Begins In Metro Vigan, Ilocos Sur!</h1>
            <p>Create personalized itineraries, explore local attractions, and make the most of your travel experience.</p>
            <?php if ($isLoggedIn): ?>
                <a href="get_itinerary.php" class="cta-button">Plan Your Trip Now</a>
            <?php else: ?>
                <a href="javascript:void(0)" onclick="showLoginPrompt()" class="cta-button">Plan Your Trip Now</a>
            <?php endif; ?>
        </div>

        <!-- Slideshow Navigation -->
        <div class="slide-nav">
            <div class="slide-dot active"></div>
            <div class="slide-dot"></div>
            <div class="slide-dot"></div>
            <div class="slide-dot"></div>
            <div class="slide-dot"></div>
            <div class="slide-dot"></div>
        </div>

        <!-- Login Prompt -->
        <div class="login-prompt" id="loginPrompt">
            <p>Please log in to create an itinerary</p>
            <a href="login_useracc.php" class="login-prompt-btn">Log In</a>
        </div>
    </section>
    
    <!-- Ilocos Sur History Section -->
    <section class="history-section">
        <div class="history-container">
            <div class="history-header">
                <h2>Journey Through Time</h2>
                <p>Discover the rich history of Ilocos Sur, from its pre-colonial roots to its present-day glory</p>
            </div>

            <!-- Timeline Navigation -->
            <div class="timeline-nav">
                <?php foreach($historyContent as $index => $item): ?>
                    <button class="timeline-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                            data-index="<?php echo $index; ?>">
                        <?php echo htmlspecialchars($item['period']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Content Display -->
            <div class="history-content">
                <?php foreach($historyContent as $index => $item): ?>
                    <div class="history-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                        data-index="<?php echo $index; ?>">
                        <div class="history-grid">
                            <div class="history-image">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                    alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <div class="image-overlay"></div>
                            </div>
                            <div class="history-text">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="period-badge">
                                    <?php echo htmlspecialchars($item['period']); ?>
                                </div>
                                <p><?php echo htmlspecialchars($item['content']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- What to See Section -->
    <section class="attractions-section">
        <div class="attractions-container">
            <div class="attractions-header">
                <h2>What to See in Ilocos Sur</h2>
                <p>Explore the must-visit attractions that make Ilocos Sur truly special</p>
            </div>

            <div class="attractions-slider">
                <div class="attractions-track">
                    <?php foreach($attractions as $attraction): ?>
                        <a href="featured_attraction_details.php?id=<?php echo htmlspecialchars($attraction['attraction_id']); ?>" 
                        class="attraction-card-link">
                            <div class="attraction-card">
                                <div class="attraction-image">
                                    <img src="../uploads/<?php echo htmlspecialchars($attraction['destination_thumbnail']); ?>" 
                                        alt="<?php echo htmlspecialchars($attraction['name']); ?>">
                                    <div class="attraction-overlay"></div>
                                </div>
                                <div class="attraction-content">
                                    <div class="attraction-category">
                                        <i class="fas <?php echo getFeaturedAttractionIcon($attraction['category']); ?>"></i>
                                        <?php echo htmlspecialchars($attraction['category']); ?>
                                    </div>
                                    <h3><?php echo htmlspecialchars($attraction['name']); ?></h3>
                                    <div class="attraction-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($attraction['location']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <button class="slider-nav prev" aria-label="Previous attraction">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="slider-nav next" aria-label="Next attraction">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

    <?php if ($isLoggedIn):
        $recommendedDestinations = getRecommendedDestinations($_SESSION['user_id'], $conn);
        if (!empty($recommendedDestinations)):
    ?>
        <!-- Recommended Destinations -->
        <section class="explore-section">
            <div class="explore-container">
                <div class="section-header">
                    <h2>Recommended For You</h2>
                    <p>Destinations tailored to your preferences</p>
                </div>
                <div class="explore-grid">
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
                <div class="text-center" style="text-align: center; margin-top: 40px;">
                    <a href="recommended_destinations.php" class="view-all-btn">View All Recommendations</a>
                </div>
            </div>
        </section>
    <?php 
        endif;
    endif;
    ?>

    <!-- Tourist Spots Section -->
    <section class="explore-section">
        <div class="explore-container">
            <div class="section-header">
                <h2>Discover Tourist Spots</h2>
                <p>Explore the most captivating destinations across Metro Vigan</p>
            </div>
            <div class="explore-grid">
                <?php
                if ($result_tourist_spots->num_rows > 0) {
                    $count = 0;
                    while ($row = $result_tourist_spots->fetch_assoc()) {
                        if ($count >= 6) break; // Show only 6 spots
                        ?>
                        <a href="destination_details.php?id=<?php echo htmlspecialchars($row['tourist_spot_id']); ?>&type=tourist" class="explore-link">
                            <div class="explore-card">
                                <img src="../uploads/<?php echo htmlspecialchars($row['destination_thumbnail']); ?>" 
                                    alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                    class="explore-image">
                                <div class="explore-content">
                                    <span class="explore-category">
                                        <i class="fas <?php echo getCategoryIcon($row['category'], 'tourist'); ?>"></i>
                                        <?php echo htmlspecialchars($row['category']); ?>
                                    </span>
                                    <h3 class="explore-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                                    <div class="explore-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($row['barangay'] . ', ' . $row['location']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php
                        $count++;
                    }
                }
                ?>
            </div>
            <div class="text-center" style="text-align: center; margin-top: 40px;">
                <a href="destination_listing.php" class="view-all-btn">View All Tourist Spots</a>
            </div>
        </div>
    </section>

    <!-- Businesses Section -->
    <section class="explore-section">
        <div class="explore-container">
            <div class="section-header">
                <h2>Local Businesses</h2>
                <p>Discover the best local establishments Metro Vigan has to offer</p>
            </div>
            <div class="explore-grid">
                <?php
                if ($result_businesses->num_rows > 0) {
                    $count = 0;
                    while ($row = $result_businesses->fetch_assoc()) {
                        if ($count >= 6) break; // Show only 6 businesses
                        ?>
                        <a href="destination_details.php?id=<?php echo htmlspecialchars($row['business_id']); ?>&type=business" class="explore-link">
                            <div class="explore-card">
                                <img src="../uploads/<?php echo htmlspecialchars($row['destination_thumbnail']); ?>" 
                                    alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                    class="explore-image">
                                <div class="explore-content">
                                    <span class="explore-category">
                                        <i class="fas <?php echo getCategoryIcon($row['category'], 'business'); ?>"></i>
                                        <?php echo htmlspecialchars($row['category']); ?>
                                    </span>
                                    <h3 class="explore-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                                    <div class="explore-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($row['barangay'] . ', ' . $row['location']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php
                        $count++;
                    }
                }
                ?>
            </div>
            <div class="text-center" style="text-align: center; margin-top: 40px;">
                <a href="destination_listing.php" class="view-all-btn">View All Businesses</a>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="explore-section">
        <div class="explore-container">
            <div class="section-header">
                <h2>Explore Our Destinations</h2>
                <p>Discover tourist spots and local businesses across Metro Vigan</p>
            </div>
            
            <div class="map-controls">
                <button class="location-btn active" data-location="all">All Locations</button>
                <button class="location-btn" data-location="Vigan">Vigan</button>
                <button class="location-btn" data-location="Bantay">Bantay</button>
                <button class="location-btn" data-location="Santa Catalina">Santa Catalina</button>
                <button class="location-btn" data-location="San Vicente">San Vicente</button>
                <button class="location-btn" data-location="Caoayan">Caoayan</button>
            </div>
            
            <div id="destination-map"></div>
            
            <div class="map-legend">
                <div class="legend-item">
                    <img src="https://maps.google.com/mapfiles/ms/icons/blue-dot.png" alt="Tourist Spot">
                    <span>Tourist Spots</span>
                </div>
                <div class="legend-item">
                    <img src="https://maps.google.com/mapfiles/ms/icons/red-dot.png" alt="Restaurant">
                    <span>Restaurants</span>
                </div>
                <div class="legend-item">
                    <img src="https://maps.google.com/mapfiles/ms/icons/pink-dot.png" alt="Accommodation">
                    <span>Accommodations</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="section-title">
            <h2>Why Choose MViTour</h2>
            <p>Experience seamless travel planning with our innovative features</p>
        </div>
        <div class="features-container">
            <div class="feature-card">
                <i class="fas fa-route"></i>
                <h3>Seamless Organization</h3>
                <p>All your travel details in one place - from accommodations to activities, complete with directions.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-lightbulb"></i>
                <h3>Smart Recommendations</h3>
                <p>Receive personalized suggestions based on your interests and preferences for a tailored travel experience.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-route"></i>
                <h3>Personalized Itineraries</h3>
                <p>Create custom travel plans tailored to your preferences, budget, and schedule.</p>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Slideshow functionality
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slide-dot');
        let currentSlide = 0;
        const slideInterval = 5000;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        setInterval(nextSlide, slideInterval);

        // Profile Dropdown functionality
        const profileBtn = document.querySelector('.profile-btn');
        const dropdownContent = document.querySelector('.dropdown-content');

        if (profileBtn && dropdownContent) { // Check if elements exist
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

            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            let timeoutId;

            searchInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }

                timeoutId = setTimeout(() => {
                    fetch(`search_destinations.php?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                searchResults.innerHTML = data.map(item => `
                                    <a href="destination_details.php?id=${item.id}&type=${item.type}" class="search-result-item">
                                        <img src="../uploads/${item.destination_thumbnail}" alt="${item.name}" class="result-img">
                                        <div class="result-info">
                                            <div class="result-name">${item.name}</div>
                                            <div class="result-category">
                                                <i class="fas ${item.type === 'tourist' ? 
                                                    getCategoryIconTourist(item.category) : 
                                                    getCategoryIconBusiness(item.category)}"></i>
                                                ${item.category}
                                            </div>
                                            <div class="result-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                ${item.barangay}, ${item.location}
                                            </div>
                                        </div>
                                    </a>
                                `).join('');
                                searchResults.style.display = 'block';
                            } else {
                                searchResults.innerHTML = '<div class="search-result-item">No results found</div>';
                                searchResults.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            searchResults.innerHTML = '<div class="search-result-item">Error fetching results</div>';
                            searchResults.style.display = 'block';
                        });
                }, 300);
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                const searchBox = document.querySelector('.header-search');
                const searchResults = document.getElementById('searchResults');
                
                if (!searchBox.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });

            // Helper functions for category icons
            function getCategoryIconTourist(category) {
                switch (category) {
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

            function getCategoryIconBusiness(category) {
                switch (category) {
                    case 'Hotel': return 'fa-hotel';
                    case 'Restaurant': return 'fa-utensils';
                    case 'Resort': return 'fa-umbrella-beach';
                    case 'Inn': return 'fa-bed';
                    case 'Transient House': return 'fa-house';
                    case 'Apartelle': return 'fa-building';
                    default: return 'fa-store';
                }
            }
        });

        function showLoginPrompt() {
            document.getElementById('loginPrompt').classList.add('active');
            setTimeout(() => {
                document.getElementById('loginPrompt').classList.remove('active');
            }, 3000);
        }

        // Close modal when clicking outside
        document.getElementById('loginPrompt').addEventListener('click', (e) => {
            if (e.target === document.getElementById('loginPrompt')) {
                document.getElementById('loginPrompt').classList.remove('active');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const timelineBtns = document.querySelectorAll('.timeline-btn');
            const historyItems = document.querySelectorAll('.history-item');

            function setActiveItem(index) {
                // Remove active class from all buttons and items
                timelineBtns.forEach(btn => btn.classList.remove('active'));
                historyItems.forEach(item => item.classList.remove('active'));

                // Add active class to selected button and item
                timelineBtns[index].classList.add('active');
                historyItems[index].classList.add('active');
            }

            timelineBtns.forEach((btn, index) => {
                btn.addEventListener('click', () => setActiveItem(index));
            });
        });

        // Map initialization
        let map;
        let markers = [];
        const locationCenters = {
            'all': { lat: 17.5747, lng: 120.3869 },
            'Vigan': { lat: 17.5747, lng: 120.3869 },
            'Bantay': { lat: 17.5981, lng: 120.4466 },
            'Santa Catalina': { lat: 17.5867, lng: 120.3590 },
            'San Vicente': { lat: 17.6185, lng: 120.3611 },
            'Caoayan': { lat: 17.5380, lng: 120.3956 }
        };

        function initMap() {
            map = new google.maps.Map(document.getElementById('destination-map'), {
                zoom: 13,
                center: locationCenters['all'],
                styles: [{
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }]
            });

            // Load destinations
            fetch('get_destinations.php')
                .then(response => response.json())
                .then(destinations => {
                    addMarkers(destinations);
                });
        }

        function addMarkers(destinations) {
            // Clear existing markers
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            destinations.forEach(dest => {
                if (dest.latitude && dest.longitude) {
                    // Determine marker color based on type and category
                    let iconUrl;
                    if (dest.type === 'tourist') {
                        iconUrl = 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
                    } else if (dest.type === 'business') {
                        if (dest.category === 'Restaurant') {
                            iconUrl = 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';
                        } else {
                            // For hotels, resorts, etc.
                            iconUrl = 'https://maps.google.com/mapfiles/ms/icons/pink-dot.png';
                        }
                    }

                    const marker = new google.maps.Marker({
                        position: { 
                            lat: parseFloat(dest.latitude), 
                            lng: parseFloat(dest.longitude) 
                        },
                        map: map,
                        title: dest.name,
                        icon: {
                            url: iconUrl
                        }
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div style="padding: 15px; max-width: 300px;">
                                <img src="../uploads/${dest.destination_thumbnail}" 
                                    alt="${dest.name}" 
                                    style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">
                                <h3 style="font-weight: bold; font-size: 16px; margin-bottom: 8px; color: #333;">
                                    ${dest.name}
                                </h3>
                                <p style="color: #666; font-size: 14px; margin-bottom: 8px;">
                                    ${dest.category}
                                </p>
                                <p style="font-size: 14px; margin-bottom: 12px; color: #555;">
                                    <i class="fas fa-map-marker-alt" style="color: #007bff;"></i> 
                                    ${dest.barangay}, ${dest.location}
                                </p>
                                <a href="destination_details.php?id=${dest.id}&type=${dest.type}" 
                                style="display: inline-block; padding: 8px 16px; background-color: #007bff; 
                                        color: white; text-decoration: none; border-radius: 20px; 
                                        text-align: center; font-weight: 500; transition: all 0.3s ease;">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        `
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });

                    markers.push(marker);
                }
            });
        }

        // Location filter functionality
        document.querySelectorAll('.location-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const location = btn.dataset.location;
                
                // Update active button
                document.querySelectorAll('.location-btn').forEach(b => 
                    b.classList.remove('active'));
                btn.classList.add('active');

                // Filter destinations
                fetch('get_destinations.php')
                    .then(response => response.json())
                    .then(destinations => {
                        const filtered = location === 'all' 
                            ? destinations 
                            : destinations.filter(d => d.location === location);
                        
                        addMarkers(filtered);
                        map.setCenter(locationCenters[location]);
                        map.setZoom(location === 'all' ? 13 : 14);
                    });
            });
        });

        // Add this to your existing JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const track = document.querySelector('.attractions-track');
            const cards = document.querySelectorAll('.attraction-card');
            const prevButton = document.querySelector('.slider-nav.prev');
            const nextButton = document.querySelector('.slider-nav.next');
            
            let currentIndex = 0;
            const cardWidth = cards[0].offsetWidth + 30; // Including gap
            const maxIndex = cards.length - 3; // Show 3 cards at a time

            function updateSlidePosition() {
                track.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
                
                // Update button states
                prevButton.style.opacity = currentIndex === 0 ? '0.5' : '1';
                nextButton.style.opacity = currentIndex === maxIndex ? '0.5' : '1';
            }

            prevButton.addEventListener('click', () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateSlidePosition();
                }
            });

            nextButton.addEventListener('click', () => {
                if (currentIndex < maxIndex) {
                    currentIndex++;
                    updateSlidePosition();
                }
            });

            // Initial button states
            updateSlidePosition();

            // Handle window resize
            window.addEventListener('resize', () => {
                // Recalculate card width
                cardWidth = cards[0].offsetWidth + 30;
                updateSlidePosition();
            });
        });
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAKPXW_Gzu-tewUUeJC-Iaxv2G0bFhkG0s&callback=initMap" async defer></script>
</body>
</html>