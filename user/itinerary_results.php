<?php
session_start();
include '../db.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Check if we're coming from a form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['itinerary_data'] = $_POST;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// If we have itinerary data in session, use it
if (isset($_SESSION['itinerary_data'])) {
    $_POST = $_SESSION['itinerary_data'];
}

// Helper class to represent a location with coordinates
class Location {
    public $id;
    public $type;
    public $latitude;
    public $longitude;
    public $name;
    public $category;
    public $thumbnail;
    public $description;
    
    public function __construct($id, $type, $lat, $lng, $name, $category, $thumbnail = null, $description = null) {
        $this->id = $id;
        $this->type = $type;
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->name = $name;
        $this->category = $category;
        $this->thumbnail = $thumbnail;
        $this->description = $description;
    }
}

// Function to calculate distance between two points using Haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
         sin($dLon/2) * sin($dLon/2);
         
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

// Function to find nearest restaurant to a given location
function findNearestRestaurant($location, $restaurants, $usedRestaurants, $maxDistance = 2) {

    // If we've used all restaurants, reset the used list
    if (count($usedRestaurants) >= count($restaurants)) {
        $usedRestaurants = []; // Reset used restaurants when all have been used
    }
    
    // Check if location is null or missing coordinates
    if (!$location || !isset($location->latitude) || !isset($location->longitude)) {
        return null;
    }

    $nearestRestaurant = null;
    $minDistance = $maxDistance;
    
    foreach ($restaurants as $restaurant) {
        // Skip if restaurant already used or missing coordinates
        if (in_array($restaurant['business_id'], $usedRestaurants) || 
            !isset($restaurant['latitude']) || 
            !isset($restaurant['longitude'])) {
            continue;
        }
        
        $distance = calculateDistance(
            $location->latitude,
            $location->longitude,
            $restaurant['latitude'],
            $restaurant['longitude']
        );
        
        if ($distance < $minDistance) {
            $minDistance = $distance;
            $nearestRestaurant = $restaurant;
        }
    }
    
    return $nearestRestaurant;
}

// Function to optimize route using nearest neighbor algorithm
// Function to optimize route using nearest neighbor algorithm
function optimizeRoute($startingPoint, $spots) {
    // Check if starting point is null or missing coordinates
    if (!$startingPoint || !isset($startingPoint->latitude) || !isset($startingPoint->longitude)) {
        return $spots; // Return unoptimized spots if no valid starting point
    }

    $optimizedRoute = [];
    $remainingSpots = array_filter($spots, function($spot) {
        return isset($spot['latitude']) && isset($spot['longitude']);
    });

    if (empty($remainingSpots)) {
        return $spots; // Return original spots if no valid coordinates
    }

    $currentLocation = $startingPoint;
    
    while (!empty($remainingSpots)) {
        $nearestSpot = null;
        $minDistance = PHP_FLOAT_MAX;
        $nearestIndex = -1;
        
        foreach ($remainingSpots as $index => $spot) {
            // Skip spots with missing coordinates (although we filtered them above)
            if (!isset($spot['latitude']) || !isset($spot['longitude'])) {
                continue;
            }

            $distance = calculateDistance(
                $currentLocation->latitude,
                $currentLocation->longitude,
                $spot['latitude'],
                $spot['longitude']
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestSpot = $spot;
                $nearestIndex = $index;
            }
        }
        
        if ($nearestSpot) {
            $optimizedRoute[] = $nearestSpot;
            unset($remainingSpots[$nearestIndex]);
            $currentLocation = new Location(
                $nearestSpot['tourist_spot_id'] ?? $nearestSpot['business_id'],
                'spot',
                $nearestSpot['latitude'],
                $nearestSpot['longitude'],
                $nearestSpot['name'],
                $nearestSpot['category'],
                $nearestSpot['destination_thumbnail'],
                $nearestSpot['description']
            );
        }
    }
    
    return !empty($optimizedRoute) ? $optimizedRoute : $spots;
}

