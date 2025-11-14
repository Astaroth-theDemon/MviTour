<?php

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_adminacc.php"); // Redirect to login page if not logged in
    exit();
}

include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string ($_POST['name']);
    $description = $conn->real_escape_string ($_POST['description']);
    $location = $conn->real_escape_string ($_POST['location']);
    $barangay = $conn->real_escape_string ($_POST['barangay']);
    $category = $conn->real_escape_string ($_POST['category']);
    $latitude = $conn->real_escape_string ($_POST['latitude']);
    $longitude = $conn->real_escape_string ($_POST['longitude']);
    $entrance_fee = $conn->real_escape_string ($_POST['entrance_fee']);
    $opening_time = $conn->real_escape_string($_POST['opening_time']);
    $closing_time = $conn->real_escape_string($_POST['closing_time']);
    $is_open = isset($_POST['is_open']) ? (int)$_POST['is_open'] : 0;

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
    $sql = "INSERT INTO tourist_spots (name, description, location, barangay, category, latitude, longitude, entrance_fee, images, destination_thumbnail, opening_time, closing_time, is_open) 
            VALUES ('$name', '$description', '$location', '$barangay', '$category', '$latitude', '$longitude', '$entrance_fee', '$images', '$thumbnailImage', '$opening_time', '$closing_time', $is_open)";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = 'Tourist spot added successfully!';
        header("Location: manage_tourist_spots.php");
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
    <title>Add Tourist Spot - MViTour</title>
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
        .form-group input[type="number"],
        .form-group select,
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
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
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

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: white;
            color: #dc3545;
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-btn {
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
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: white;
            color: #dc3545;
            border-color: #dc3545;
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

        /* Category Selection Styles */
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
            padding: 10px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .category-option:hover {
            transform: translateY(-2px);
            border-color: #007bff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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

        .status-options {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .status-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .status-option input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .status-option span {
            font-size: 1rem;
            user-select: none;
        }

        .form-group input[type="time"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input[type="time"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
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
            <a href="choose_destination.php">Choose Destination</a>
            <i class="fas fa-chevron-right"></i>
            <span>Add Tourist Spot</span>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div class="page-title">
                <h1>Add Tourist Spot</h1>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <!-- Basic Information -->
                <div class="form-section">
                    <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                    <div class="form-group">
                        <label>Name of the Tourist Spot:</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Category:</label>
                        <input type="hidden" name="category" id="selectedCategory" required>
                        <div class="category-grid">
                            <div class="category-option" data-value="Religious Site">
                                <i class="fas fa-church"></i>
                                <span>Religious Site</span>
                            </div>
                            <div class="category-option" data-value="Nature Trail">
                                <i class="fas fa-mountain"></i>
                                <span>Nature Trail</span>
                            </div>
                            <div class="category-option" data-value="Recreational Activities">
                                <i class="fas fa-hiking"></i>
                                <span>Recreational Activities</span>
                            </div>
                            <div class="category-option" data-value="Historical Road">
                                <i class="fas fa-road"></i>
                                <span>Historical Road</span>
                            </div>
                            <div class="category-option" data-value="Falls">
                                <i class="fas fa-water"></i>
                                <span>Falls</span>
                            </div>
                            <div class="category-option" data-value="Museum">
                                <i class="fas fa-landmark"></i>
                                <span>Museum</span>
                            </div>
                            <div class="category-option" data-value="Camping Ground">
                                <i class="fas fa-campground"></i>
                                <span>Camping Ground</span>
                            </div>
                            <div class="category-option" data-value="Parks">
                                <i class="fas fa-tree"></i>
                                <span>Parks</span>
                            </div>
                            <div class="category-option" data-value="Beach">
                                <i class="fas fa-umbrella-beach"></i>
                                <span>Beach</span>
                            </div>
                            <div class="category-option" data-value="Structures and Buildings">
                                <i class="fas fa-building"></i>
                                <span>Structures and Buildings</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Entrance Fee:</label>
                        <input type="number" name="entrance_fee" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Operating Hours:</label>
                        <div style="display: flex; gap: 10px; margin-top: 5px;">
                            <div style="width: 50%;">
                                <label style="font-weight: normal; font-size: 0.9rem;">Opening Time:</label>
                                <input type="time" name="opening_time" required>
                            </div>
                            <div style="width: 50%;">
                                <label style="font-weight: normal; font-size: 0.9rem;">Closing Time:</label>
                                <input type="time" name="closing_time" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Currently Open?</label>
                        <div class="status-options">
                            <label class="status-option">
                                <input type="radio" name="is_open" value="1" checked>
                                <span>Open</span>
                            </label>
                            <label class="status-option">
                                <input type="radio" name="is_open" value="0">
                                <span>Closed</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="form-section">
                    <h2><i class="fas fa-map-marker-alt"></i> Location Details</h2>
                    <div class="form-group">
                        <label>Location:</label>
                        <select name="location" id="location" onchange="updateBarangays()" required>
                            <option value="" disabled selected>Select location</option>
                            <option value="Vigan">Vigan</option>
                            <option value="Bantay">Bantay</option>
                            <option value="Santa Catalina">Santa Catalina</option>
                            <option value="San Vicente">San Vicente</option>
                            <option value="Caoayan">Caoayan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Barangay:</label>
                        <select name="barangay" id="barangay" required>
                            <option value="">Please select a location first</option>
                        </select>
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
                        Add Tourist Spot
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Barangay data
        const barangays = {
            "Vigan": [
                "Ayusan Norte", "Ayusan Sur", "Barangay I (Poblacion)", "Barangay II (Poblacion)", 
                "Barangay III (Poblacion)", "Barangay IV (Poblacion)", "Barangay V (Poblacion)", 
                "Barangay VI (Poblacion)", "Barangay VII (Poblacion)", "Barangay VIII (Poblacion)", 
                "Barangay IX (Poblacion)", "Barraca", "Beddeng Daya", "Beddeng Laud", "Bongtolan", 
                "Bulala", "Cabalangegan", "Cabaroan Daya", "Cabaroan Laud", "Camangaan", 
                "Capangpangan", "Mindoro", "Nagsangalan", "Pantay Daya", "Pantay Fatima", 
                "Pantay Laud", "Paoa", "Paratong", "Pong-ol", "Purok-a-bassit", "Purok-a-dackel", 
                "Raois", "Rugsuanan", "Salindeg", "San Jose", "San Julian Norte", 
                "San Julian Sur", "San Pedro", "Tamag"
            ],
            "Bantay": [
                "Aggay", "An-annam", "Balaleng", "Banaoang", "Barangay 1 (Poblacion)", 
                "Barangay 2 (Poblacion)", "Barangay 3 (Poblacion)", "Barangay 4 (Poblacion)", 
                "Barangay 5 (Poblacion)", "Barangay 6 (Poblacion)", "Bulag", "Buquig", 
                "Cabalanggan", "Cabaroan", "Cabusligan", "Capangdanan", "Guimod", 
                "Lingsat", "Malingeb", "Mira", "Naguiddayan", "Ora", "Paing", 
                "Puspus", "Quimmarayan", "Sagneb", "Sagpat", "San Isidro", "San Julian", 
                "San Mariano (formerly Sallacong)", "Sinabaan", "Taguiporo", "Taleb", "Tay-ac"
            ],
            "Santa Catalina": [
                "Cabaroan", "Cabittaogan", "Cabuloan", "Pangada", "Paratong", 
                "Poblacion", "Sinabaan", "Subec", "Tamorong"
            ],
            "San Vicente": [
                "Bantaoay", "Bayubay Norte", "Bayubay Sur", "Lubong", "Poblacion", 
                "Pudoc", "San Sebastian"
            ],
            "Caoayan": [
                "Anonang Mayor", "Anonang Menor", "Baggoc", "Callaguip", "Caparacadan", 
                "Don Alejandro Quirolgico (Poblacion)", "Don Dimas Querubin (Poblacion)", 
                "Don Lorenzo Querubin (Poblacion)", "Fuerte", "Manangat", "Naguilian", 
                "Nansuagao", "Pandan", "Pantay Tamurong", "Pantay-Quitiquit", "Puro", "Villamar"
            ]
        };

        function updateBarangays() {
        const location = document.getElementById("location").value;
        const barangayDropdown = document.getElementById("barangay");

        barangayDropdown.innerHTML = ""; // Clear existing options

        if (location && barangays[location]) {
            barangays[location].forEach(barangay => {
                const option = document.createElement("option");
                option.value = barangay;
                option.textContent = barangay;
                barangayDropdown.appendChild(option);
            });
        } else {
            const defaultOption = document.createElement("option");
            defaultOption.value = "";
            defaultOption.textContent = "Please select a location first";
            barangayDropdown.appendChild(defaultOption);
        }
    }

        document.getElementById("location").addEventListener("change", updateBarangays);

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

        function confirmAction(message, url) {
            if (confirm(message)) {
                window.location.href = url;
            }
        }

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

        // Update breadcrumb and logout click handlers
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

        // Category selection
        document.querySelectorAll('.category-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.category-option').forEach(opt => 
                    opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedCategory').value = this.dataset.value;
            });
        });

        // Success message
        function showSuccessModal(message, redirectUrl) {
            showConfirmModal(message, (confirmed) => {
                window.location.href = redirectUrl;
            });
        }
    </script>

</body>
</html>
