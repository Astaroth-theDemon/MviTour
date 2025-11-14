<?php
session_start();
include '../db.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Check if the ID and type are provided in the URL
if (isset($_GET['id']) && isset($_GET['type'])) {
    $id = $_GET['id'];
    $type = $_GET['type'];

    // Fetch details based on the type
    if ($type === 'tourist') {
        $sql = "SELECT * FROM Tourist_Spots WHERE tourist_spot_id = $id AND status = 'active'";
    } elseif ($type === 'business') {
        $sql = "SELECT * FROM Businesses WHERE business_id = $id AND status = 'active'";
    } else {
        echo "Invalid type specified.";
        exit;
    }

    $result = $conn->query($sql);
    $destination = $result->fetch_assoc();

    if (!$destination) {
        echo "No destination found or destination is not active.";
        exit;
    }

    // Get images and split them
    $thumbnail = $destination['destination_thumbnail'];
    $images = explode(',', $destination['images']);
    array_unshift($images, $thumbnail); // Add thumbnail as the first image
} else {
    echo "No destination specified.";
    exit;
}

// Fetch average rating and rating counts
$sql_rating = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings,
    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
    FROM ratings 
    WHERE destination_id = ? AND destination_type = ?";

$stmt = $conn->prepare($sql_rating);
$stmt->bind_param("is", $id, $type);
$stmt->execute();
$rating_result = $stmt->get_result()->fetch_assoc();

$avg_rating = $rating_result['avg_rating'] ? round($rating_result['avg_rating'], 1) : 0;
$total_ratings = $rating_result['total_ratings'];
$rating_counts = [
    5 => $rating_result['five_star'],
    4 => $rating_result['four_star'],
    3 => $rating_result['three_star'], 
    2 => $rating_result['two_star'],
    1 => $rating_result['one_star']
];

// Check if user has already rated
$user_rating = null;
if ($isLoggedIn) {
    $sql_user_rating = "SELECT rating FROM ratings WHERE user_id = ? AND destination_id = ? AND destination_type = ?";
    $stmt = $conn->prepare($sql_user_rating);
    $stmt->bind_param("iis", $_SESSION['user_id'], $id, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_rating = $result->fetch_assoc()['rating'];
    }
}

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    if (!$isLoggedIn) {
        echo json_encode(['error' => 'Please log in to rate']);
        exit;
    }

    $rating = intval($_POST['rating']);
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['error' => 'Invalid rating']);
        exit;
    }

    $sql = $user_rating 
        ? "UPDATE ratings SET rating = ? WHERE user_id = ? AND destination_id = ? AND destination_type = ?"
        : "INSERT INTO ratings (rating, user_id, destination_id, destination_type) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $rating, $_SESSION['user_id'], $id, $type);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to save rating']);
    }
    exit;
}

// Fetch comments
$sql_comments = "SELECT c.*, u.username 
                FROM comments c 
                JOIN users u ON c.user_id = u.user_id 
                WHERE c.destination_id = ? AND c.destination_type = ? 
                ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql_comments);