// Function to build daily itinerary with optimized locations
function buildOptimizedDailyItinerary($spots, $restaurants, $accommodation, $includeDining, &$usedRestaurants) {
    $dailyItinerary = [];
    
    // Start from accommodation if available
    $startingPoint = $accommodation ? new Location(
        $accommodation['business_id'],
        'accommodation',
        $accommodation['latitude'],
        $accommodation['longitude'],
        $accommodation['name'],
        $accommodation['category'],
        $accommodation['destination_thumbnail'],
        $accommodation['description']
    ) : null;
    
    // Optimize tourist spots route
    $optimizedSpots = optimizeRoute($startingPoint, $spots);
    
    // Distribute spots across time slots
    $timeSlots = ['morning', 'afternoon', 'evening'];
    $spotIndex = 0;
    
    foreach ($timeSlots as $timeSlot) {
        if ($spotIndex >= count($optimizedSpots)) break;
        
        $currentSpot = $optimizedSpots[$spotIndex];
        $dailyItinerary[] = [
            'time' => $timeSlot,
            'type' => 'spot',
            'data' => $currentSpot
        ];
        
        // Add nearest restaurant if dining is included
        if ($includeDining) {
            $spotLocation = new Location(
                $currentSpot['tourist_spot_id'],
                'spot',
                $currentSpot['latitude'],
                $currentSpot['longitude'],
                $currentSpot['name'],
                $currentSpot['category']
            );
            
            $nearestRestaurant = findNearestRestaurant($spotLocation, $restaurants, $usedRestaurants);
            
            if ($nearestRestaurant) {
                $dailyItinerary[] = [
                    'time' => $timeSlot . '_dining',
                    'type' => 'restaurant',
                    'data' => $nearestRestaurant
                ];
                $usedRestaurants[] = $nearestRestaurant['business_id'];
            }
        }
        
        $spotIndex++;
    }
    
    return $dailyItinerary;
}

// Main optimization function
function optimizeItinerary($tourist_spots, $restaurants, $accommodation, $duration, $include_dining) {
    $optimizedItinerary = [];
    $usedRestaurants = []; // Initialize empty array
    
    // Split spots across days
    $spotsPerDay = ceil(count($tourist_spots) / $duration);
    $dailySpots = array_chunk($tourist_spots, $spotsPerDay);
    
    for ($day = 0; $day < $duration; $day++) {
        if (!isset($dailySpots[$day])) continue;
        
        // Reset used restaurants at the start of each day if needed
        if ($day > 0 && count($restaurants) < ($duration * 3)) {
            $usedRestaurants = [];
        }
        
        $dayItinerary = buildOptimizedDailyItinerary(
            $dailySpots[$day],
            $restaurants,
            $day === 0 ? $accommodation : null,
            $include_dining,
            $usedRestaurants
        );
        
        // Update used restaurants after each day
        foreach ($dayItinerary as $item) {
            if ($item['type'] === 'restaurant') {
                $usedRestaurants[] = $item['data']['business_id'];
            }
        }
        
        $optimizedItinerary[$day] = $dayItinerary;
    }
    
    return $optimizedItinerary;
}

// Retrieve form data
$people = $_POST['people'];
$destination = $_POST['destination'];
$duration = $_POST['duration'];
$budget = $_POST['budget'];
$activities = explode(",", $_POST['activities']);
$sights = explode(",", $_POST['sights']);
$include_dining = $_POST['include_dining'] === 'yes';
$accommodation_needed = $_POST['need_accommodation'] === 'yes';
$accommodation_type = $_POST['accommodation'] ?? '';
$start_date = new DateTime($_POST['start_date']); 
$end_date = new DateTime($_POST['end_date']);

// Define central points for destinations
$central_points = [
    'Vigan' => ['latitude' => 17.5747, 'longitude' => 120.3869],
    'Bantay' => ['latitude' => 17.5855, 'longitude' => 120.3873],
    'Santa Catalina' => ['latitude' => 17.5425, 'longitude' => 120.3989],
    'San Vicente' => ['latitude' => 17.5747, 'longitude' => 120.3869],
    'Caoayan' => ['latitude' => 17.5453, 'longitude' => 120.3728]
];

// Get initial coordinates for selected destination
$initial_lat = $central_points[$destination]['latitude'] ?? 17.5747;
$initial_lng = $central_points[$destination]['longitude'] ?? 120.3869;

// Initialize variables
$total_cost = 0;
$recommended_accommodation = null;
$tourist_spots = [];
$restaurants = [];
$restaurant_error = '';

