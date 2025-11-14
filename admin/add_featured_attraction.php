<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_adminacc.php");
    exit();
}

include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $location = $conn->real_escape_string($_POST['location']);
    $category = $conn->real_escape_string($_POST['category']);
    $latitude = $conn->real_escape_string($_POST['latitude']);
    $longitude = $conn->real_escape_string($_POST['longitude']);

    // Handle thumbnail upload
    $thumbnailImage = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $thumbnailName = time() . '-thumbnail-' . basename($_FILES['thumbnail']['name']);
        $thumbnailPath = '../uploads/' . $thumbnailName;

        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath)) {
            $thumbnailImage = $thumbnailName;
        }
    }

    // Handle image uploads
    $uploadedImages = [];
    if (!empty(array_filter($_FILES['images']['name']))) {
        foreach ($_FILES['images']['name'] as $key => $image) {
            $imageTmpName = $_FILES['images']['tmp_name'][$key];
            $imageName = time() . '-' . $key . '-' . basename($image);
            $imagePath = '../uploads/' . $imageName;

            if (move_uploaded_file($imageTmpName, $imagePath)) {
                $uploadedImages[] = $imageName;
            }
        }
    }

    $images = implode(',', $uploadedImages);

    // Insert data into the database
    $sql = "INSERT INTO featured_attractions (name, description, location, category, latitude, longitude, images, destination_thumbnail) 
            VALUES ('$name', '$description', '$location', '$category', '$latitude', '$longitude', '$images', '$thumbnailImage')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = 'Featured attraction added successfully!';
        header("Location: manage_featured_attractions.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Featured Attraction - MViTour</title>
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

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 20px;
        }

        /* Form Container */
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #007bff;
        }

        /* Page Title */
        .page-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }

        .form-section h2 {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: #007bff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section h2 i {
            font-size: 1.2rem;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input[type="file"] {
            padding: 10px 0;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        /* Category Selection */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .category-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .category-option:hover {
            transform: translateY(-2px);
            border-color: #007bff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .category-option.selected {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .category-option i {
            font-size: 2rem;
        }

        .category-option span {
            text-align: center;
            font-weight: 500;
        }

        /* Image Preview */
        .image-preview {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .image-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        .thumbnail-preview {
            margin-top: 10px;
        }

        .thumbnail-preview img {
            max-width: 200px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        /* Buttons */
        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 2px solid transparent;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: white;
            color: #007bff;
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

        /* Map Note */
        .map-note {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .map-note i {
            color: #856404;
            font-size: 1.2rem;
        }

        .map-note p {
            color: #856404;
            margin: 0;
        }

        .map-note a {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>
<body>
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
            <a href="manage_featured_attractions.php">Featured Attractions</a>
            <i class="fas fa-chevron-right"></i>
            <span>Add Featured Attraction</span>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div class="page-title">
                <h1>Add Featured Attraction</h1>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <!-- Basic Information -->
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                    <div class="form-group">
                        <label>Name of the Attraction:</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Category:</label>
                        <input type="hidden" name="category" id="selectedCategory" required>
                        <div class="category-grid">
                            <div class="category-option" data-value="Heritage Sites">
                                <i class="fas fa-landmark"></i>
                                <span>Heritage Sites</span>
                            </div>
                            <div class="category-option" data-value="Natural Wonders">
                                <i class="fas fa-mountain"></i>
                                <span>Natural Wonders</span>
                            </div>
                            <div class="category-option" data-value="Cultural Spots">
                                <i class="fas fa-masks-theater"></i>
                                <span>Cultural Spots</span>
                            </div>
                            <div class="category-option" data-value="Local Delicacies/Food Spots">
                                <i class="fas fa-utensils"></i>
                                <span>Local Delicacies/Food Spots</span>
                            </div>
                            <div class="category-option" data-value="Traditional Crafts">
                                <i class="fas fa-hands-holding"></i>
                                <span>Traditional Crafts</span>
                            </div>
                            <div class="category-option" data-value="Festivals & Events">
                                <i class="fas fa-calendar-day"></i>
                                <span>Festivals & Events</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" required></textarea>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="form-section">
                    <h2><i class="fas fa-map-marker-alt"></i> Location Details</h2>
                    <div class="form-group">
                        <label>Location:</label>
                        <input type="text" name="location" required placeholder="Enter the location">
                    </div>

                    <div class="map-note">
                        <i class="fas fa-info-circle"></i>
                        <p>To add the map location, obtain the latitude and longitude coordinates of the destination from 
                        <a href="https://www.google.com/maps" target="_blank">Google Maps</a>.</p>
                    </div>

                    <div class="form-group">
                        <label>Latitude:</label>
                        <input type="text" name="latitude" required>
                    </div>

                    <div class="form-group">
                        <label>Longitude:</label>
                        <input type="text" name="longitude" required>
                    </div>
                </div>

                <!-- Images -->
                <div class="form-section">
                    <h2><i class="fas fa-images"></i> Images</h2>
                    <div class="form-group">
                        <label>Insert Thumbnail:</label>
                        <input type="file" name="thumbnail" accept="image/*" onchange="previewThumbnail(event)" required>
                        <div class="thumbnail-preview" id="thumbnailPreview"></div>
                    </div>

                    <div class="form-group">
                        <label>Insert Images:</label>
                        <input type="file" name="images[]" multiple accept="image/*" onchange="previewImages(event)" required>
                        <div class="image-preview" id="imagePreview"></div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Featured Attraction
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Preview Thumbnail Image
        function previewThumbnail(event) {
            const thumbnailPreview = document.getElementById("thumbnailPreview");
            thumbnailPreview.innerHTML = "";
            const file = event.target.files[0];
            if (file) {
                const img = document.createElement("img");
                img.src = URL.createObjectURL(file);
                thumbnailPreview.appendChild(img);
            }
        }

        // Preview Selected Images
        function previewImages(event) {
            const imagePreview = document.getElementById("imagePreview");
            imagePreview.innerHTML = "";
            const files = event.target.files;
            Array.from(files).forEach(file => {
                const img = document.createElement("img");
                img.src = URL.createObjectURL(file);
                imagePreview.appendChild(img);
            });
        }

        // Category selection
        document.querySelectorAll('.category-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.category-option').forEach(opt => 
                    opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedCategory').value = this.dataset.value;
            });
        });

        // Confirm before leaving page
        let formChanged = false;
        const form = document.querySelector('form');

        form.addEventListener('change', () => {
            formChanged = true;
        });

        // Add this event listener to handle form submission
        form.addEventListener('submit', () => {
            // Reset formChanged when form is being submitted intentionally
            formChanged = false;
        });

        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Navigation confirmation
        document.querySelectorAll('.breadcrumb a, .logout-btn').forEach(link => {
            link.addEventListener('click', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to leave? Any unsaved changes will be lost.')) {
                        window.location.href = this.href;
                    }
                }
            });
        });
    </script>
</body>
</html>