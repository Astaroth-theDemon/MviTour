<?php
include '../db.php';

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_adminacc.php");
    exit();
}

// Get the attraction ID from the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the existing attraction details
    $sql = "SELECT * FROM featured_attractions WHERE attraction_id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $attraction = $result->fetch_assoc();
        $currentImages = !empty($attraction['images']) ? explode(',', $attraction['images']) : array();
        $thumbnail = $attraction['destination_thumbnail'];
    } else {
        echo "<script>alert('Featured attraction not found!'); window.location.href='manage_featured_attractions.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('No attraction ID specified!'); window.location.href='manage_featured_attractions.php';</script>";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $location = $conn->real_escape_string($_POST['location']);
    $category = $conn->real_escape_string($_POST['category']);
    $latitude = $conn->real_escape_string($_POST['latitude']);
    $longitude = $conn->real_escape_string($_POST['longitude']);
    
    // Handle image deletions
    $imagesToKeep = isset($_POST['keep_images']) ? $_POST['keep_images'] : array();
    $updatedImages = array();
    
    foreach($currentImages as $img) {
        if (in_array($img, $imagesToKeep)) {
            $updatedImages[] = $img;
        } else {
            // Delete file if it exists
            $filepath = "../uploads/$img";
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    // Handle thumbnail update
    if (!empty($_FILES['thumbnail']['name'])) {
        $thumbnailTmpName = $_FILES['thumbnail']['tmp_name'];
        $thumbnailName = time() . '-' . $_FILES['thumbnail']['name'];
        $thumbnailPath = '../uploads/' . $thumbnailName;

        if (move_uploaded_file($thumbnailTmpName, $thumbnailPath)) {
            // Delete old thumbnail if exists and is different
            if (!empty($attraction['destination_thumbnail']) && 
                file_exists("../uploads/" . $attraction['destination_thumbnail']) &&
                $attraction['destination_thumbnail'] !== $thumbnailName) {
                unlink("../uploads/" . $attraction['destination_thumbnail']);
            }
            $thumbnail = $thumbnailName;
        }
    }

    // Handle new images
    if (!empty(array_filter($_FILES['images']['name']))) {
        foreach ($_FILES['images']['name'] as $key => $image) {
            $imageTmpName = $_FILES['images']['tmp_name'][$key];
            $imageName = time() . '-' . $key . '-' . basename($image);
            $imagePath = '../uploads/' . $imageName;

            if (move_uploaded_file($imageTmpName, $imagePath)) {
                $updatedImages[] = $imageName;
            }
        }
    }

    $imagesString = !empty($updatedImages) ? implode(',', $updatedImages) : '';

    // Update database
    $update_sql = "UPDATE featured_attractions SET 
                name = '$name',
                description = '$description',
                location = '$location',
                category = '$category',
                latitude = '$latitude',
                longitude = '$longitude',
                images = '$imagesString',
                destination_thumbnail = '$thumbnail'
               WHERE attraction_id = $id";

    if ($conn->query($update_sql) === TRUE) {
        $_SESSION['success_message'] = 'Featured attraction updated successfully!';
        header("Location: manage_featured_attractions.php");
        exit();
    } else {
        echo "Error updating featured attraction: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Featured Attraction - MViTour</title>
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
        .image-preview-container {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .image-preview-item {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #dee2e6;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview-item.empty {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .image-preview-item.empty i {
            font-size: 2rem;
        }

        .delete-checkbox {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }

        .delete-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
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

        /* Modal Styles */
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

        .modal-buttons .btn {
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
            min-width: 70px;  /* Set minimum width for consistent button sizes */
        }

        .modal-buttons .btn-secondary {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        .modal-buttons .btn-secondary:hover {
            background-color: white;
            color: #6c757d;
        }

        .modal-buttons .btn-primary {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .modal-buttons .btn-primary:hover {
            background-color: white;
            color: #007bff;
        }

        /* Buttons */
        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
            width: 100%;
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
            justify-content: center;
            gap: 8px;
            border: 2px solid transparent;
            min-width: 150px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: white;
            color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: white;
            color: #6c757d;
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

        /* Help Text */
        .help-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        /* No Image Placeholder */
        .no-image-placeholder {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            color: #6c757d;
            margin-top: 10px;
        }

        .no-image-placeholder i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

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

        .breadcrumb span {
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <p id="modalMessage"></p>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="handleModalNo()">No</button>
                <button class="btn btn-primary" onclick="handleModalYes()">Yes</button>
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
            <a href="manage_featured_attractions.php">Featured Attractions</a>
            <i class="fas fa-chevron-right"></i>
            <span>Edit Featured Attraction</span>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div class="page-title">
                <h1>Edit Featured Attraction</h1>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <!-- Basic Information -->
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                    <div class="form-group">
                        <label>Name of the Attraction:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($attraction['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Category:</label>
                        <input type="hidden" name="category" id="selectedCategory" value="<?php echo htmlspecialchars($attraction['category']); ?>" required>
                        <div class="category-grid">
                            <div class="category-option <?php echo ($attraction['category'] == 'Heritage Sites') ? 'selected' : ''; ?>" data-value="Heritage Sites">
                                <i class="fas fa-landmark"></i>
                                <span>Heritage Sites</span>
                            </div>
                            <div class="category-option <?php echo ($attraction['category'] == 'Natural Wonders') ? 'selected' : ''; ?>" data-value="Natural Wonders">
                                <i class="fas fa-mountain"></i>
                                <span>Natural Wonders</span>
                            </div>
                            <div class="category-option <?php echo ($attraction['category'] == 'Cultural Spots') ? 'selected' : ''; ?>" data-value="Cultural Spots">
                                <i class="fas fa-masks-theater"></i>
                                <span>Cultural Spots</span>
                            </div>
                            <div class="category-option <?php echo ($attraction['category'] == 'Local Delicacies/Food Spots') ? 'selected' : ''; ?>" data-value="Local Delicacies/Food Spots">
                                <i class="fas fa-utensils"></i>
                                <span>Local Delicacies/Food Spots</span>
                            </div>
                            <div class="category-option <?php echo ($attraction['category'] == 'Traditional Crafts') ? 'selected' : ''; ?>" data-value="Traditional Crafts">
                                <i class="fas fa-hands-holding"></i>
                                <span>Traditional Crafts</span>
                            </div>
                            <div class="category-option <?php echo ($attraction['category'] == 'Festivals & Events') ? 'selected' : ''; ?>" data-value="Festivals & Events">
                                <i class="fas fa-calendar-day"></i>
                                <span>Festivals & Events</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" required><?php echo htmlspecialchars($attraction['description']); ?></textarea>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="form-section">
                    <h2><i class="fas fa-map-marker-alt"></i> Location Details</h2>
                    <div class="form-group">
                        <label>Location:</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($attraction['location']); ?>" required>
                    </div>

                    <div class="map-note">
                        <i class="fas fa-info-circle"></i>
                        <p>To add the map location, obtain the latitude and longitude coordinates of the destination from 
                        <a href="https://www.google.com/maps" target="_blank">Google Maps</a>.</p>
                    </div>

                    <div class="form-group">
                        <label>Latitude:</label>
                        <input type="text" name="latitude" value="<?php echo htmlspecialchars($attraction['latitude']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Longitude:</label>
                        <input type="text" name="longitude" value="<?php echo htmlspecialchars($attraction['longitude']); ?>" required>
                    </div>
                </div>

                <!-- Images Section -->
                <div class="form-section">
                    <h2><i class="fas fa-images"></i> Images</h2>
                    
                    <!-- Current Thumbnail -->
                    <div class="form-group">
                        <label>Current Thumbnail:</label>
                        <div class="image-preview-container">
                            <?php if (!empty($thumbnail) && file_exists("../uploads/$thumbnail")): ?>
                                <div class="image-preview-item">
                                    <img src="../uploads/<?php echo htmlspecialchars($thumbnail); ?>" alt="Current Thumbnail">
                                </div>
                            <?php else: ?>
                                <div class="image-preview-item empty">
                                    <i class="fas fa-image"></i>
                                    <p>No thumbnail available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upload New Thumbnail -->
                    <div class="form-group">
                        <label>Upload New Thumbnail (Optional):</label>
                        <input type="file" name="thumbnail" accept="image/*">
                    </div>

                    <!-- Current Images -->
                    <div class="form-group">
                        <label>Current Images:</label>
                        <?php if (!empty($currentImages)): ?>
                            <div class="image-preview-container">
                                <?php foreach ($currentImages as $img): ?>
                                    <?php if (file_exists("../uploads/$img")): ?>
                                        <div class="image-preview-item">
                                            <img src="../uploads/<?php echo htmlspecialchars($img); ?>" alt="Attraction Image">
                                            <div class="delete-checkbox">
                                                <input type="checkbox" name="keep_images[]" value="<?php echo htmlspecialchars($img); ?>" checked>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <p class="help-text">Uncheck the box to remove an image</p>
                        <?php else: ?>
                            <div class="no-image-placeholder">
                                <i class="fas fa-images"></i>
                                <p>No additional images available</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Upload New Images -->
                    <div class="form-group">
                        <label>Upload Additional Images (Optional):</label>
                        <input type="file" name="images[]" multiple accept="image/*">
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
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

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }

        // Category selection
        document.querySelectorAll('.category-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.category-option').forEach(opt => 
                    opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update hidden input value
                document.getElementById('selectedCategory').value = this.dataset.value;
            });
        });

        // Image preview functionality
        function previewImage(input, previewContainer) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'image-preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Image Preview';
                    
                    previewItem.appendChild(img);
                    previewContainer.innerHTML = '';
                    previewContainer.appendChild(previewItem);
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Handle thumbnail preview
        const thumbnailInput = document.querySelector('input[name="thumbnail"]');
        const thumbnailPreview = document.querySelector('.image-preview-container');
        if (thumbnailInput) {
            thumbnailInput.addEventListener('change', function() {
                previewImage(this, thumbnailPreview);
            });
        }

        // Handle multiple image preview
        const imagesInput = document.querySelector('input[name="images[]"]');
        const imagesPreview = document.getElementById('imagesPreview');
        if (imagesInput) {
            imagesInput.addEventListener('change', function() {
                if (this.files) {
                    const previewContainer = document.createElement('div');
                    previewContainer.className = 'image-preview-container';

                    Array.from(this.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewItem = document.createElement('div');
                            previewItem.className = 'image-preview-item';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = 'Image Preview';
                            
                            previewItem.appendChild(img);
                            previewContainer.appendChild(previewItem);
                        }
                        reader.readAsDataURL(file);
                    });

                    // Replace existing preview
                    if (imagesPreview) {
                        imagesPreview.innerHTML = '';
                        imagesPreview.appendChild(previewContainer);
                    }
                }
            });
        }

        // Form validation and submission
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate category selection
            const selectedCategory = document.getElementById('selectedCategory').value;
            if (!selectedCategory) {
                alert('Please select a category for the featured attraction.');
                return;
            }

            // Confirm form submission
            showConfirmModal('Are you sure you want to save these changes?', (confirmed) => {
                if (confirmed) {
                    this.submit();
                }
            });
        });

        // Navigation confirmation
        document.querySelectorAll('.breadcrumb a, .logout-btn').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                showConfirmModal("Are you sure you want to leave? Any unsaved changes will be lost.", (confirmed) => {
                    if (confirmed) {
                        window.location.href = href;
                    }
                });
            });
        });

        // Handle ESC key press
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'block') {
                modal.style.display = 'none';
            }
        });

        // Success message handling
        <?php if (isset($_SESSION['success_message'])): ?>
            alert("<?php echo $_SESSION['success_message']; ?>");
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        // Form change detection
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
    </script>
</body>
</html>