// Fetch accommodation if needed
if ($accommodation_needed && !empty($accommodation_type)) {
    $accommodation_query = $conn->prepare("
        SELECT *
        FROM businesses
        WHERE location = ? 
        AND budget <= ? 
        AND category = ?
        AND status = 'active'
        ORDER BY (
            POW(latitude - ?, 2) + 
            POW(longitude - ?, 2)
        ) ASC
        LIMIT 1
    ");

    $accommodation_query->bind_param(
        "sisdd",
        $destination,
        $budget,
        $accommodation_type,
        $initial_lat,
        $initial_lng
    );
    
    $accommodation_query->execute();
    $recommended_accommodation = $accommodation_query->get_result()->fetch_assoc();
}

// Fetch tourist spots
$placeholders = str_repeat('?,', count($sights) - 1) . '?';
$tourist_query = $conn->prepare("
    SELECT *, latitude, longitude 
    FROM tourist_spots
    WHERE location = ? 
    AND entrance_fee <= ? 
    AND category IN ($placeholders)
    AND status = 'active'
    AND is_open = 1
");

$params = array_merge(
    [$destination, $budget],
    $sights
);
$types = 'si' . str_repeat('s', count($sights));
$tourist_query->bind_param($types, ...$params);
$tourist_query->execute();
$all_tourist_spots = $tourist_query->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($all_tourist_spots)) {
    if (!isset($error_messages)) {
        $error_messages = [];
    }
    $error_messages[] = "No open tourist spots found matching your criteria in $destination.";
}

// Fetch restaurants if dining is included
if ($include_dining) {
    $restaurant_query = $conn->prepare("
        SELECT *, latitude, longitude 
        FROM businesses
        WHERE location = ? 
        AND budget <= ? 
        AND category = 'Restaurant'
        AND status = 'active'
        AND is_open = 1
    ");
    $restaurant_query->bind_param("si", $destination, $budget);
    $restaurant_query->execute();
    $restaurants = $restaurant_query->get_result()->fetch_all(MYSQLI_ASSOC);

    // Error handling for restaurant availability
    $required_restaurants = $duration * 3; // 3 meals per day
    if (empty($restaurants)) {
        $restaurant_error = "No restaurants found within your budget in $destination. You may need to plan meals separately.";
    } elseif (count($restaurants) < $required_restaurants) {
        $available_meals = floor(count($restaurants) / 3) * 3;
        $missing_meals = $required_restaurants - count($restaurants);
        $restaurant_error = "Only found " . count($restaurants) . " restaurants, which covers " . 
                          ($available_meals/3) . " days of meals. You'll need to plan " . 
                          $missing_meals . " additional meals.";
    }
}

if ($include_dining && empty($restaurants)) {
    $restaurant_error = "No open restaurants found within your budget in $destination. You may need to plan meals separately.";
}

// Update accommodation to be near first tourist spot if we have spots
if ($accommodation_needed && !empty($all_tourist_spots) && !empty($accommodation_type)) {
    $optimized_spots = optimizeRoute(
        new Location(
            'start',
            'start',
            $initial_lat,
            $initial_lng,
            'Start Point',
            'start'
        ),
        $all_tourist_spots
    );

    if (!empty($optimized_spots)) {
        $first_spot = $optimized_spots[0];
        $accommodation_query = $conn->prepare("
            SELECT *
            FROM businesses
            WHERE location = ? 
            AND budget <= ? 
            AND category = ?
            AND status = 'active'
            AND is_open = 1
            ORDER BY (
                POW(latitude - ?, 2) + 
                POW(longitude - ?, 2)
            ) ASC
            LIMIT 1
        ");

        $accommodation_query->bind_param(
            "sisdd",
            $destination,
            $budget,
            $accommodation_type,
            $first_spot['latitude'],
            $first_spot['longitude']
        );
        
        $accommodation_query->execute();
        $new_accommodation = $accommodation_query->get_result()->fetch_assoc();
        
        if ($new_accommodation) {
            $recommended_accommodation = $new_accommodation;
        }
    }
}

if ($accommodation_needed && !$recommended_accommodation) {
    if (!isset($error_messages)) {
        $error_messages = [];
    }
    $error_messages[] = "No open accommodation found matching your criteria in $destination.";
}

// Generate optimized itinerary
$optimized_itinerary = optimizeItinerary(
    $all_tourist_spots,
    $restaurants,
    $recommended_accommodation,
    $duration,
    $include_dining
);

// Calculate total cost
function calculateTotalCost($duration, $people, $tourist_spots, $restaurants, $accommodation = null) {
    $total_cost = 0;
    
    if ($accommodation) {
        $accommodation_cost = $accommodation['budget'] * $duration;
        $total_cost += $accommodation_cost;
    }
    
    foreach ($tourist_spots as $spot) {
        if (isset($spot['entrance_fee'])) {
            $total_cost += $spot['entrance_fee'] * $people;
        }
    }
    
    if (!empty($restaurants)) {
        $meals_per_day = count(array_filter($restaurants, function($r) {
            return isset($r['budget']) && $r['budget'] > 0;
        }));
        
        $total_restaurant_cost = 0;
        foreach ($restaurants as $restaurant) {
            if (isset($restaurant['budget'])) {
                $total_restaurant_cost += $restaurant['budget'];
            }
        }
        
        if ($meals_per_day > 0) {
            $avg_meal_cost = $total_restaurant_cost / $meals_per_day;
            $total_cost += ($avg_meal_cost * 3 * $duration * $people);
        }
    }
    
    return $total_cost;
}

// Calculate total cost
$total_cost = calculateTotalCost(
    $duration,
    $people,
    $all_tourist_spots,
    $restaurants,
    $recommended_accommodation
);

// Helper function to get category icon
function getCategoryIcon($category) {
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
}

// Helper function to get time period display name
function getTimePeriodDisplay($time) {
    switch ($time) {
        case 'morning_dining':
            return 'Morning Dining';
        case 'afternoon_dining':
            return 'Afternoon Dining';
        case 'evening_dining':
            return 'Evening Dining';
        default:
            return ucfirst($time);
    }
}

// Helper function to get content class based on type
function getContentClass($type) {
    switch ($type) {
        case 'spot':
            return 'spot-content';
        case 'restaurant':
            return 'dining-content';
        case 'accommodation':
            return 'accommodation-content';
        default:
            return '';
    }
}

// Helper function to get dot class based on type
function getDotClass($type) {
    switch ($type) {
        case 'spot':
            return 'tourist-dot';
        case 'restaurant':
            return 'restaurant-dot';
        case 'accommodation':
            return 'accommodation-dot';
        default:
            return '';
    }
}

// Validate itinerary data
$has_valid_spots = !empty($all_tourist_spots) && array_filter($all_tourist_spots, function($spot) {
    return $spot['is_open'] == 1;
});
$has_valid_restaurants = $include_dining ? (!empty($restaurants) && array_filter($restaurants, function($rest) {
    return $rest['is_open'] == 1;
})) : true;
$has_valid_accommodation = $accommodation_needed ? ($recommended_accommodation && $recommended_accommodation['is_open'] == 1) : true;

$itinerary_is_valid = $has_valid_spots && $has_valid_restaurants && $has_valid_accommodation;

if (!$itinerary_is_valid) {
    $error_messages = [];
    if (!$has_valid_spots) {
        $error_messages[] = "No tourist spots found matching your criteria.";
    }
    if ($include_dining && !$has_valid_restaurants) {
        $error_messages[] = "No restaurants found within your budget.";
    }
    if ($accommodation_needed && !$has_valid_accommodation) {
        $error_messages[] = "No accommodation found matching your criteria.";
    }
}

// Process which days have accommodation check-in/out
$accommodation_days = [];
if ($accommodation_needed && $recommended_accommodation) {
    $accommodation_days = [
        'check_in' => 0,                  // First day for check-in
        'check_out' => $duration - 1      // Last day for check-out
    ];    
}

// Check if we should display restaurant warnings
$show_restaurant_warning = !empty($restaurant_error);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itinerary Results - MViTour</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400&display=swap" rel="stylesheet">
    <link rel="icon" href="../assets/mvitour_logo.ico" type="image/x-icon">
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
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1400px; /* Increased from 1000px */
            margin: 120px auto 40px;
            padding: 30px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 15px;
            border: 3px solid #007bff;
        }

        .itinerary-header {
            position: relative;
            height: 500px; /* Adjusted height for better proportion */
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 0;
        }

        .itinerary-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.4));
            z-index: 1;
        }

        .itinerary-header img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .itinerary-header-content {
            position: relative;
            z-index: 2;
            padding: 40px;
            color: white;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .itinerary-header-content h1 {
            font-size: 4em;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            font-family: 'Playfair Display', serif; /* More elegant font */
            letter-spacing: 2px;
        }

        .itinerary-header-content p {
            font-size: 1.8em;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            font-family: 'Montserrat', sans-serif; /* Clean, modern font */
            font-weight: 300;
            letter-spacing: 1px;
        }

        .itinerary-header h1 {
            font-size: 3.5em;
            margin-bottom: 10px;
            color: white;
        }

        .itinerary-details {
            display: flex;
            justify-content: space-around;
            margin: 0;
            padding: 30px;
            background: white;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }


        .detail-item {
            text-align: center;
            padding: 15px 25px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .detail-item i {
            font-size: 1.8em;
            color: #007bff;
            margin-bottom: 10px;
        }

        .timeline {
            position: relative;
            margin: 40px 0 120px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 3px;
            height: calc(100% - -35px);
            background: linear-gradient(to bottom, #007bff, #00bfff);
            border-radius: 3px;
        }

        .timeline-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .timeline-item:nth-child(even) {
            flex-direction: row-reverse;
        }

        .timeline-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 25px;
            position: relative;
            width: 45%;
            margin: 30px 0;
            transition: all 0.3s ease;
            text-decoration: none !important;
        }

        .timeline-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .timeline-content p {
            text-align: justify;
            color: #666666;
            text-indent: 30px; /* Controls the indentation */
            line-height: 1.6; /* For better readability */
            margin-bottom: 15px; /* Space before the View Details button */
        }

        .timeline-time {
            font-weight: bold;
            color: #ff4757;
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .timeline-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .timeline-dot {
            width: 20px;
            height: 20px;
            background:#007bff;
            border-radius: 50%;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .notes-section {
            margin: 50px 30px;
            padding: 30px;
            background: white;
            border: 1px solid #007bff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .notes-section h3 {
            color: #007bff;
            font-family: 'Montserrat', sans-serif;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0,123,255,0.1);
        }

        .notes-section h3 i {
            font-size: 1.2em;
        }

        .notes-section ul {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            list-style: none;
        }

            .notes-section li {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .notes-section li:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .notes-section li i {
            color: #007bff;
            font-size: 1.2em;
        }

        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin: 15px 0;
        }

        .dining-content .category-badge {
            background-color: #ff4757;
        }

        .accommodation-content .category-badge {
            background-color: #e83e8c;
        }

        .category-badge i {
            font-size: 1rem;
            display: inline-block;
        }

        .category-badge span {
            display: inline;
            color: white;
            margin-left: 8px;
        }

        /* Remove icon from h3 since we now show it in the badge */
        .timeline-content h3 i {
            display: none;
        }

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

        .header-nav {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
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

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            color: #0c5460;
        }

        .itinerary-card {
            background: #fff;
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .itinerary-card h3 {
            margin-bottom: 10px;
            font-size: 1.5rem;
            color: #333;
        }

        .itinerary-card ul {
            list-style: none;
            padding: 0;
        }

        .itinerary-card ul li {
            margin-bottom: 10px;
            font-size: 1rem;
            color: #555;
        }

        .day-header {
            text-align: center;
            margin: 50px 0 30px;
            position: relative;
        }

        .day-header::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(0, 123, 255, 0.1);
            z-index: -1;
        }

        .day-header h2 {
            background: linear-gradient(135deg, #007bff, #00bfff);
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            font-size: 1.6em;
            box-shadow: 0 4px 15px rgba(0,123,255,0.2);
        }

        .day-header .date-text {
            display: block;
            font-size: 0.7em;
            margin-top: 8px;
            font-weight: normal;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .dining-info {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dining-info i {
            color: #ff4757;
        }

        .timeline-time {
            background: #ff4757;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .dining-content {
            background: #fff8f8; /* Slightly different background for dining cards */
            border-left: 4px solid #ff4757;
        }

        .dining-content p,
        .accommodation-content p {
            text-align: justify;
            text-indent: 30px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .dining-content h3 {
            color: #ff4757;
        }

        .dining-content .fa-utensils {
            margin-right: 8px;
        }

        .restaurant-dot {
            background: #ff4757;
            border: 3px solid #ffe5e7;
        }

        .dining-content .timeline-time {
            background: #ff6b6b;
        }

        .spot-content {
        background: #f0f8ff; /* Light blue background for tourist spots */
        border-left: 4px solid #007bff;
        }

        .spot-content h3 {
            color: #007bff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .spot-content .fa-landmark,
        .spot-content .fa-church,
        .spot-content .fa-mountain,
        .spot-content .fa-water,
        .spot-content .fa-tree,
        .spot-content .fa-building,
        .spot-content .fa-monument,
        .spot-content .fa-umbrella-beach {
            margin-right: 8px;
        }

        .tourist-dot {
            background: #007bff;
            border: 3px solid #e6f2ff;
        }

        .spot-content .timeline-time {
            background: #007bff;
        }

        /* Accommodation specific styles */
        .accommodation-content {
            background: #fff0f7;
            border-left: 4px solid #e83e8c;
        }

        .accommodation-content h3 {
            color: #e83e8c;
        }

        .accommodation-content .fa-hotel {
            margin-right: 8px;
        }

        .accommodation-dot {
            background: #e83e8c;
            border: 3px solid #ffe6f2;
        }

        .accommodation-content .timeline-time {
            background: #e83e8c;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .timeline::after {
            content: 'End of Journey';
            position: absolute;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            padding: 20px 40px;
            background: linear-gradient(45deg, #007bff, #00bfff, #007bff);
            background-size: 200% 200%;
            color: white;
            border-radius: 25px;
            font-weight: bold;
            z-index: 2;
            font-size: 1.4rem;
            animation: gradientAnimation 3s ease infinite;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
        }

        .timeline-item:last-child {
            margin-bottom: 100px; /* Add extra space before the endpoint */
        }

        .modify-btn-container {
            position: fixed;
            top: 120px; /* Adjust based on your header height */
            right: 8px;
            z-index: 999;
        }

        .modify-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 35px;
            background-color: #007bff;
            border: 2px solid #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .modify-btn:hover {
            background-color: white;
            color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);;
        }

        .modify-btn i {
            font-size: 1.1em;
        }

        .download-btn-container {
            position: fixed;
            top: 180px; /* Positioned below modify button (120px + some spacing) */
            right: 8px; /* Match modify button's right position */
            z-index: 999;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 10px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            border: 2px solid #28a745;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            background-color: white;
            color: #28a745;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .download-btn i {
            font-size: 1.1em;
        }

        .save-btn-container {
            position: fixed;
            top: 240px; /* Positioned below download button */
            right: 8px;
            z-index: 999;
        }

        .save-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 33px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            border: 2px solid #6c757d;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .save-btn:hover {
            background-color: white;
            color: #6c757d;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .save-btn i {
            font-size: 1.1em;
        }

        .alert {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Fixed Header Section -->
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

    <div class="modify-btn-container">
        <a href="get_itinerary.php" class="modify-btn">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>

    <div class="download-btn-container">
        <button id="downloadBtn" class="download-btn">
            <i class="fas fa-download"></i> Download
        </button>
    </div>

    <div class="save-btn-container">
        <button id="saveBtn" class="save-btn">
            <i class="fas fa-save"></i> Save
        </button>
    </div>

    <div class="container">
        <div class="itinerary-header">
            <img src="../assets/background6.png" alt="Vigan Street">
            <div class="itinerary-header-content">
                <h1><?php echo htmlspecialchars($destination); ?> Tour Itinerary</h1>
                <p><?php echo $start_date->format('F j, Y'); ?> - <?php echo $end_date->format('F j, Y'); ?></p>
            </div>
        </div>

        <div class="itinerary-details">
            <div class="detail-item">
                <i class="fas fa-users"></i>
                <p><?php echo $people; ?> Travelers</p>
            </div>
            <div class="detail-item">
                <i class="fas fa-calendar-alt"></i>
                <p><?php echo $duration; ?> Days</p>
            </div>
            <div class="detail-item">
                <i class="fas fa-wallet"></i>
                <p>Budget: ₱<?php echo number_format($budget); ?></p>
            </div>
            <div class="detail-item">
                <i class="fas fa-coins"></i>
                <p>Total Cost: ₱<?php echo number_format($total_cost); ?></p>
            </div>
        </div>

        <!-- Destination closed error alert -->
        <?php if (!empty($error_messages)): ?>
            <?php foreach($error_messages as $error): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Restaurant error alert -->
        <?php if (!empty($restaurant_error)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($restaurant_error); ?>
            </div>
        <?php endif; ?>

        <div class="timeline">
            <?php for ($day = 0; $day < $duration; $day++): ?>
                <?php 
                $current_date = clone $start_date;
                $current_date->modify("+{$day} days");
                ?>
                <div class="day-header">
                    <h2>
                        Day <?php echo $day + 1; ?><br>
                        <span class="date-text"><?php echo $current_date->format('F j, Y'); ?></span>
                    </h2>
                </div>

                <?php
                // Check-in card for first day
                if ($accommodation_needed && $recommended_accommodation && $day === $accommodation_days['check_in']): ?>
                    <div class="timeline-item">
                        <a href="destination_details.php?id=<?php echo htmlspecialchars($recommended_accommodation['business_id']); ?>&type=business" 
                        class="timeline-content accommodation-content">
                            <div class="timeline-time">Morning</div>
                            <img src="../uploads/<?php echo htmlspecialchars($recommended_accommodation['destination_thumbnail']); ?>" 
                                alt="<?php echo htmlspecialchars($recommended_accommodation['name']); ?>" 
                                class="timeline-image">
                            <div class="category-badge">
                                <i class="fas fa-hotel"></i>
                                <span>Hotel</span>
                            </div>
                            <h3>Check-in: <?php echo htmlspecialchars($recommended_accommodation['name']); ?></h3>
                            <p>Start your journey by checking in to your accommodation</p>
                        </a>
                        <div class="timeline-dot accommodation-dot"></div>
                    </div>
                <?php endif; ?>

                <?php foreach ($optimized_itinerary[$day] as $item): ?>
                    <div class="timeline-item">
                        <?php
                        $itemData = $item['data'];
                        $contentClass = getContentClass($item['type']);
                        $dotClass = getDotClass($item['type']);
                        $detailsType = $item['type'] === 'restaurant' ? 'business' : 'tourist';
                        $itemId = $item['type'] === 'restaurant' ? $itemData['business_id'] : $itemData['tourist_spot_id'];
                        ?>
                        
                        <a href="destination_details.php?id=<?php echo htmlspecialchars($itemId); ?>&type=<?php echo $detailsType; ?>" 
                        class="timeline-content <?php echo $contentClass; ?>">
                            <div class="timeline-time"><?php echo getTimePeriodDisplay($item['time']); ?></div>
                            <img src="../uploads/<?php echo htmlspecialchars($itemData['destination_thumbnail']); ?>" 
                                alt="<?php echo htmlspecialchars($itemData['name']); ?>" 
                                class="timeline-image">
                            <div class="category-badge">
                                <i class="fas <?php echo $item['type'] === 'restaurant' ? 'fa-utensils' : getCategoryIcon($itemData['category']); ?>"></i>
                                <span><?php echo htmlspecialchars($itemData['category']); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($itemData['name']); ?></h3>
                            <p><?php echo htmlspecialchars($itemData['description'] ?? ''); ?></p>
                        </a>
                        <div class="timeline-dot <?php echo $dotClass; ?>"></div>
                    </div>
                <?php endforeach; ?>

                <?php 
                // Check-out card for last day
                if ($accommodation_needed && $recommended_accommodation && $day === $accommodation_days['check_out']): ?>
                    <div class="timeline-item">
                        <a href="destination_details.php?id=<?php echo htmlspecialchars($recommended_accommodation['business_id']); ?>&type=business" 
                        class="timeline-content accommodation-content">
                            <div class="timeline-time">Evening</div>
                            <img src="../uploads/<?php echo htmlspecialchars($recommended_accommodation['destination_thumbnail']); ?>"
                                alt="<?php echo htmlspecialchars($recommended_accommodation['name']); ?>" 
                                class="timeline-image">
                            <div class="category-badge">
                                <i class="fas fa-hotel"></i>
                                <span>Hotel</span>
                            </div>
                            <h3>Check-out: <?php echo htmlspecialchars($recommended_accommodation['name']); ?></h3>
                            <p>End your journey by checking out from your accommodation</p>
                        </a>
                        <div class="timeline-dot accommodation-dot"></div>
                    </div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>

        <div class="notes-section">
            <h3><i class="fas fa-exclamation-circle"></i> Important Notes</h3>
            <ul>
                <li>
                    <i class="fas fa-copy"></i>
                    <span>Always carry a copy of your itinerary and important contact numbers</span>
                </li>
                <li>
                    <i class="fas fa-cloud-sun"></i>
                    <span>Check the weather forecast before visiting outdoor destinations</span>
                </li>
                <li>
                    <i class="fas fa-phone-alt"></i>
                    <span>Keep emergency contact numbers handy (local police, hospitals, tourist help desk)</span>
                </li>
                <li>
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Bring cash as some establishments might not accept cards</span>
                </li>
                <li>
                    <i class="fas fa-water"></i>
                    <span>Bring a water bottle and stay hydrated during tours</span>
                </li>
                <li>
                    <i class="fas fa-shield-alt"></i>
                    <span>Keep your valuables secure and be mindful of your belongings</span>
                </li>
            </ul>
        </div>
    </div>

    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        // Core elements
            const downloadBtn = document.querySelector('.download-btn-container');
            const modifyBtn = document.querySelector('.modify-btn-container');
            const saveBtn = document.querySelector('.save-btn-container');
            const container = document.querySelector('.container');
            const profileBtn = document.querySelector('.profile-btn');
            const dropdownContent = document.querySelector('.dropdown-content');

            // Profile dropdown functionality
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

            // Save button functionality
            document.getElementById('saveBtn').addEventListener('click', function() {
                const isLoggedIn = document.querySelector('.profile-dropdown') !== null;
                
                if (!isLoggedIn) {
                    alert('Please log in to save itineraries');
                    window.location.href = 'login_useracc.php';
                    return;
                }

                const itineraryData = JSON.parse(document.getElementById('saveBtn').getAttribute('data-itinerary'));

                fetch('save_itinerary.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(itineraryData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Itinerary saved successfully!');
                    } else {
                        alert('Error saving itinerary: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving itinerary');
                });
            });

            // Download button functionality
            document.getElementById('downloadBtn').addEventListener('click', async function() {
                // Hide buttons during capture
                downloadBtn.style.display = 'none';
                modifyBtn.style.display = 'none';
                saveBtn.style.display = 'none';

                try {
                    // Pre-process badges
                    const badges = container.querySelectorAll('.category-badge');
                    badges.forEach(badge => {
                        // Badge container styles
                        badge.style.setProperty('display', 'inline-flex', 'important');
                        badge.style.setProperty('visibility', 'visible', 'important');
                        badge.style.setProperty('align-items', 'center', 'important');
                        badge.style.setProperty('gap', '8px', 'important');
                        
                        // Badge text styles
                        const span = badge.querySelector('span');
                        if (span) {
                            span.style.setProperty('display', 'inline-block', 'important');
                            span.style.setProperty('visibility', 'visible', 'important');
                            span.style.setProperty('color', 'white', 'important');
                            span.style.setProperty('margin-left', '8px', 'important');
                            span.style.setProperty('opacity', '1', 'important');
                        }
                        
                        // Badge icon styles
                        const icon = badge.querySelector('i');
                        if (icon) {
                            icon.style.setProperty('display', 'inline-block', 'important');
                            icon.style.setProperty('visibility', 'visible', 'important');
                            icon.style.setProperty('color', 'white', 'important');
                            icon.style.setProperty('opacity', '1', 'important');
                        }
                    });

                    // Wait for styles to apply
                    await new Promise(resolve => setTimeout(resolve, 500));

                    // Store original styles
                    const originalPadding = container.style.padding;
                    const timeline = document.querySelector('.timeline');
                    const originalMargin = timeline.style.marginBottom;

                    // Set capture styles
                    container.style.padding = '30px';
                    timeline.style.marginBottom = '150px';

                    // Capture the content
                    const canvas = await html2canvas(container, {
                        scale: 2,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: '#ffffff',
                        logging: true,
                        onclone: function(clonedDoc) {
                            const clonedBadges = clonedDoc.querySelectorAll('.category-badge');
                            clonedBadges.forEach(badge => {
                                // Force styles in cloned document
                                badge.style.setProperty('display', 'inline-flex', 'important');
                                badge.style.setProperty('visibility', 'visible', 'important');
                                badge.style.setProperty('align-items', 'center', 'important');
                                badge.style.setProperty('gap', '8px', 'important');
                                badge.style.setProperty('opacity', '1', 'important');
                                
                                const span = badge.querySelector('span');
                                if (span) {
                                    span.style.setProperty('display', 'inline-block', 'important');
                                    span.style.setProperty('visibility', 'visible', 'important');
                                    span.style.setProperty('color', 'white', 'important');
                                    span.style.setProperty('margin-left', '8px', 'important');
                                    span.style.setProperty('opacity', '1', 'important');
                                    
                                    // Ensure text content
                                    if (!span.textContent.trim()) {
                                        span.textContent = span.getAttribute('data-category') || 'Category';
                                    }
                                }
                                
                                const icon = badge.querySelector('i');
                                if (icon) {
                                    icon.style.setProperty('display', 'inline-block', 'important');
                                    icon.style.setProperty('visibility', 'visible', 'important');
                                    icon.style.setProperty('color', 'white', 'important');
                                    icon.style.setProperty('opacity', '1', 'important');
                                }
                            });
                        }
                    });

                    // Create and trigger download
                    const link = document.createElement('a');
                    link.download = 'itinerary.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();

                    // Restore original styles
                    container.style.padding = originalPadding;
                    timeline.style.marginBottom = originalMargin;

                } catch (error) {
                    console.error('Error generating image:', error);
                    alert('There was an error generating the image. Please try again.');
                } finally {
                    // Restore buttons
                    downloadBtn.style.display = 'block';
                    modifyBtn.style.display = 'block';
                    saveBtn.style.display = 'block';
                }
            });

            // Initialize page with data
            window.addEventListener('DOMContentLoaded', function() {
                const itineraryData = JSON.parse(document.querySelector('script[type="application/json"]').textContent);
                document.getElementById('saveBtn').setAttribute('data-itinerary', JSON.stringify(itineraryData));
            });
    </script>
    <script type="application/json">
        <?php echo json_encode($_POST); ?>
    </script>
</body>
</html>