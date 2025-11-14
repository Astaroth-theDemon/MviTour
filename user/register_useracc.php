<?php
session_start();
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle back button
    if (isset($_POST['action']) && $_POST['action'] === 'back') {
        $_SESSION['registration_step'] = 'initial';
        // Don't unset temp data when going back
        header("Location: register_useracc.php");
        exit();
    }

    // Check if this is the preferences submission
    if (isset($_POST['step']) && $_POST['step'] === 'preferences') {
        $user_id = $_SESSION['temp_user_id'];
        $username = $_SESSION['temp_username'];
        $email = $_SESSION['temp_email'];
        $password = $_SESSION['temp_password'];
        
        // Decode the JSON strings into arrays
        $activities = isset($_POST['activities']) ? json_decode($_POST['activities']) : [];
        $categories = isset($_POST['categories']) ? json_decode($_POST['categories']) : [];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // First, insert the user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            $stmt->execute();
            
            // Get the actual user_id from the insert
            $user_id = $conn->insert_id;
            
            // Insert activities preferences
            if (!empty($activities)) {
                $stmt = $conn->prepare("INSERT INTO UserPreferences (user_id, preference_type, preference_value) VALUES (?, 'activity', ?)");
                foreach ($activities as $activity) {
                    $stmt->bind_param("is", $user_id, $activity);
                    $stmt->execute();
                }
            }
            
            // Insert category preferences
            if (!empty($categories)) {
                $stmt = $conn->prepare("INSERT INTO UserPreferences (user_id, preference_type, preference_value) VALUES (?, 'category', ?)");
                foreach ($categories as $category) {
                    $stmt->bind_param("is", $user_id, $category);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            
            // Clear all temporary session data
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_username']);
            unset($_SESSION['temp_email']);
            unset($_SESSION['temp_password']);
            unset($_SESSION['registration_step']);
            
            // Set success message and redirect to login
            $_SESSION['success_message'] = "Registration successful! Please log in.";
            header("Location: login_useracc.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error saving preferences. Please try again.";
        }
    } else {
        // This is the initial registration submission
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $error = "";

        // Basic validation
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "All fields are required.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = "Email already registered.";
                } else {
                    // Store data in session instead of inserting to database
                    $_SESSION['temp_username'] = $username;
                    $_SESSION['temp_email'] = $email;
                    $_SESSION['temp_password'] = $password;
                    $_SESSION['temp_user_id'] = uniqid(); // Temporary ID
                    $_SESSION['registration_step'] = 'preferences';
                    header("Location: register_useracc.php");
                    exit();
                }
            }
        }
    }
}