$stmt->bind_param("is", $id, $type);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!$isLoggedIn) {
        echo json_encode(['error' => 'Please log in to comment']);
        exit;
    }

    $comment = trim($_POST['comment']);
    if (empty($comment)) {
        echo json_encode(['error' => 'Comment cannot be empty']);
        exit;
    }

    $sql = "INSERT INTO comments (user_id, destination_id, destination_type, username, comment_text) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $_SESSION['user_id'], $id, $type, $_SESSION['username'], $comment);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to save comment']);
    }
    exit;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($destination['name']); ?> - MViTour</title>
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

        /* Header styles - matching other pages */
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

        /* Main Content Container */
        .main-container {
            max-width: 1200px;
            margin: 80px auto 40px;
            padding: 20px;
        }

        /* Image Gallery Styles */
        .gallery-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .image-viewer {
            width: 100%;
            height: 530px;
            position: relative;
            overflow: hidden;
            background: #f8f9fa;
        }

        .slide-container {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            transition: transform 0.3s ease-in-out;
        }

        .slide {
            min-width: 100%;
            width: 100%;
            height: 100%;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: fill;
        }

        /* Image Preview Thumbnails */
        .image-previews {
            display: flex;
            gap: 10px;
            padding: 15px;
            overflow-x: auto;
            background: white;
        }

        .image-preview {
            width: 80px;
            height: 60px;
            border-radius: 5px;
            cursor: pointer;
            object-fit: cover;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .image-preview:hover {
            transform: translateY(-2px);
        }

        .image-preview.active {
            border-color: #007bff;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .image-previews::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .image-previews {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        .nav-arrows {
            position: absolute;
            top: 50%;
            width: 100%;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }

        .nav-arrows span {
            cursor: pointer;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .nav-arrows span:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        /* Destination Info Styles */
        .info-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .destination-header {
            margin-bottom: 20px;
        }

        .destination-title {
            font-size: 2rem;
            font-family: 'Playfair Display', serif;
            margin-bottom: 15px;
            color: #333;
        }

        .location-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            margin-bottom: 15px;
        }

        .price-info {
            font-size: 1.1rem;
            color: #28a745;
            margin-bottom: 20px;
        }

        .about-section {
            margin-top: 30px;
        }

        .about-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .description {
            line-height: 1.8;
            color: #555;
            text-align: justify;
            text-indent: 50px;
        }

        /* Map Section */
        .map-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .map-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .map-container {
            height: 400px;
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 10px;
            }

            .image-viewer {
                width: 100%;
                position: relative;
                overflow: hidden;
                background: #f8f9fa;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .destination-title {
                font-size: 1.5rem;
            }

            .map-container {
                height: 300px;
            }
        }

        /* Add these styles to the existing <style> section */
        .ratings-container {
            background: white;
            border-radius: 15px;
            margin: 30px 0;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 30px;
        }

        .ratings-left {
            flex: 0.3;
            padding-right: 30px;
            border-right: 1px solid #eee;
        }

        .overall-rating {
            text-align: center;
            margin-bottom: 30px;
        }

        .rating-number {
            font-size: 3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .total-ratings {
            color: #666;
            font-size: 0.9rem;
        }

        .rating-bars {
            margin-top: 20px;
        }

        .rating-bar-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .rating-label {
            min-width: 30px;
            margin-right: 10px;
            color: #666;
        }

        .rating-bar {
            flex: 1;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-bar-fill {
            height: 100%;
            background: #ffc107;
            border-radius: 4px;
        }

        .rating-count {
            min-width: 50px;
            margin-left: 10px;
            color: #666;
            text-align: right;
        }

        .add-rating-btn {
            display: block;
            align-items: center;
            gap: 8px;
            margin: 20px auto; 
            padding: 10px 30px;
            background: #007bff;
            color: white;
            border: 2px solid #007bff;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            width: fit-content; 
            font-weight: 500;
        }

        .add-rating-btn:hover {
            background-color: white;
            color: #007bff;
            transform: translateY(-2px);
        }

        .add-rating-btn i {
            font-size: 1.2rem; /* Match the text size */
        }

        /* Rating Modal Styles */
        .rating-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .rating-modal.active {
            display: flex;
        }

        .rating-modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .rating-modal h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .star-rating {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .star-rating .star {
            margin: 0 5px;
            transition: color 0.3s ease;
        }

        .star-rating .star.active {
            color: #ffc107;
        }

        .star-rating .star:hover {
            color: #ffc107;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .modal-btn.submit {
            background: #007bff;
            color: white;
        }

        .modal-btn.cancel {
            background: #6c757d;
            color: white;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
            margin-bottom: 15px;
        }

        .login-prompt-btn {
            padding: 8px 20px;
            background: #007bff;
            color: white;
            border: 2px solid #007bff;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;  /* Add this line */
            display: inline-block;
        }

        .login-prompt-btn:hover {
            background-color: white;
            color: #007bff;
        }

        .comments-right {
            flex: 0.7;
            display: flex;
            flex-direction: column;
        }

        .comments-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .comments-title {
            font-size: 1.5rem;
            font-weight: 500;
            color: #333;
        }

        .add-comment-btn {
            padding: 13px 30px;
            background: #007bff;
            color: white;
            border: 2px solid #007bff;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .add-comment-btn:hover {
            background-color: white;
            color: #007bff;
            transform: translateY(-2px);
        }

        .comments-list {
            flex: 1;
            overflow-y: auto;
            max-height: 600px;
            padding-right: 15px;
        }

        .comment-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .comment-user {
            font-weight: 500;
            color: #333;
        }

        .comment-date {
            color: #666;
            font-size: 0.9rem;
        }

        .comment-text {
            color: #555;
            line-height: 1.5;
        }

        .comment-form {
            margin-top: 20px;
            display: none;
        }

        .comment-form.active {
            display: block;
        }

        .comment-textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            min-height: 80px;
            margin-bottom: 10px;
        }

        .comment-form-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .comment-form-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .comment-form-btn.submit {
            background: #007bff;
            color: white;
        }

        .comment-form-btn.cancel {
            background: #6c757d;
            color: white;
        }

        .comment-form-btn:hover {
            transform: translateY(-2px);
        }

        .no-comments {
            text-align: center;
            color: #666;
            padding: 20px;
        }

        /* Search Box Styling */
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

        /* Search Results Styling */
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

        .category-badge, .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .category-badge {
            background: linear-gradient(45deg, #007bff, #00bfff);
            color: white;
        }

        .status-badge {
            display: block; /* Makes it appear on a new line */
            width: fit-content; /* Only take up as much width as needed */
        }

        .status-badge.open {
            background-color: #28a745;
            color: white;
        }

        .status-badge.closed {
            background-color: #dc3545;
            color: white;
        }

        .operating-hours {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            margin: 10px 0;
            font-size: 0.95rem;
        }

        .operating-hours i {
            color: #007bff;
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

    <div class="main-container">
        <!-- Image Gallery Section -->
        <div class="gallery-section">
        <div class="image-viewer">
            <div class="slide-container" id="slideContainer">
                <div class="slide">
                    <img id="currentImage" src="../uploads/<?php echo htmlspecialchars($thumbnail); ?>" alt="Destination Image">
                </div>
            </div>
            <div class="nav-arrows">
                <span onclick="prevImage()">❮</span>
                <span onclick="nextImage()">❯</span>
            </div>
        </div>
            <div class="image-previews" id="imagePreviews">
                <?php foreach($images as $index => $image): ?>
                    <img 
                        src="../uploads/<?php echo htmlspecialchars($image); ?>" 
                        alt="Preview <?php echo $index + 1; ?>"
                        class="image-preview <?php echo $index === 0 ? 'active' : ''; ?>"
                        onclick="showImage(<?php echo $index; ?>)"
                    >
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Destination Info Section -->
        <div class="info-section">
            <div class="destination-header">
                <h1 class="destination-title"><?php echo htmlspecialchars($destination['name']); ?></h1>
                <div class="category-badge">
                <i class="fas <?php echo getCategoryIcon($destination['category'], $type); ?>"></i>
                <?php echo htmlspecialchars($destination['category']); ?>
            </div>
            <div class="status-badge <?php echo $destination['is_open'] ? 'open' : 'closed'; ?>">
                <i class="fas <?php echo $destination['is_open'] ? 'fa-door-open' : 'fa-door-closed'; ?>"></i>
                <?php echo $destination['is_open'] ? 'Open' : 'Closed'; ?>
            </div>
                <?php if ($destination['opening_time'] && $destination['closing_time']): ?>
                <div class="operating-hours">
                    <i class="fas fa-clock"></i>
                    <span>Operating Hours: <?php 
                        echo date('g:i A', strtotime($destination['opening_time'])) . ' - ' . 
                            date('g:i A', strtotime($destination['closing_time'])); 
                    ?></span>
                </div>
                <?php endif; ?>
                <div class="location-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($destination['barangay'] . ', ' . $destination['location']); ?></span>
                </div>
                <div class="price-info">
                    <i class="fas fa-tag"></i>
                    <?php if ($type === 'tourist'): ?>
                        <span>₱<?php echo number_format($destination['entrance_fee'], 2); ?> entrance fee</span>
                    <?php else: ?>
                        <span>Starting at ₱<?php echo number_format($destination['budget'], 2); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="about-section">
                <h2>About</h2>
                <p class="description"><?php echo htmlspecialchars($destination['description']); ?></p>
            </div>
        </div>

        <!-- Map Section -->
        <div class="map-section">
            <div class="map-header">
                <h2>Location</h2>
            </div>
            <div class="map-container" id="map"></div>
        </div>

        <!-- Ratings Container -->
        <div class="ratings-container">
            <div class="ratings-left">
                <div class="overall-rating">
                    <div class="rating-number">
                        <?php echo $avg_rating; ?> 
                        <i class="fas fa-star" style="color: #ffc107; font-size: 0.8em;"></i>
                    </div>
                    <div class="rating-stars">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $avg_rating) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i - 0.5 <= $avg_rating) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <div class="total-ratings"><?php echo $total_ratings; ?> ratings</div>
                </div>

                <div class="rating-bars">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <div class="rating-bar-item">
                            <span class="rating-label">
                                <?php echo $i; ?> <i class="fas fa-star" style="color: #ffc107; font-size: 0.9em;"></i>
                            </span>
                            <div class="rating-bar">
                                <div class="rating-bar-fill" style="width: <?php 
                                    echo $total_ratings > 0 ? ($rating_counts[$i] / $total_ratings * 100) : 0;
                                ?>%"></div>
                            </div>
                            <span class="rating-count"><?php echo $rating_counts[$i]; ?></span>
                        </div>
                    <?php endfor; ?>
                </div>

                <button class="add-rating-btn" onclick="showRatingModal()">
                    <i class="fas fa-star"></i>
                    <?php echo $user_rating ? 'Update Your Rating' : 'Add Your Rating'; ?>
                </button>
            </div>
            
            <!-- Comments section -->
            <div class="comments-right">
                <div class="comments-header">
                    <h3 class="comments-title">Comments</h3>
                    <button class="add-comment-btn" onclick="showCommentForm()">
                        <i class="fas fa-comment"></i> Add Comment
                    </button>
                </div>

                <form class="comment-form" id="commentForm">
                    <textarea class="comment-textarea" placeholder="Write your comment..." required></textarea>
                    <div class="comment-form-buttons">
                        <button type="button" class="comment-form-btn cancel" onclick="hideCommentForm()">Cancel</button>
                        <button type="submit" class="comment-form-btn submit">Submit</button>
                    </div>
                </form>

                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                        <div class="no-comments">No comments yet. Be the first to comment!</div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <span class="comment-user"><?php echo htmlspecialchars($comment['username']); ?></span>
                                    <span class="comment-date"><?php echo date('M j, Y', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <div class="comment-text">
                                    <?php echo htmlspecialchars($comment['comment_text']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Rating Modal -->
        <div class="rating-modal" id="ratingModal">
            <div class="rating-modal-content">
                <h3>Rate this destination</h3>
                <div class="star-rating" id="starRating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?php echo $i <= $user_rating ? 'active' : ''; ?>" 
                            data-rating="<?php echo $i; ?>">
                            <i class="fas fa-star"></i>
                        </span>
                    <?php endfor; ?>
                </div>
                <div class="modal-buttons">
                    <button class="modal-btn cancel" onclick="hideRatingModal()">Cancel</button>
                    <button class="modal-btn submit" onclick="submitRating()">Submit</button>
                </div>
            </div>
        </div>

        <!-- Login Prompt -->
        <div class="login-prompt" id="loginPrompt">
            <p>Please log in to add a rating</p>
            <a href="login_useracc.php" class="login-prompt-btn">Log In</a>
        </div>
    </div>

    <script>
        // Image Gallery Scripts
        const images = <?php echo json_encode($images); ?>;
        let currentIndex = 0;
        let isTransitioning = false;

        function showImage(index, direction) {
            if (index >= 0 && index < images.length && !isTransitioning) {
                isTransitioning = true;
                const slideContainer = document.getElementById('slideContainer');
                
                // Create new image element
                const newImage = document.createElement('img');
                newImage.src = `../uploads/${images[index]}`;
                newImage.alt = "Destination Image";
                
                // Create new slide div
                const newSlide = document.createElement('div');
                newSlide.className = 'slide';
                newSlide.appendChild(newImage);
                
                // Reset any existing transition
                slideContainer.style.transition = 'none';
                
                if (direction === 'right') {
                    // For next image
                    slideContainer.appendChild(newSlide);
                    slideContainer.style.transform = 'translateX(0)';
                    
                    // Force browser reflow
                    slideContainer.offsetHeight;
                    
                    // Apply transition and move
                    slideContainer.style.transition = 'transform 0.3s ease-in-out';
                    slideContainer.style.transform = 'translateX(-100%)';
                } else if (direction === 'left') {
                    // For previous image
                    slideContainer.insertBefore(newSlide, slideContainer.firstChild);
                    slideContainer.style.transform = 'translateX(-100%)';
                    
                    // Force browser reflow
                    slideContainer.offsetHeight;
                    
                    // Apply transition and move
                    slideContainer.style.transition = 'transform 0.3s ease-in-out';
                    slideContainer.style.transform = 'translateX(0)';
                }
                
                // Update current index and previews
                currentIndex = index;
                updatePreviews();
                
                // Cleanup after transition
                setTimeout(() => {
                    while (slideContainer.children.length > 1) {
                        slideContainer.removeChild(direction === 'right' ? slideContainer.firstChild : slideContainer.lastChild);
                    }
                    slideContainer.style.transition = 'none';
                    slideContainer.style.transform = 'translateX(0)';
                    
                    // Reset transition after cleanup
                    setTimeout(() => {
                        slideContainer.style.transition = 'transform 0.3s ease-in-out';
                        isTransitioning = false;
                    }, 20);
                }, 300);
            }
        }

        function updatePreviews() {
            const previews = document.querySelectorAll('.image-preview');
            previews.forEach((preview, index) => {
                if (index === currentIndex) {
                    preview.classList.add('active');
                } else {
                    preview.classList.remove('active');
                }
            });
        }

        function updateImageIndicator() {
            const imageIndicator = document.getElementById('imageIndicator');
            imageIndicator.textContent = `Image ${currentIndex + 1} of ${images.length}`;
        }

        function prevImage() {
            if (!isTransitioning) {
                const newIndex = (currentIndex - 1 + images.length) % images.length;
                showImage(newIndex, 'left');
            }
        }

        function nextImage() {
            if (!isTransitioning) {
                const newIndex = (currentIndex + 1) % images.length;
                showImage(newIndex, 'right');
            }
        }

        // Initialize the first image
        document.addEventListener('DOMContentLoaded', () => {
            updatePreviews();
        });

        // Map Initialization
        function initMap() {
    const destination = { 
        lat: <?php echo $destination['latitude']; ?>, 
        lng: <?php echo $destination['longitude']; ?> 
    };
    
    const map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: destination,
        styles: [
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            }
        ]
    });

    // Create marker for destination
    const destinationMarker = new google.maps.Marker({
        position: destination,
        map: map,
        title: '<?php echo htmlspecialchars($destination['name']); ?>',
        icon: {
            url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
            scaledSize: new google.maps.Size(40, 40)
        }
    });

    // Add info window for destination
    const destinationInfo = new google.maps.InfoWindow({
        content: '<div style="padding: 10px;">' +
                 '<h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($destination['name']); ?></h3>' +
                 '<p><?php echo htmlspecialchars($destination['barangay'] . ', ' . $destination['location']); ?></p>' +
                 '</div>'
    });

    destinationMarker.addListener('click', () => {
        destinationInfo.open(map, destinationMarker);
    });


    // Check if geolocation is available
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                // Add marker for user's location
                const userMarker = new google.maps.Marker({
                    position: userLocation,
                    map: map,
                    title: 'Your Location',
                    icon: {
                        url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                        scaledSize: new google.maps.Size(40, 40)
                    }
                });

                // Add info window for user location
                const userInfo = new google.maps.InfoWindow({
                    content: '<div style="padding: 10px;"><h3>Your Location</h3></div>'
                });

                userMarker.addListener('click', () => {
                    userInfo.open(map, userMarker);
                });

                // Create a DirectionsService object to use the route method
                const directionsService = new google.maps.DirectionsService();
                
                // Create a DirectionsRenderer object to display the route
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    map: map,
                    suppressMarkers: true // Hide default markers as we're using custom ones
                });

                // Define the request for the route
                const request = {
                    origin: userLocation,
                    destination: destination,
                    travelMode: google.maps.TravelMode.DRIVING,
                    provideRouteAlternatives: true // Show alternative routes if available
                };

                // Get the route from DirectionsService
                directionsService.route(request, function(response, status) {
                    if (status === 'OK') {
                        // Display the route on the map
                        directionsRenderer.setDirections(response);

                        // Get the first route
                        const route = response.routes[0];
                        const leg = route.legs[0];

                        // Create info window for route details
                        const infoWindow = new google.maps.InfoWindow({
                            content: `
                                <div style="padding: 10px;">
                                    <h3 style="margin-bottom: 8px;">Route Information</h3>
                                    <div style="margin-bottom: 5px;">
                                        <strong>Distance:</strong> ${leg.distance.text}
                                    </div>
                                    <div style="margin-bottom: 5px;">
                                        <strong>Duration:</strong> ${leg.duration.text}
                                    </div>
                                    <div style="margin-bottom: 5px;">
                                        <strong>From:</strong> ${leg.start_address}
                                    </div>
                                    <div>
                                        <strong>To:</strong> ${leg.end_address}
                                    </div>
                                </div>
                            `
                        });

                        // Position the info window at the midpoint of the route
                        const midpoint = Math.floor(leg.steps.length / 2);
                        infoWindow.setPosition(leg.steps[midpoint].start_location);
                        infoWindow.open(map);

                        // Adjust map bounds to show the entire route
                        const bounds = new google.maps.LatLngBounds();
                        bounds.extend(userLocation);
                        bounds.extend(destination);
                        map.fitBounds(bounds);
                    } else {
                        window.alert('Directions request failed due to ' + status);
                    }
                });

            },
            (error) => {
                console.error('Error getting user location:', error);
                handleLocationError(true, map.getCenter());
            }
        );
    } else {
        // Browser doesn't support Geolocation
        handleLocationError(false, map.getCenter());
    }
}

