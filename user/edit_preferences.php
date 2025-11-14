<?php
session_start();
include '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_useracc.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $activities = isset($_POST['activities']) ? json_decode($_POST['activities']) : [];
    $categories = isset($_POST['categories']) ? json_decode($_POST['categories']) : [];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete existing preferences
        $stmt = $conn->prepare("DELETE FROM UserPreferences WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Insert new activity preferences
        if (!empty($activities)) {
            $stmt = $conn->prepare("INSERT INTO UserPreferences (user_id, preference_type, preference_value) VALUES (?, 'activity', ?)");
            foreach ($activities as $activity) {
                $stmt->bind_param("is", $user_id, $activity);
                $stmt->execute();
            }
        }
        
        // Insert new category preferences
        if (!empty($categories)) {
            $stmt = $conn->prepare("INSERT INTO UserPreferences (user_id, preference_type, preference_value) VALUES (?, 'category', ?)");
            foreach ($categories as $category) {
                $stmt->bind_param("is", $user_id, $category);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        $success = "Preferences updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating preferences. Please try again.";
    }
}

// Fetch current preferences
$current_activities = [];
$current_categories = [];

$stmt = $conn->prepare("SELECT preference_type, preference_value FROM UserPreferences WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['preference_type'] === 'activity') {
        $current_activities[] = $row['preference_value'];
    } else if ($row['preference_type'] === 'category') {
        $current_categories[] = $row['preference_value'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Preferences - MViTour</title>
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
            min-height: 100vh;
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

        .logo {
            font-size: 1.8rem;
            font-weight: 400;
            display: flex;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
            color: white;
            text-decoration: none;
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

        .preferences-container {
            max-width: 800px;
            margin: 100px auto 40px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .preferences-title {
            font-size: 2rem;
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Playfair Display', serif;
        }

        .preferences-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .preference-option {
            background: #f8f9fa;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }

        .preference-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .preference-option.selected {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .preference-option i {
            font-size: 1.5em;
            margin-bottom: 8px;
            display: block;
        }

        .section-title {
            margin: 30px 0 15px;
            font-weight: 500;
            color: #333;
            font-size: 1.3rem;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1.2rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
        }

        .submit-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-message {
            background-color: #fee;
            color: #e33;
        }

        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="homepage.php" class="logo">
            <img src="../assets/mvitour_logo.png" alt="MViTour Logo">
            MViTour
        </a>
        <div class="header-nav">
            <a href="destination_listing.php" class="nav-link">
                <i class="fas fa-list-ul"></i> Destinations
            </a>
        </div>
    </header>

    <div class="preferences-container">
        <a href="homepage.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <h1 class="preferences-title">Edit Your Preferences</h1>

        <?php if (!empty($error)): ?>
            <div class="message error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="edit_preferences.php" method="POST">
            <h3 class="section-title">What activities do you enjoy?</h3>
            <div class="preferences-grid">
                <div class="preference-option <?php echo in_array('Cultural', $current_activities) ? 'selected' : ''; ?>" data-value="Cultural">
                    <i class="fas fa-masks-theater"></i>
                    <span>Cultural</span>
                </div>
                <div class="preference-option <?php echo in_array('Historical', $current_activities) ? 'selected' : ''; ?>" data-value="Historical">
                    <i class="fas fa-landmark"></i>
                    <span>Historical</span>
                </div>
                <div class="preference-option <?php echo in_array('Adventure', $current_activities) ? 'selected' : ''; ?>" data-value="Adventure">
                    <i class="fas fa-compass"></i>
                    <span>Adventure</span>
                </div>
                <div class="preference-option <?php echo in_array('Nature', $current_activities) ? 'selected' : ''; ?>" data-value="Nature">
                    <i class="fas fa-leaf"></i>
                    <span>Nature</span>
                </div>
                <div class="preference-option <?php echo in_array('Relaxation', $current_activities) ? 'selected' : ''; ?>" data-value="Relaxation">
                    <i class="fas fa-spa"></i>
                    <span>Relaxation</span>
                </div>
                <div class="preference-option <?php echo in_array('Educational', $current_activities) ? 'selected' : ''; ?>" data-value="Educational">
                    <i class="fas fa-book"></i>
                    <span>Educational</span>
                </div>
            </div>
            <input type="hidden" name="activities" id="selectedActivities">

            <h3 class="section-title">What places interest you most?</h3>
            <div class="preferences-grid">
                <div class="preference-option <?php echo in_array('Religious Site', $current_categories) ? 'selected' : ''; ?>" data-value="Religious Site">
                    <i class="fas fa-church"></i>
                    <span>Religious Sites</span>
                </div>
                <div class="preference-option <?php echo in_array('Museum', $current_categories) ? 'selected' : ''; ?>" data-value="Museum">
                    <i class="fas fa-landmark"></i>
                    <span>Museums</span>
                </div>
                <div class="preference-option <?php echo in_array('Structures and Buildings', $current_categories) ? 'selected' : ''; ?>" data-value="Structures and Buildings">
                    <i class="fas fa-building"></i>
                    <span>Structures</span>
                </div>
                <div class="preference-option <?php echo in_array('Parks', $current_categories) ? 'selected' : ''; ?>" data-value="Parks">
                    <i class="fas fa-tree"></i>
                    <span>Parks</span>
                </div>
                <div class="preference-option <?php echo in_array('Beaches', $current_categories) ? 'selected' : ''; ?>" data-value="Beaches">
                    <i class="fas fa-umbrella-beach"></i>
                    <span>Beaches</span>
                </div>
                <div class="preference-option <?php echo in_array('Restaurant', $current_categories) ? 'selected' : ''; ?>" data-value="Restaurant">
                    <i class="fas fa-utensils"></i>
                    <span>Restaurants</span>
                </div>
            </div>
            <input type="hidden" name="categories" id="selectedCategories">

            <button type="submit" class="submit-btn">Update Preferences</button>
        </form>
    </div>

    <script>
        // Activities selection
        const activityOptions = document.querySelectorAll('.preferences-grid:nth-of-type(1) .preference-option');
        const selectedActivitiesInput = document.getElementById('selectedActivities');

        function updateActivities() {
            const selectedActivities = Array.from(activityOptions)
                .filter(opt => opt.classList.contains('selected'))
                .map(opt => opt.dataset.value);
            selectedActivitiesInput.value = JSON.stringify(selectedActivities);
        }

        activityOptions.forEach(option => {
            option.addEventListener('click', () => {
                option.classList.toggle('selected');
                updateActivities();
            });
        });

        // Categories selection
        const categoryOptions = document.querySelectorAll('.preferences-grid:nth-of-type(2) .preference-option');
        const selectedCategoriesInput = document.getElementById('selectedCategories');

        function updateCategories() {
            const selectedCategories = Array.from(categoryOptions)
                .filter(opt => opt.classList.contains('selected'))
                .map(opt => opt.dataset.value);
            selectedCategoriesInput.value = JSON.stringify(selectedCategories);
        }

        categoryOptions.forEach(option => {
            option.addEventListener('click', () => {
                option.classList.toggle('selected');
                updateCategories();
            });
        });

        // Initialize hidden inputs with current selections
        updateActivities();
        updateCategories();
    </script>
</body>
</html>