// Check which step we're on
$show_preferences = isset($_SESSION['registration_step']) && $_SESSION['registration_step'] === 'preferences';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MViTour</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Montserrat', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                        url('../assets/vigan.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
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

        .auth-container {
            max-width: 500px;
            width: 90%;  /* Added for better responsiveness */
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: absolute;  /* Changed to absolute positioning */
            top: 55%;          /* Center vertically */
            left: 50%;         /* Center horizontally */
            transform: translate(-50%, -50%);  /* Perfect centering */
            max-height: 85vh;  /* Maximum height */
            overflow-y: auto;  /* Allow scrolling within container if needed */
        }

        .auth-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
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
            transition: background-color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        .auth-links {
            text-align: center;
            margin-top: 20px;
        }

        .auth-links a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #fee;
            color: #e33;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .form-group .password-input {
            padding-right: 45px;
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
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .back-btn {
            flex: 1;
            padding: 15px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1.2rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        .submit-btn {
            flex: 2;
        }

        .auth-container::-webkit-scrollbar {
            width: 8px;
        }

        .auth-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .auth-container::-webkit-scrollbar-thumb {
            background: #007bff;
            border-radius: 10px;
        }

        .auth-container::-webkit-scrollbar-thumb:hover {
            background: #0056b3;
        }

        /* For Firefox */
        .auth-container {
            scrollbar-width: thin;
            scrollbar-color: #007bff #f1f1f1;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="homepage.php" class="logo">
            <img src="../assets/mvitour_logo.png" alt="MViTour Logo">
            MViTour
        </a>
    </header>

    <div class="auth-container">
        <?php if (!$show_preferences): ?>
            <!-- Registration Form -->
            <h1 class="auth-title">Create Account</h1>
            
            <?php if (isset($error) && !empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="register_useracc.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-toggle">
                        <input type="password" id="password" name="password" class="password-input" required>
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-toggle">
                        <input type="password" id="confirm_password" name="confirm_password" class="password-input" required>
                        <i class="fas fa-eye" id="toggleConfirmPassword"></i>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Next</button>
            </form>

        <?php else: ?>
            <!-- Preferences Form -->

            <h1 class="auth-title">Select Your Preferences</h1>

            <form action="register_useracc.php" method="POST">
                <input type="hidden" name="step" value="preferences">

                <h3 class="section-title">What activities do you enjoy?</h3>
                <div class="preferences-grid">
                    <div class="preference-option" data-value="Cultural">
                        <i class="fas fa-masks-theater"></i>
                        <span>Cultural</span>
                    </div>
                    <div class="preference-option" data-value="Historical">
                        <i class="fas fa-landmark"></i>
                        <span>Historical</span>
                    </div>
                    <div class="preference-option" data-value="Adventure">
                        <i class="fas fa-compass"></i>
                        <span>Adventure</span>
                    </div>
                    <div class="preference-option" data-value="Nature">
                        <i class="fas fa-leaf"></i>
                        <span>Nature</span>
                    </div>
                    <div class="preference-option" data-value="Relaxation">
                        <i class="fas fa-spa"></i>
                        <span>Relaxation</span>
                    </div>
                    <div class="preference-option" data-value="Educational">
                        <i class="fas fa-book"></i>
                        <span>Educational</span>
                    </div>
                </div>
                <input type="hidden" name="activities" id="selectedActivities">

                <h3 class="section-title">What places interest you most?</h3>
                <div class="preferences-grid">
                    <div class="preference-option" data-value="Religious Site">
                        <i class="fas fa-church"></i>
                        <span>Religious Sites</span>
                    </div>
                    <div class="preference-option" data-value="Museum">
                        <i class="fas fa-landmark"></i>
                        <span>Museums</span>
                    </div>
                    <div class="preference-option" data-value="Structures and Buildings">
                        <i class="fas fa-building"></i>
                        <span>Structures and Buildings</span>
                    </div>
                    <div class="preference-option" data-value="Parks">
                        <i class="fas fa-tree"></i>
                        <span>Parks</span>
                    </div>
                    <div class="preference-option" data-value="Beaches">
                        <i class="fas fa-umbrella-beach"></i>
                        <span>Beaches</span>
                    </div>
                    <div class="preference-option" data-value="Restaurant">
                        <i class="fas fa-utensils"></i>
                        <span>Restaurants</span>
                    </div>
                </div>
                <input type="hidden" name="categories" id="selectedCategories">

                <div class="button-group" style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" name="action" value="back" class="back-btn">Back</button>
                    <button type="submit" class="submit-btn">Complete Registration</button>
                </div>
            </form>
        <?php endif; ?>

        <div class="auth-links">
            <p>Already have an account? <a href="login_useracc.php">Log In</a></p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.className = `fas fa-eye${type === 'password' ? '' : '-slash'}`;
        });

        document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
            const confirmPassword = document.getElementById('confirm_password');
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.className = `fas fa-eye${type === 'password' ? '' : '-slash'}`;
        });

        // Preferences selection
        if (document.querySelector('.preferences-grid')) {
            // Activities selection
            const activityOptions = document.querySelectorAll('.preferences-grid:nth-of-type(1) .preference-option');
            const selectedActivitiesInput = document.getElementById('selectedActivities');

            activityOptions.forEach(option => {
                option.addEventListener('click', () => {
                    option.classList.toggle('selected');
                    const selectedActivities = Array.from(activityOptions)
                        .filter(opt => opt.classList.contains('selected'))
                        .map(opt => opt.dataset.value);
                    selectedActivitiesInput.value = JSON.stringify(selectedActivities);
                    console.log('Selected Activities:', selectedActivitiesInput.value); // For debugging
                });
            });

            // Categories selection
            const categoryOptions = document.querySelectorAll('.preferences-grid:nth-of-type(2) .preference-option');
            const selectedCategoriesInput = document.getElementById('selectedCategories');

            categoryOptions.forEach(option => {
                option.addEventListener('click', () => {
                    option.classList.toggle('selected');
                    const selectedCategories = Array.from(categoryOptions)
                        .filter(opt => opt.classList.contains('selected'))
                        .map(opt => opt.dataset.value);
                    selectedCategoriesInput.value = JSON.stringify(selectedCategories);
                    console.log('Selected Categories:', selectedCategoriesInput.value); // For debugging
                });
            });
        }
    </script>
</body>
</html>