// Function to handle location errors
function handleLocationError(browserHasGeolocation, pos) {
    const infoWindow = new google.maps.InfoWindow();
    infoWindow.setPosition(pos);
    infoWindow.setContent(
        browserHasGeolocation ?
        'Error: The Geolocation service failed.' :
        'Error: Your browser doesn\'t support geolocation.'
    );
    infoWindow.open(map);
}
        // Rating Modal Functions
        function showRatingModal() {
            <?php if (!$isLoggedIn): ?>
            document.getElementById('loginPrompt').classList.add('active');
            setTimeout(() => {
                document.getElementById('loginPrompt').classList.remove('active');
            }, 3000);
            return;
            <?php endif; ?>
            
            document.getElementById('ratingModal').classList.add('active');
        }

        function hideRatingModal() {
            document.getElementById('ratingModal').classList.remove('active');
        }

        // Star Rating Functionality
        const starRating = document.getElementById('starRating');
        let selectedRating = <?php echo $user_rating ?: 0; ?>;

        starRating.addEventListener('mouseover', (e) => {
            const star = e.target.closest('.star');
            if (!star) return;

            const rating = parseInt(star.dataset.rating);
            updateStars(rating);
        });

        starRating.addEventListener('mouseout', () => {
            updateStars(selectedRating);
        });

        starRating.addEventListener('click', (e) => {
            const star = e.target.closest('.star');
            if (!star) return;

            selectedRating = parseInt(star.dataset.rating);
            updateStars(selectedRating);
        });

        function updateStars(rating) {
            document.querySelectorAll('.star').forEach((star, index) => {
                star.classList.toggle('active', index < rating);
            });
        }

        // Submit Rating Function
        function submitRating() {
            if (!selectedRating) {
                alert('Please select a rating');
                return;
            }

            const formData = new FormData();
            formData.append('rating', selectedRating);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    hideRatingModal();
                    // Reload page to show updated ratings
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to submit rating. Please try again.');
            });
        }

        // Close modals when clicking outside
        document.getElementById('ratingModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('ratingModal')) {
                hideRatingModal();
            }
        });

        document.getElementById('loginPrompt').addEventListener('click', (e) => {
            if (e.target === document.getElementById('loginPrompt')) {
                document.getElementById('loginPrompt').classList.remove('active');
            }
        });

        // Comment Functions
        function showCommentForm() {
            <?php if (!$isLoggedIn): ?>
            document.getElementById('loginPrompt').classList.add('active');
            setTimeout(() => {
                document.getElementById('loginPrompt').classList.remove('active');
            }, 3000);
            return;
            <?php endif; ?>
            
            document.getElementById('commentForm').classList.add('active');
        }

        function hideCommentForm() {
            document.getElementById('commentForm').classList.remove('active');
            document.querySelector('.comment-textarea').value = '';
        }

        // Handle comment submission
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const comment = document.querySelector('.comment-textarea').value.trim();
            if (!comment) return;

            const formData = new FormData();
            formData.append('comment', comment);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    hideCommentForm();
                    // Reload page to show new comment
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to submit comment. Please try again.');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
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
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAKPXW_Gzu-tewUUeJC-Iaxv2G0bFhkG0s&callback=initMap" async defer></script>
</body>
</html>