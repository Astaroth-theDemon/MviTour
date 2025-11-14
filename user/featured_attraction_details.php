<?php
session_start();
include '../db.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Check if attraction ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch attraction details
    $sql = "SELECT * FROM featured_attractions WHERE attraction_id = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $attraction = $result->fetch_assoc();

    if (!$attraction) {
        echo "No attraction found or attraction is not active.";
        exit;
    }

    // Get images and split them
    $thumbnail = $attraction['destination_thumbnail'];
    $images = explode(',', $attraction['images']);
    array_unshift($images, $thumbnail); // Add thumbnail as the first image
} else {
    echo "No attraction specified.";
    exit;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($attraction['name']); ?> - MViTour</title>
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
            background-color: #007bff;
            color: white;
        }
        
        .category-badge.featured {
            background: linear-gradient(45deg, #007bff, #00bfff);
            box-shadow: 0 4px 15px rgba(0,123,255,0.2);
        }

        .info-section .description {
            white-space: pre-line; /* Preserves line breaks in the description */
        }

        .featured-tag {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(45deg, #ffd700, #ffa500);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(255,215,0,0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .featured-tag i {
            font-size: 1.1rem;
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
            <div class="featured-tag">
                <i class="fas fa-crown"></i>
                <span>Featured Attraction</span>
            </div>
            <div class="image-viewer">
                <div class="slide-container" id="slideContainer">
                    <div class="slide">
                        <img id="currentImage" src="../uploads/<?php echo htmlspecialchars($thumbnail); ?>" 
                             alt="<?php echo htmlspecialchars($attraction['name']); ?>">
                    </div>
                </div>
                <div class="nav-arrows">
                    <span onclick="prevImage()">❮</span>
                    <span onclick="nextImage()">❯</span>
                </div>
            </div>
            <div class="image-previews" id="imagePreviews">
                <?php foreach($images as $index => $image): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($image); ?>" 
                         alt="Preview <?php echo $index + 1; ?>"
                         class="image-preview <?php echo $index === 0 ? 'active' : ''; ?>"
                         onclick="showImage(<?php echo $index; ?>)">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Attraction Info Section -->
        <div class="info-section">
            <div class="destination-header">
                <h1 class="destination-title"><?php echo htmlspecialchars($attraction['name']); ?></h1>
                <div class="category-badge featured">
                    <i class="fas <?php echo getFeaturedAttractionIcon($attraction['category']); ?>"></i>
                    <?php echo htmlspecialchars($attraction['category']); ?>
                </div>
                <div class="location-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($attraction['location']); ?></span>
                </div>
            </div>
            <div class="about-section">
                <h2>About</h2>
                <p class="description"><?php echo nl2br(htmlspecialchars($attraction['description'])); ?></p>
            </div>
        </div>

        <!-- Map Section -->
        <div class="map-section">
            <div class="map-header">
                <h2>Location</h2>
            </div>
            <div class="map-container" id="map"></div>
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
                newImage.alt = "Featured Attraction Image";
                
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

            // Search functionality
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
                                                <i class="fas ${getFeaturedAttractionIcon(item.category)}"></i>
                                                ${item.category}
                                            </div>
                                            <div class="result-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                ${item.location}
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

            // Hide search results when clicking outside
            document.addEventListener('click', function(e) {
                const searchBox = document.querySelector('.header-search');
                if (!searchBox.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        });

        // Map initialization
        function initMap() {
            const attraction = { 
                lat: <?php echo $attraction['latitude']; ?>, 
                lng: <?php echo $attraction['longitude']; ?> 
            };
            
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: attraction,
                styles: [
                    {
                        featureType: "poi",
                        elementType: "labels",
                        stylers: [{ visibility: "off" }]
                    }
                ]
            });

            const marker = new google.maps.Marker({
                position: attraction,
                map: map,
                title: '<?php echo htmlspecialchars($attraction['name']); ?>'
            });

            // Get directions if geolocation is available
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const origin = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    const directionsService = new google.maps.DirectionsService();
                    const directionsRenderer = new google.maps.DirectionsRenderer({
                        map: map,
                        suppressMarkers: true
                    });

                    directionsService.route({
                        origin: origin,
                        destination: attraction,
                        travelMode: google.maps.TravelMode.DRIVING
                    }, (response, status) => {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(response);
                        }
                    });
                });
            }
        }

        // Helper function for category icons
        function getFeaturedAttractionIcon(category) {
            switch (category) {
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
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAKPXW_Gzu-tewUUeJC-Iaxv2G0bFhkG0s&callback=initMap" async defer></script>
</body>
</html>